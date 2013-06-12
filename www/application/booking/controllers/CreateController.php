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

class Booking_CreateController extends Tillikum_Controller_Booking
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
            'booking',
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
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The person for whom you were trying to create a facility' .
                    ' booking could not be found.'
                ),
                404
            );
        }

        $sessionData = $this->sessionContainer[$this->containerKey];

        $form = $this->getDi()
            ->newInstance('Tillikum_Form')
            ->setAction($this->_helper->url->url());

        $facilityBooking = new Entity\Booking\Facility\Facility();
        $facilityBilling = new Entity\Booking\Facility\Billing\Billing();

        $facilityBooking->person = $person;
        $facilityBooking->billing = $facilityBilling;

        // facility subform
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
            } elseif (isset($decodedJson)) {
                $form->facility_booking->populate($decodedJson);
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
                $rateSubForm->rule_id->setAttrib('data-rate-start', 'facility_booking-billing-' . $rateSubForm->start->getId());
                $rateSubForm->rule_id->setAttrib('data-rate-end', 'facility_booking-billing-' .  $rateSubForm->end->getId());
            }
        }

        $this->view->form = $form;
        $this->view->person = $facilityBooking->person;

        if (isset($sessionData)) {
            $this->view->clearSessionUri = $this->_helper->url(
                'clear',
                'create',
                'booking',
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
        } elseif (count($rateValues) > 0) {
            $formRequiresResubmit = true;
        }

        $this->sessionContainer[$this->containerKey] = array(
            'formData' => $values,
            'facilityBookingFormWarnings' => $form->facility_booking->getWarnings()
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
                    'Sorry, it looks like we lost the facility booking you were in' .
                    ' the process of editing. Nothing has been permanently' .
                    ' changed yet. Please go back and try once more, but if you' .
                    ' get this message again, contact technical support.'
                ),
                404
            );
        }

        $sessionData = $this->sessionContainer[$this->containerKey];

        $facilityBooking = new Entity\Booking\Facility\Facility;
        $facilityBooking->person = $this->getEntityManager()->find(
            'Tillikum\Entity\Person\Person',
            $personId
        );

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Facility');
        $form->bind($facilityBooking);
        $form->populate($sessionData['formData']['facility_booking']);
        $form->bindValues();

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Booking\Billing');

        $facilityBilling = new Entity\Booking\Facility\Billing\Billing;
        $facilityBooking->billing = $facilityBilling;
        $facilityBilling->booking = $facilityBooking;

        $form->bind($facilityBilling);
        $form->populate($sessionData['formData']['facility_booking']['billing']);
        $form->bindValues();

        $billingEvents = new ArrayCollection();
        if (!empty($sessionData['formData']['facility_booking']['billing']['rates'])) {
            foreach ($sessionData['formData']['facility_booking']['billing']['rates'] as $rate) {
                $form = $this->getDi()
                    ->newInstance('Tillikum\Form\Booking\FacilityRate');

                $rateEntity = new Entity\Booking\Facility\Billing\Rate\Rate;

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

        $personId = $this->_request->getParam('pid');

        $this->getEntityManager()->persist($facilityBooking);

        foreach ($billingEvents as $billingEvent) {
            $this->getEntityManager()->persist($billingEvent);
        }

        $this->getEntityManager()->flush();

        unset($this->sessionContainer[$this->containerKey]);

        $this->_helper->redirector('index', 'create', 'mealplan', array('pid' => $facilityBooking->person->id));
    }
}
