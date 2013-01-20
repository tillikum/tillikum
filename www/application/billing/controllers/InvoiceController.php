<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Billing_InvoiceController extends Tillikum_Controller_Billing
{
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->getAcl()->isAllowed('_user', 'billing_invoice')) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'You are not allowed to access billing invoices.'
                ),
                403
            );
        }
    }

    public function viewAction()
    {
        $id = (string) $this->_request->getParam('id');

        $invoice = $this->getEntityManager()
            ->find('Tillikum\Entity\Billing\Invoice\Invoice', $id);

        if ($invoice === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The invoice you were looking for could not be found.'
                ),
                404
            );
        }

        $this->view->invoice = $invoice;
        $this->view->entryData = $this->_helper->dataTableBillingEntry(
            $invoice->entries
        );
    }
}
