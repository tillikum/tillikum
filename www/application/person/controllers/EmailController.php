<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Person_EmailController extends Tillikum_Controller_Person
{
    public function createAction()
    {
        $personId = $this->_request->getParam('person_id');

        $person = $this->getEntityManager()->find(
            'Tillikum\Entity\Person\Person',
            $personId
        );

        if ($person === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The person for whom you are trying to add an email address could not be found.'
                ),
                404
            );
        }

        $emailAddress = $this->getDi()
            ->newInstance('Tillikum\Entity\Person\Address\Email');

        $emailAddress->person = $person;

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Person\Address\Email');

        $form->addElement($form->createSubmitElement(array('label' => 'Create')))
            ->setAction($this->_helper->url->url())
            ->bind($emailAddress);

        $this->view->form = $form;
        $this->view->person = $person;

        if ($this->_request->isPost()) {
            if (!$form->isValid($this->_request->getPost())) {
                return;
            }

            if ($emailAddress->is_primary) {
                foreach ($person->emails as $existingEmailAddress) {
                    $existingEmailAddress->is_primary = false;
                }
            }

            $form->bindValues();

            $this->getEntityManager()->persist($emailAddress);
            $this->getEntityManager()->flush();

            $this->_helper->redirector('view', 'person', null, array('id' => $person->id));
        }
    }

    public function editAction()
    {
        $emailId = $this->_request->getParam('id');

        $emailAddress = $this->getEntityManager()->find(
            'Tillikum\Entity\Person\Address\Email',
            $emailId
        );

        if ($emailAddress === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The email address you were trying to edit could not be found.'
                ),
                404
            );
        }

        $person = $emailAddress->person;

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Person\Address\Email');

        $form->addElement($form->createSubmitElement(array('label' => 'Save')))
            ->setAction($this->_helper->url->url())
            ->bind($emailAddress);

        $this->view->form = $form;
        $this->view->person = $person;

        if ($this->_request->isPost()) {
            if (!$form->isValid($this->_request->getPost())) {
                return;
            }

            if ($emailAddress->is_primary) {
                foreach ($person->emails as $existingEmailAddress) {
                    $existingEmailAddress->is_primary = false;
                }
            }

            $form->bindValues();

            $this->getEntityManager()->flush();

            $this->_helper->redirector('view', 'person', null, array('id' => $person->id));
        }
    }
}
