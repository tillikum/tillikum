<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Booking_IndexController extends Tillikum_Controller_Booking
{
    public function indexAction()
    {
    }

    public function viewAction()
    {
        $id = $this->_request->getParam('id');

        if ($id === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The booking you were trying to view could not be found.'
            ), 404);
        }

        $booking = $this->getEntityManager()->find(
            'Tillikum\Entity\Booking\Facility\Facility',
            $id
        );

        if ($booking === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The booking you were trying to view could not be found.'
            ), 404);
        }

        $this->view->booking = $booking;
        $this->view->bookingData = $this->_helper->dataTableBookingDetail(array($booking));
        $this->view->billingData = $this->_helper->dataTableBookingBilling($booking->billing);
        $this->view->rateData = $this->_helper->dataTableBookingBillingRate($booking->billing->rates);
    }
}
