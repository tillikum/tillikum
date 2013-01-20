<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Billing_AdhocController extends Tillikum_Controller_Billing
{
    public function createAction()
    {
        if (!$this->getAcl()->isAllowed('_user', 'billing_event', 'write')) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'You are not allowed to create ad hoc billing events.'
                ),
                403
            );
        }

        $personId = (string) $this->_request->getParam('pid');

        $person = $this->getEntityManager()
            ->find('Tillikum\Entity\Person\Person', $personId);

        if ($person === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The person for whom you are trying to create an ad hoc' .
                    ' billing event could not be found.'
                ),
                404
            );
        }

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Billing\AdHocEvent')
            ->setAction($this->_helper->url->url());

        if ($this->_request->isPost()) {
            $this->createProcessor($form, $this->_request->getPost(), $person);
        }

        $this->view->form = $form;

        $this->view->person = $person;
    }

    protected function createProcessor($form, $input, $person)
    {
        $form->bind(new \Tillikum\Entity\Billing\Event\AdHoc());

        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $event = $form->entity;
        $event->is_processed = false;
        $event->person = $person;

        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();

        $this->_helper->redirector(
            'view',
            'person',
            'person',
            array(
                'id' => $person->id,
            )
        );
    }
}
