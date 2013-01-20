<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Person_PersonController extends Tillikum_Controller_Person
{
    public function autocompleteAction()
    {
        $limit = $this->_request->getQuery('limit') ?: 15;
        $query = $this->_request->getQuery('q');

        $people = $this->getEntityManager()
            ->getRepository('Tillikum\Entity\Person\Person')
            ->createSearchQueryBuilder($query)
            ->select(array('partial p.{id, display_name, given_name, middle_name, family_name}'))
            ->from('Tillikum\Entity\Person\Person', 'p')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $ret = array();
        foreach ($people as $person) {
            $ret[] = array(
                'key' => $person->id,
                'value' => $person->id,
                'label' => $person->display_name,
                'uri' => $this->_helper->url('view', 'person', 'person', array('id' => $person->id))
            );
        }

        usort($ret, function($a, $b) {
            return strnatcmp($a['label'], $b['label']);
        });

        $this->_helper->json($ret);
    }

    public function imageAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $id = $this->_request->getParam('id');

        $image = $this->getEntityManager()
            ->getRepository('Tillikum\Entity\Person\Image')
            ->findOneByPerson($id);

        if ($image === null) {
            $this->_response->setHttpResponseCode(404);

            return;
        }

        $imageData = stream_get_contents($image->image);

        $mimeType = finfo_buffer(
            finfo_open(FILEINFO_MIME),
            $imageData
        );

        $this->_response->setHeader('Content-Type', $mimeType);
        $this->_response->setBody($imageData);
    }

    public function viewAction()
    {
        $id = (string) $this->_request->getParam('id');

        $person = $this->getEntityManager()
            ->find('Tillikum\Entity\Person\Person', $id);

        if ($person === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The person you were looking for could not be found.'
                ),
                404
            );
        }

        $this->view->person = $person;

        $this->view->bookingSummaryData = $this->_helper
            ->dataTableBookingSummary($person->bookings);

        $this->view->invoiceData = $this->_helper
            ->dataTableInvoice($person->invoices);

        $this->view->mealplanSummaryData = $this->_helper
            ->dataTableMealplanSummary($person->mealplans);

        $this->view->contractSummaryData = $this->_helper
            ->dataTableContractSummary($person->contract_signatures);

        $this->view->tabNav = $this->getDi()
            ->get('PersonTabNavigation');
    }

    public function createAction()
    {
        $person = $this->getDi()
            ->newInstance('PersonEntity');

        $form = $this->getDi()
            ->newInstance('PersonForm')
            ->setAction($this->_helper->url->url())
            ->bind($person);

        if ($this->_request->isPost()) {
            $this->processCreate($form, $this->_request->getPost());
        }

        $this->view->form = $form;
    }

    protected function processCreate($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $person = $form->person;

        $this->getEntityManager()->persist($person);
        $this->getEntityManager()->flush();

        $this->_helper->redirector('view', null, null, array('id' => $person->id));
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');

        $person = $this->getEntityManager()
            ->find('Tillikum\Entity\Person\Person', $id);

        if ($person === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The person you were looking for could not be found.'
                ),
                404
            );
        }

        $form = $this->getDi()
            ->newInstance('PersonForm')
            ->setAction($this->_helper->url->url())
            ->bind($person);

        if ($this->_request->isPost()) {
            $this->processEdit($form, $this->_request->getPost());
        }

        $this->view->form = $form;
        $this->view->person = $person;
    }

    protected function processEdit($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $person = $form->person;

        $this->getEntityManager()->persist($person);
        $this->getEntityManager()->flush();

        $this->_helper->redirector('view', null, null, array('id' => $person->id));
    }
}
