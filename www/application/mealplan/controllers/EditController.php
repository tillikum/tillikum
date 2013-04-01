<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

use Doctrine\Common\Collections\ArrayCollection;
use Tillikum\Entity;
use Tillikum\Form;
use Zend\Session;

class Mealplan_EditController extends Tillikum_Controller_Mealplan
{
    protected $sessionContainer;
    protected $containerKey;

    public function clearAction()
    {
        $this->sessionContainer = $this->getSessionContainer(__CLASS__);

        $bookingId = $this->_request->getParam('id');
        $this->containerKey = 'id' . $bookingId;

        unset($this->sessionContainer[$this->containerKey]);

        $this->_helper->redirector(
            'index',
            'edit',
            'mealplan',
            array(
                'id' => $bookingId,
            )
        );
    }

    public function indexAction()
    {
        $this->sessionContainer = $this->getSessionContainer(__CLASS__);

        $bookingId = $this->_request->getParam('id');
        $this->containerKey = 'id' . $bookingId;

        $mealplanBooking = $this->getEntityManager()->find(
            'Tillikum\Entity\Booking\Mealplan\Mealplan',
            $bookingId
        );

        if ($mealplanBooking === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The booking you were trying to edit could not be found.'
            ), 404);
        }

        $sessionData = $this->sessionContainer[$this->containerKey];

        $form = $this->getDi()
            ->newInstance('Tillikum_Form')
            ->setAction($this->_helper->url->url());

        $mealplanBookingSubForm = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Mealplan')
            ->addDecorator(
                'Fieldset',
                array(
                    'legend' => 'Meal plan booking'
                )
            )
            ->setElementsBelongTo('mealplan_booking');

        $mealplanBookingSubForm->removeElement('tillikum_submit');
        $mealplanBookingSubForm->removeDecorator('Form');
        $mealplanBookingSubForm->bind($mealplanBooking);

        $form->addSubForm($mealplanBookingSubForm, 'mealplan_booking');

        if ($mealplanBooking->billing) {
            $mealplanBilling = $mealplanBooking->billing;
        } else {
            $mealplanBilling = new Entity\Booking\Mealplan\Billing\Billing();
        }

        // Billing subform
        $mealplanBillingSubForm = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Billing')
            ->addDecorator(
                'Fieldset',
                array(
                    'legend' => 'Billing'
                )
            )
            ->setElementsBelongTo('billing');

        $mealplanBillingSubForm->removeElement('tillikum_submit');
        $mealplanBillingSubForm->removeDecorator('form');
        $mealplanBillingSubForm->bind($mealplanBilling);

        $mealplanBookingSubForm->addSubForm($mealplanBillingSubForm, 'billing');

        $rates = array();
        if ($this->_request->isPost()) {
            $rates = $this->_request->getPost();
            $rates = $rates['mealplan_booking']['billing']['rates'];
        } else {
            if ($sessionData) {
                $rates = $sessionData['formData']['mealplan_booking']['billing']['rates'];
            } elseif ($mealplanBooking->billing) {
                $rates = $mealplanBooking->billing->rates;
            }
        }

        if ($rates) {
            foreach ($rates as $idx => $rate) {
                if (is_array($rate) && empty($rate['rule_id'])) {
                    continue;
                }

                $rateSubForm = $this->getDi()
                    ->newInstance('Tillikum\Form\Booking\MealplanRate')
                    ->addDecorator('Fieldset', array(
                        'legend' => 'Rate'
                    ))
                    ->setElementsBelongTo("rates[$idx]");

                $rateSubForm->removeElement('tillikum_submit');
                $rateSubForm->removeDecorator('Form');

                if (is_object($rate)) {
                    $rateSubForm->bind($rate);
                }

                $mealplanBillingSubForm->addSubForm($rateSubForm, "rate_$idx");
            }
        }

        $form->addElement(
            $form->createSubmitElement(
                array(
                    'label' => 'Next...',
                )
            )
        );

        if ($this->_request->isPost()) {
            $this->processIndex($form, $this->_request->getPost());
        } else {
            if ($sessionData) {
                $form->populate($sessionData['formData']);
            }
        }

        $newRateIndex = 'rate' . uniqid();

        $rateSubForm = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\MealplanRate')
            ->addDecorator('Fieldset', array(
                'legend' => 'Add new rate'
            ))
            ->setElementsBelongTo("rates[{$newRateIndex}]");

        $rateSubForm->removeElement('delete_me');
        $rateSubForm->removeElement('tillikum_submit');
        $rateSubForm->removeDecorator('Form');

        // Everything in the 'new' rate form is optional
        foreach ($rateSubForm->getElements() as $rateElement) {
            $rateElement->setRequired(false);
        }

        $mealplanBillingSubForm->addSubForm($rateSubForm, "rate_$newRateIndex");

        foreach ($mealplanBillingSubForm->getSubForms() as $rateSubForm) {
            /**
             * @todo re-enable once we get defaults in the database
             */
            //$rateSubForm->rule_id->setAttrib('tillikum-default-mealplan-rule', '');
            //$rateSubForm->rule_id->setAttrib('data-mealplan-id', $mealplanBookingSubForm->mealplan_id->getId());
            //$rateSubForm->rule_id->setAttrib('data-mealplan-start', $mealplanBookingSubForm->start->getId());
            //$rateSubForm->rule_id->setAttrib('data-mealplan-end', $mealplanBookingSubForm->end->getId());
        }

        $this->view->form = $form;
        $this->view->person = $mealplanBooking->person;

        if (isset($sessionData)) {
            $this->view->clearSessionUri = $this->_helper->url(
                'clear',
                'edit',
                'mealplan',
                array(
                    'id' => $bookingId,
                )
            );
        }
    }

    protected function processIndex($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $bookingId = $this->_request->getParam('id');

        $values = $form->getValues();

        $formRequiresResubmit = false;

        if (empty($values['mealplan_booking']['billing']['rates'])) {
            $values['mealplan_booking']['billing']['rates'] = array();
        }

        $rateValues = $values['mealplan_booking']['billing']['rates'];
        foreach ($rateValues as $idx => $rate) {
            if ((bool) $rate['delete_me']) {
                unset($rateValues[$idx]);
            }
        }
        $values['mealplan_booking']['billing']['rates'] = $rateValues;

        if (isset($this->sessionContainer[$this->containerKey])) {
            $currentRateData = $this->sessionContainer[$this->containerKey]
                ['formData']['mealplan_booking']['billing']['rates'];

            if (count($currentRateData) !== count($rateValues)) {
                $formRequiresResubmit = true;
            }
        } else {
            if (isset($form->booking->billing)) {
                $billing = $form->booking->billing;
                if (count($billing->rates) !== count($rateValues)) {
                    $formRequiresResubmit = true;
                }
            }
        }

        $this->sessionContainer[$this->containerKey] = array(
            'formData' => $values,
            'mealplanBookingFormWarnings' => $form->mealplan_booking->getWarnings()
        );

        if ($formRequiresResubmit) {
            $this->_helper->redirector('index', null, null, array('id' => $bookingId));
        } else {
            $this->_helper->redirector('confirm', null, null, array('id' => $bookingId));
        }
    }

    public function confirmAction()
    {
        $this->sessionContainer = $this->getSessionContainer(__CLASS__);

        $bookingId = $this->_request->getParam('id');
        $this->containerKey = 'id' . $bookingId;

        if (!isset($this->sessionContainer[$this->containerKey])) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'Sorry, it looks like we lost the booking you were in the'
                . ' process of editing. Nothing has been permanently changed'
                . ' yet. Please go back and try once more, but if you get this'
                . ' message again, contact technical support.'
            ), 404);
        }

        $mealplanBooking = $this->getEntityManager()->find(
            'Tillikum\Entity\Booking\Mealplan\Mealplan',
            $bookingId
        );

        if ($mealplanBooking === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The booking you were trying to edit has been deleted since you'
              . ' began editing.'
            ), 404);
        }

        $sessionData = $this->sessionContainer[$this->containerKey];

        $existingMealplan = $mealplanBooking->mealplan;

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Mealplan');
        $form->bind($mealplanBooking);
        $form->populate($sessionData['formData']['mealplan_booking']);
        $form->bindValues();

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Billing');

        if ($mealplanBooking->billing) {
            $mealplanBilling = $mealplanBooking->billing;
            $oldThrough = $mealplanBooking->billing->through ? clone($mealplanBooking->billing->through) : null;
        } else {
            $mealplanBilling = new Entity\Booking\Mealplan\Billing\Billing();
            $oldThrough = null;
        }

        $mealplanBooking->billing = $mealplanBilling;
        $mealplanBilling->booking = $mealplanBooking;

        $form->bind($mealplanBilling);
        $form->populate($sessionData['formData']['mealplan_booking']['billing']);
        $form->bindValues();

        $billingEvents = new ArrayCollection();

        foreach ($mealplanBilling->rates as $existingRate) {
            if ($oldThrough && $oldThrough >= $existingRate->start) {
                $eventEnd = min($existingRate->end, $oldThrough);

                $billingEventEntity = new Entity\Billing\Event\MealplanBooking();
                $billingEventEntity->person = $mealplanBooking->person;
                $billingEventEntity->rule = $existingRate->rule;
                $billingEventEntity->is_processed = false;
                $billingEventEntity->mealplan = $existingMealplan;
                $billingEventEntity->is_credit = true;
                $billingEventEntity->start = $existingRate->start;
                $billingEventEntity->end = $eventEnd;

                $billingEvents->add($billingEventEntity);
            }

            $doRemove = true;
            if (!empty($sessionData['formData']['mealplan_booking']['billing']['rates'])) {
                foreach ($sessionData['formData']['mealplan_booking']['billing']['rates'] as $rate) {
                    if ($rate['id'] == $existingRate->id) {
                        $doRemove = false;
                    }
                }
            }

            if ($doRemove) {
                $this->getEntityManager()->remove($existingRate);
            }
        }

        $mealplanBilling->rates = new ArrayCollection();

        if (!empty($sessionData['formData']['mealplan_booking']['billing']['rates'])) {
            foreach ($sessionData['formData']['mealplan_booking']['billing']['rates'] as $rate) {
                $form = $this->getDi()
                    ->newInstance('Tillikum\Form\Booking\MealplanRate');

                $rateEntity = $this->getEntityManager()
                    ->find('Tillikum\Entity\Booking\Mealplan\Billing\Rate\Rate', $rate['id']);

                if ($rateEntity === null) {
                    $rateEntity = new Entity\Booking\Mealplan\Billing\Rate\Rate();
                }

                $form->bind($rateEntity);
                $form->populate($rate);
                $form->bindValues();
                $rateEntity->billing = $mealplanBilling;
                $mealplanBilling->rates->add($rateEntity);

                if ($mealplanBilling->through && $mealplanBilling->through >= $rateEntity->start) {
                    $eventEnd = min($rateEntity->end, $mealplanBilling->through);

                    $billingEventEntity = new Entity\Billing\Event\MealplanBooking();
                    $billingEventEntity->person = $mealplanBooking->person;
                    $billingEventEntity->rule = $rateEntity->rule;
                    $billingEventEntity->is_processed = false;
                    $billingEventEntity->mealplan = $mealplanBooking->mealplan;
                    $billingEventEntity->is_credit = false;
                    $billingEventEntity->start = $rateEntity->start;
                    $billingEventEntity->end = $eventEnd;

                    $billingEvents->add($billingEventEntity);
                }

                $this->getEntityManager()->persist($rateEntity);
            }
        }

        $form = $this->getDi()
            ->newInstance('Tillikum_Form')
            ->setAction($this->_helper->url->url())
            ->addElements(
                array(
                    $form->createSubmitElement(array('label' => 'Save'))
                )
            )
            ->setWarnings($sessionData['mealplanBookingFormWarnings']);

        if ($this->_request->isPost()) {
            $this->processConfirm($form, $this->_request->getPost(), $mealplanBooking, $billingEvents);
        }

        $this->view->bookingData = $this->_helper->dataTableMealplanConfirm(array($mealplanBooking));
        $this->view->billingData = $this->_helper->dataTableBillingConfirm($mealplanBooking->billing);
        $this->view->billingEventData = $this->_helper->dataTableBillingEventConfirm($billingEvents);
        $this->view->rateData = $this->_helper->dataTableRateConfirm($mealplanBooking->billing->rates);

        $this->view->form = $form;
    }

    protected function processConfirm($form, $input, $mealplanBooking, $billingEvents)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $bookingId = $this->_request->getParam('id');

        $this->getEntityManager()->persist($mealplanBooking);

        foreach ($billingEvents as $billingEvent) {
            $this->getEntityManager()->persist($billingEvent);
        }

        $this->getEntityManager()->flush();

        unset($this->sessionContainer[$this->containerKey]);

        $this->_helper->redirector('view', 'person', 'person', array('id' => $mealplanBooking->person->id));
    }
}
