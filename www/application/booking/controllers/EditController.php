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

class Booking_EditController extends Tillikum_Controller_Booking
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
            'booking',
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

        $facilityBooking = $this->getEntityManager()->find(
            'Tillikum\Entity\Booking\Facility\Facility',
            $bookingId
        );

        if ($facilityBooking === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The booking you were trying to edit could not be found.'
            ), 404);
        }

        $sessionData = $this->sessionContainer[$this->containerKey];

        $form = $this->getDi()
            ->newInstance('Tillikum_Form')
            ->setAction($this->_helper->url->url());

        $facilityBookingSubForm = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Facility')
            ->addDecorator(
                'Fieldset',
                array(
                    'legend' => 'Facility booking'
                )
            )
            ->setElementsBelongTo('facility_booking');

        $facilityBookingSubForm->removeElement('tillikum_submit');
        $facilityBookingSubForm->removeDecorator('Form');
        $facilityBookingSubForm->bind($facilityBooking);

        $form->addSubForm($facilityBookingSubForm, 'facility_booking');

        if ($facilityBooking->billing) {
            $facilityBilling = $facilityBooking->billing;
        } else {
            $facilityBilling = new Entity\Booking\Facility\Billing\Billing();
        }

        // Billing subform
        $facilityBillingSubForm = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Billing')
            ->addDecorator(
                'Fieldset',
                array(
                    'legend' => 'Billing'
                )
            )
            ->setElementsBelongTo('billing');

        $facilityBillingSubForm->removeElement('tillikum_submit');
        $facilityBillingSubForm->removeDecorator('form');
        $facilityBillingSubForm->bind($facilityBilling);

        $facilityBookingSubForm->addSubForm($facilityBillingSubForm, 'billing');

        $rates = array();
        if ($this->_request->isPost()) {
            $rates = $this->_request->getPost();
            $rates = $rates['facility_booking']['billing']['rates'];
        } else {
            if ($sessionData) {
                $rates = $sessionData['formData']['facility_booking']['billing']['rates'];
            } elseif ($facilityBooking->billing) {
                $rates = $facilityBooking->billing->rates;
            }
        }

        if ($rates) {
            foreach ($rates as $idx => $rate) {
                if (is_array($rate) && empty($rate['rule_id'])) {
                    continue;
                }

                $rateSubForm = $this->getDi()
                    ->newInstance('Tillikum\Form\Booking\FacilityRate')
                    ->addDecorator('Fieldset', array(
                        'legend' => 'Rate'
                    ))
                    ->setElementsBelongTo("rates[$idx]");

                $rateSubForm->removeElement('tillikum_submit');
                $rateSubForm->removeDecorator('Form');

                if (is_object($rate)) {
                    $rateSubForm->bind($rate);
                }

                $facilityBillingSubForm->addSubForm($rateSubForm, "rate_$idx");
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

                $facility = $this->getEntityManager()->find(
                    'Tillikum\Entity\Facility\Facility',
                    $form->facility_booking->facility_id->getValue()
                );

                $form->facility_booking->facility_name->setValue(
                    implode(' ', $facility->getNamesOnDate(new DateTime(
                        $sessionData['formData']['facility_booking']['start']
                    )))
                );
            }
        }

        $newRateIndex = 'rate' . uniqid();

        $rateSubForm = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\FacilityRate')
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

        $facilityBillingSubForm->addSubForm($rateSubForm, "rate_$newRateIndex");

        foreach ($facilityBillingSubForm->getSubForms() as $rateSubForm) {
            $rateSubForm->rule_id->setAttrib('tillikum-default-facility-rule', '');
            $rateSubForm->rule_id->setAttrib('data-facility-id', $facilityBookingSubForm->facility_id->getId());
            $rateSubForm->rule_id->setAttrib('data-facility-start', $facilityBookingSubForm->start->getId());
            $rateSubForm->rule_id->setAttrib('data-facility-end', $facilityBookingSubForm->end->getId());
        }

        $this->view->form = $form;
        $this->view->person = $facilityBooking->person;

        if (isset($sessionData)) {
            $this->view->clearSessionUri = $this->_helper->url(
                'clear',
                'edit',
                'booking',
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

        if (empty($values['facility_booking']['billing']['rates'])) {
            $values['facility_booking']['billing']['rates'] = array();
        }

        $rateValues = $values['facility_booking']['billing']['rates'];
        foreach ($rateValues as $idx => $rate) {
            if ((bool) $rate['delete_me']) {
                unset($rateValues[$idx]);
            }
        }
        $values['facility_booking']['billing']['rates'] = $rateValues;

        if (isset($this->sessionContainer[$this->containerKey])) {
            $currentRateData = $this->sessionContainer[$this->containerKey]
                ['formData']['facility_booking']['billing']['rates'];

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
            'facilityBookingFormWarnings' => $form->facility_booking->getWarnings()
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

        $facilityBooking = $this->getEntityManager()->find(
            'Tillikum\Entity\Booking\Facility\Facility',
            $bookingId
        );

        if ($facilityBooking === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The booking you were trying to edit has been deleted since you'
              . ' began editing.'
            ), 404);
        }

        $sessionData = $this->sessionContainer[$this->containerKey];

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Facility');
        $form->bind($facilityBooking);
        $form->populate($sessionData['formData']['facility_booking']);
        $form->bindValues();

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Billing');

        if ($facilityBooking->billing) {
            $facilityBilling = $facilityBooking->billing;
            $oldThrough = $facilityBooking->billing->through ? clone($facilityBooking->billing->through) : null;
        } else {
            $facilityBilling = new Entity\Booking\Facility\Billing\Billing();
            $oldThrough = null;
        }

        $facilityBooking->billing = $facilityBilling;
        $facilityBilling->booking = $facilityBooking;

        $form->bind($facilityBilling);
        $form->populate($sessionData['formData']['facility_booking']['billing']);
        $form->bindValues();

        $billingEvents = new ArrayCollection();

        foreach ($facilityBilling->rates as $existingRate) {
            if ($oldThrough && $oldThrough >= $existingRate->start) {
                $eventEnd = min($existingRate->end, $oldThrough);

                $billingEventEntity = new Entity\Billing\Event\FacilityBooking();
                $billingEventEntity->person = $facilityBooking->person;
                $billingEventEntity->rule = $existingRate->rule;
                $billingEventEntity->is_processed = false;
                $billingEventEntity->facility = $facilityBooking->facility;
                $billingEventEntity->is_credit = true;
                $billingEventEntity->start = $existingRate->start;
                $billingEventEntity->end = $eventEnd;

                $billingEvents->add($billingEventEntity);
            }

            $doRemove = true;
            if (!empty($sessionData['formData']['facility_booking']['billing']['rates'])) {
                foreach ($sessionData['formData']['facility_booking']['billing']['rates'] as $rate) {
                    if ($rate['id'] == $existingRate->id) {
                        $doRemove = false;
                    }
                }
            }

            if ($doRemove) {
                $this->getEntityManager()->remove($existingRate);
            }
        }

        $facilityBilling->rates = new ArrayCollection();

        if (!empty($sessionData['formData']['facility_booking']['billing']['rates'])) {
            foreach ($sessionData['formData']['facility_booking']['billing']['rates'] as $rate) {
                $form = $this->getDi()
                    ->newInstance('Tillikum\Form\Booking\FacilityRate');

                $rateEntity = $this->getEntityManager()
                    ->find('Tillikum\Entity\Booking\Facility\Billing\Rate\Rate', $rate['id']);

                if ($rateEntity === null) {
                    $rateEntity = new Entity\Booking\Facility\Billing\Rate\Rate();
                }

                $form->bind($rateEntity);
                $form->populate($rate);
                $form->bindValues();
                $rateEntity->billing = $facilityBilling;
                $facilityBilling->rates->add($rateEntity);

                if ($facilityBilling->through && $facilityBilling->through >= $rateEntity->start) {
                    $eventEnd = min($rateEntity->end, $facilityBilling->through);

                    $billingEventEntity = new Entity\Billing\Event\FacilityBooking();
                    $billingEventEntity->person = $facilityBooking->person;
                    $billingEventEntity->rule = $rateEntity->rule;
                    $billingEventEntity->is_processed = false;
                    $billingEventEntity->facility = $facilityBooking->facility;
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
            ->setWarnings($sessionData['facilityBookingFormWarnings']);

        if ($this->_request->isPost()) {
            $this->processConfirm($form, $this->_request->getPost(), $facilityBooking, $billingEvents);
        }

        $this->view->bookingData = $this->_helper->dataTableBookingConfirm(array($facilityBooking));
        $this->view->billingData = $this->_helper->dataTableBillingConfirm($facilityBooking->billing);
        $this->view->billingEventData = $this->_helper->dataTableBillingEventConfirm($billingEvents);
        $this->view->rateData = $this->_helper->dataTableRateConfirm($facilityBooking->billing->rates);

        $this->view->form = $form;
    }

    protected function processConfirm($form, $input, $facilityBooking, $billingEvents)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $bookingId = $this->_request->getParam('id');

        $this->getEntityManager()->persist($facilityBooking);

        foreach ($billingEvents as $billingEvent) {
            $this->getEntityManager()->persist($billingEvent);
        }

        $this->getEntityManager()->flush();

        unset($this->sessionContainer[$this->containerKey]);

        $this->_helper->redirector('view', 'person', 'person', array('id' => $facilityBooking->person->id));
    }
}
