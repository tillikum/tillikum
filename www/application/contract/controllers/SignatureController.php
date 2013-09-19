<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

use Tillikum\Entity;

class Contract_SignatureController extends Tillikum_Controller_Contract
{
    public function createAction()
    {
        if (!$this->getAcl()->isAllowed('_user', 'contract_signature', 'write')) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'You do not have permission to sign contracts.'
                ),
                403
            );
        }

        $person = $this->getEntityManager()->find(
            'Tillikum\Entity\Person\Person',
            $this->_request->getParam('pid')
        );

        if ($person === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The person for which you tried to sign a contract for ' .
                    'could not be found.'
                ),
                404
            );
        }

        $signature = new Entity\Contract\Signature;
        $signature->person = $person;

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Contract\Signature')
            ->setAction($this->_helper->url->url())
            ->bind($signature);

        if ($this->_request->isPost()) {
            $this->processCreate($form, $this->_request->getPost());
        } else {
            $form->populate(
                array(
                    'person_id' => $person->id
                )
            );
        }

        $this->view->form = $form;
        $this->view->person = $person;
    }

    protected function processCreate($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $signature = $form->entity;
        $contract = $signature->contract;
        $person = $signature->person;

        if ($person->getAge($signature->signed_at) < $contract->age_of_majority) {
            $signature->requires_cosigned = true;
        } else {
            $signature->requires_cosigned = false;
        }

        $identity = $this->getAuthenticationService()
            ->getIdentity();

        $signature->is_signed = true;
        $signature->signed_by = $identity;

        if ($signature->is_cosigned) {
            $signature->cosigned_at = new DateTime();
            $signature->cosigned_by = $identity;
        } else {
            $signature->cosigned_at = null;
            $signature->cosigned_by = null;
        }

        if ($signature->is_cancelled) {
            $signature->cancelled_at = new DateTime();
            $signature->cancelled_by = $identity;
        } else {
            $signature->cancelled_at = null;
            $signature->cancelled_by = null;
        }

        $this->getEntityManager()->persist($signature);
        $this->getEntityManager()->flush();

        $this->_helper->redirector(
            'view',
            'person',
            'person',
            array(
                'id' => $person->id
            )
        );
    }

    public function editAction()
    {
        if (!$this->getAcl()->isAllowed('_user', 'contract_signature', 'write')) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'You do not have permission to modify contract signatures.'
                ),
                403
            );
        }

        $signature = $this->getEntityManager()->find(
            'Tillikum\Entity\Contract\Signature',
            $this->_request->getParam('id')
        );

        if ($signature === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The specified contract signature could not be found.'
                ),
                404
            );
        }

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Contract\Signature')
            ->setAction($this->_helper->url->url())
            ->bind($signature);

        if ($this->_request->isPost()) {
            $this->processEdit($form, $this->_request->getPost());
        }

        $this->view->form = $form;
        $this->view->person = $signature->person;
    }

    protected function processEdit($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $signature = $form->entity;
        $contract = $signature->contract;
        $person = $signature->person;

        if ($person->getAge($signature->signed_at) < $contract->age_of_majority) {
            $signature->requires_cosigned = true;
        } else {
            $signature->requires_cosigned = false;
        }

        $identity = $this->getAuthenticationService()
            ->getIdentity();

        $signature->is_signed = true;
        $signature->signed_by = $identity;

        if ($signature->is_cosigned) {
            $signature->cosigned_at = new DateTime();
            $signature->cosigned_by = $identity;
        } else {
            $signature->cosigned_at = null;
            $signature->cosigned_by = null;
        }

        if ($signature->is_cancelled) {
            $signature->cancelled_at = new DateTime();
            $signature->cancelled_by = $identity;
        } else {
            $signature->cancelled_at = null;
            $signature->cancelled_by = null;
        }

        $this->getEntityManager()->flush();

        $this->_helper->redirector(
            'view',
            'person',
            'person',
            array(
                'id' => $person->id
            )
        );
    }
}
