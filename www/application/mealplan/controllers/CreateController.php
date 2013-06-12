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

class Mealplan_CreateController extends Tillikum_Controller_Mealplan
{
    protected $sessionContainer;
    protected $containerKey;

    public function clearAction()
    {
        $this->sessionContainer = $this->getSessionContainer(__CLASS__);

        $personId = $this->_request->getParam('pid');
        $this->containerKey = 'pid' . $personId;

        unset($this->sessionContainer[$this->containerKey]);

        $this->_helper->redirector(
            'index',
            'create',
            'mealplan',
            array(
                'pid' => $personId,
            )
        );
    }

    public function indexAction()
    {
        $this->sessionContainer = $this->getSessionContainer(__CLASS__);

        $personId = $this->_request->getParam('pid');
        $this->containerKey = 'pid' . $personId;

        $person = $this->getEntityManager()->find(
            'Tillikum\Entity\Person\Person',
            $personId
        );

        if ($person === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The person for whom you were trying to create a meal plan'
              . ' booking could not be found.'
            ), 404);
        }

        $sessionData = $this->sessionContainer[$this->containerKey];

        // Main form
        $form = new \Tillikum_Form(array(
            'action' => $this->_helper->url->url()
        ));

        $mealplanBooking = new Entity\Booking\Mealplan\Mealplan();
        $mealplanBilling = new Entity\Booking\Mealplan\Billing\Billing();

        $mealplanBooking->person = $person;
        $mealplanBooking->billing = $mealplanBilling;

        // mealplan subform
        $mealplanBookingSubForm = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Mealplan')
            ->addDecorator('Fieldset', array(
                'legend' => 'Meal plan booking'
            ))
            ->setElementsBelongTo('mealplan_booking');

        $mealplanBookingSubForm->removeElement('tillikum_submit');
        $mealplanBookingSubForm->removeDecorator('Form');
        $mealplanBookingSubForm->bind($mealplanBooking);

        $form->addSubForm($mealplanBookingSubForm, 'mealplan_booking');

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
            } elseif ($json = $this->_request->getQuery('json')) {
                $decodedJson = json_decode($json, true);
                if (isset($decodedJson['billing']) && isset($decodedJson['billing']['rates'])) {
                    $rates = $decodedJson['billing']['rates'];
                }
            }
        }

        if ($rates) {
            foreach ($rates as $idx => $rate) {
                if (is_array($rate) && !isset($rate['delete_me']) && empty($rate['rule']) && empty($rate['start']) && empty($rate['end'])) {
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
            } elseif (isset($decodedJson)) {
                $form->mealplan_booking->populate($decodedJson);
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
                $rateSubForm->rule_id->setAttrib('tillikum-default-mealplan-rule', '');
                $rateSubForm->rule_id->setAttrib('data-mealplan-id', $mealplanBookingSubForm->mealplan_id->getId());
                $rateSubForm->rule_id->setAttrib('data-rate-start', 'mealplan_booking-billing-' . $rateSubForm->start->getId());
                $rateSubForm->rule_id->setAttrib('data-rate-end', 'mealplan_booking-billing-' . $rateSubForm->end->getId());
            }
        }

        $this->view->form = $form;
        $this->view->person = $mealplanBooking->person;

        if (isset($sessionData)) {
            $this->view->clearSessionUri = $this->_helper->url(
                'clear',
                'create',
                'mealplan',
                array(
                    'pid' => $personId,
                )
            );
        }
    }

    protected function processIndex($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $personId = $this->_request->getParam('pid');

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
        } elseif (count($rateValues) > 0) {
            $formRequiresResubmit = true;
        }

        $this->sessionContainer[$this->containerKey] = array(
            'formData' => $values,
            'mealplanBookingFormWarnings' => $form->mealplan_booking->getWarnings()
        );

        if ($formRequiresResubmit) {
            $this->_helper->redirector('index', null, null, array('pid' => $personId));
        } else {
            $this->_helper->redirector('confirm', null, null, array('pid' => $personId));
        }
    }

    public function confirmAction()
    {
        $this->sessionContainer = $this->getSessionContainer(__CLASS__);

        $personId = $this->_request->getParam('pid');
        $this->containerKey = 'pid' . $personId;

        if (!isset($this->sessionContainer[$this->containerKey])) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'Sorry, it looks like we lost the meal plan booking you were in' .
                    ' the process of editing. Nothing has been permanently' .
                    ' changed yet. Please go back and try once more, but if you' .
                    ' get this message again, contact technical support.'
                ),
                404
            );
        }

        $sessionData = $this->sessionContainer[$this->containerKey];

        $mealplanBooking = new Entity\Booking\Mealplan\Mealplan;
        $mealplanBooking->person = $this->getEntityManager()->find(
            'Tillikum\Entity\Person\Person',
            $personId
        );

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Mealplan');
        $form->bind($mealplanBooking);
        $form->populate($sessionData['formData']['mealplan_booking']);
        $form->bindValues();

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Billing');

        $mealplanBilling = new Entity\Booking\Mealplan\Billing\Billing;
        $mealplanBooking->billing = $mealplanBilling;
        $mealplanBilling->booking = $mealplanBooking;

        $form->bind($mealplanBilling);
        $form->populate($sessionData['formData']['mealplan_booking']['billing']);
        $form->bindValues();

        $billingEvents = new ArrayCollection();
        if (!empty($sessionData['formData']['mealplan_booking']['billing']['rates'])) {
            foreach ($sessionData['formData']['mealplan_booking']['billing']['rates'] as $rate) {
                $form = $this->getDi()
                    ->newInstance('Tillikum\Form\Booking\MealplanRate');

                $rateEntity = new Entity\Booking\Mealplan\Billing\Rate\Rate;

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
            }
        }

        $form = new \Tillikum_Form();
        $form->setAction($this->_helper->url->url())
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

        $personId = $this->_request->getParam('pid');

        $this->getEntityManager()->persist($mealplanBooking);

        foreach ($billingEvents as $billingEvent) {
            $this->getEntityManager()->persist($billingEvent);
        }

        $this->getEntityManager()->flush();

        unset($this->sessionContainer[$this->containerKey]);

        $this->_helper->redirector('view', 'person', 'person', array('id' => $mealplanBooking->person->id));
    }
}
