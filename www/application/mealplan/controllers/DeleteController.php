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

class Mealplan_DeleteController extends Tillikum_Controller_Mealplan
{
    protected $sessionContainer;
    protected $containerKey;

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
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The booking you were trying to delete could not be found.'
                ),
                404
            );
        }

        $this->_helper->redirector('confirm', null, null, array('id' => $bookingId));
    }

    public function confirmAction()
    {
        $bookingId = $this->_request->getParam('id');

        $mealplanBooking = $this->getEntityManager()->find(
            'Tillikum\Entity\Booking\Mealplan\Mealplan',
            $bookingId
        );

        if ($mealplanBooking === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The booking you were trying to edit has been deleted since you' .
                    ' began editing.'
                ),
                404
            );
        }

        $billingEvents = new ArrayCollection();
        if ($mealplanBooking->billing) {
            $mealplanBilling = $mealplanBooking->billing;
            foreach ($mealplanBilling->rates as $existingRate) {
                if ($mealplanBilling->through && $mealplanBilling->through >= $existingRate->start) {
                    $eventEnd = min($existingRate->end, $mealplanBilling->through);

                    $billingEventEntity = new Entity\Billing\Event\MealplanBooking();
                    $billingEventEntity->person = $mealplanBooking->person;
                    $billingEventEntity->rule = $existingRate->rule;
                    $billingEventEntity->is_processed = false;
                    $billingEventEntity->mealplan = $mealplanBooking->mealplan;
                    $billingEventEntity->is_credit = true;
                    $billingEventEntity->start = $existingRate->start;
                    $billingEventEntity->end = $eventEnd;

                    $billingEvents->add($billingEventEntity);
                }

                $this->getEntityManager()->remove($existingRate);
            }
        }

        $this->_helper->ensureProcessableEvents($this->getDi(), $billingEvents);

        $form = $this->getDi()
            ->newInstance('Tillikum_Form')
            ->setAction($this->_helper->url->url());

        $form->addElements(
            array(
                $form->createSubmitElement(array('label' => 'Save'))
            )
        );

        if ($this->_request->isPost()) {
            $this->processConfirm($form, $this->_request->getPost(), $mealplanBooking, $billingEvents);
        }

        $this->view->bookingData = $this->_helper->dataTableMealplanConfirm(array($mealplanBooking));
        $this->view->billingEventData = $this->_helper->dataTableBillingEventConfirm($billingEvents);

        $this->view->form = $form;
    }

    protected function processConfirm($form, $input, $mealplanBooking, $billingEvents)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $bookingId = $this->_request->getParam('id');

        $this->getEntityManager()->remove($mealplanBooking);

        foreach ($billingEvents as $billingEvent) {
            $this->getEntityManager()->persist($billingEvent);
        }

        $this->getEntityManager()->flush();

        unset($this->sessionContainer[$this->containerKey]);

        $this->_helper->redirector('view', 'person', 'person', array('id' => $mealplanBooking->person->id));
    }
}
