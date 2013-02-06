<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

use Tillikum\Entity;

class Billing_SummaryController extends Tillikum_Controller_Billing
{
    public function viewAction()
    {
        $this->_helper->layout()->disableLayout();

        $personId = (string) $this->_request->getParam('pid');

        $person = $this->getEntityManager()
            ->find('Tillikum\Entity\Person\Person', $personId);

        if ($person === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The person you were looking for could not be found.'
                ),
                404
            );
        }

        $events = $this->getEntityManager()->createQuery(
            "
            SELECT e
            FROM Tillikum\Entity\Billing\Event\Event e
            WHERE e.person = :person
            "
        )
            ->setParameter('person', $person)
            ->getResult();

        $adHocEvents = array();
        $facilityBookingEvents = array();
        $mealplanBookingEvents = array();
        foreach ($events as $event) {
            if ($event instanceof Entity\Billing\Event\AdHoc) {
                $adHocEvents[] = $event;
            } elseif ($event instanceof Entity\Billing\Event\FacilityBooking) {
                $facilityBookingEvents[] = $event;
            } elseif ($event instanceof Entity\Billing\Event\MealplanBooking) {
                $mealplanBookingEvents[] = $event;
            }
        }

        $entries = $this->getEntityManager()->createQuery(
            "
            SELECT e
            FROM Tillikum\Entity\Billing\Entry\Entry e
            JOIN e.invoice i
            WHERE i.person = :person
            "
        )
            ->setParameter('person', $person)
            ->getResult();

        $this->view->entryData = $this->_helper
            ->dataTableBillingEntry($entries);

        $this->view->adHocEventData = $this->_helper
            ->dataTableBillingEventAdHoc($adHocEvents);

        $this->view->facilityBookingEventData = $this->_helper
            ->dataTableBillingEventFacilityBooking($facilityBookingEvents);

        $this->view->mealplanBookingEventData = $this->_helper
            ->dataTableBillingEventMealplanBooking($mealplanBookingEvents);

        $this->view->person = $person;
    }
}
