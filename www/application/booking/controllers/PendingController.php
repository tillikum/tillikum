<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Booking_PendingController extends Tillikum_Controller_Booking
{
    public function indexAction()
    {
        $sm = $this->getServiceManager();

        $pendingBookingsByName = array();
        foreach ($sm->get('PendingBookings') as $pendingBooking) {
            $pendingBookingsByName[$pendingBooking] = $sm->get($pendingBooking);
        }

        $this->view->pendingBookingSummary = $this->_helper->dataTablePendingBookingSummary(
            $pendingBookingsByName
        );
    }

    public function viewAction()
    {
        $pendingBookingName = $this->_request->getParam('name');

        if ($pendingBookingName === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'Sorry, you did not specify which pending booking list to load.'
                ),
                404
            );
        }

        $sm = $this->getServiceManager();
        $di = $sm->get('Di');

        $pendingBooking = $sm->get($pendingBookingName);

        $this->view->pendingBookingName = $pendingBooking->getName();
        $this->view->pendingBookingDescription = $pendingBooking->getDescription();

        $helper = $this->getHelper($pendingBooking->getActionHelperName());
        $this->view->pendingBookingRows = $helper->direct();

        $this->view->pendingBookingHelperName = $pendingBooking->getViewHelperName();
    }
}
