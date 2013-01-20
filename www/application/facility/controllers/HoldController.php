<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Facility_HoldController extends Tillikum_Controller_Facility
{
    public function createAction()
    {
        $facilityId = $this->_request->getParam('facility_id');

        $facility = $this->getEntityManager()->find(
            'Tillikum\Entity\Facility\Facility',
            $facilityId
        );

        if ($facility === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The facility you are trying to create the hold on could not be found.'
            ), 404);
        }

        $facilityHold = new \Tillikum\Entity\Facility\Hold\Hold;
        $facilityHold->facility = $facility;

        $form = $this->getDi()
           ->newInstance('Tillikum\Form\Facility\Hold')
           ->setAction($this->_helper->url->url())
           ->bind($facilityHold);

        $this->view->facilityHold = $facilityHold;
        $this->view->form = $form;

        if ($this->_request->isPost()) {
            if (!$form->isValid($this->_request->getPost())) {
                return;
            }

            $form->bindValues();

            $this->getEntityManager()->persist($facilityHold);
            $this->getEntityManager()->flush();

            $this->_helper->redirector(
                'view',
                'facility',
                'facility',
                array(
                    'id' => $facilityHold->facility->id,
                )
            );
        }
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');

        $facilityHold = $this->getEntityManager()->find(
            'Tillikum\Entity\Facility\Hold\Hold',
            $id
        );

        if ($facilityHold === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The hold you selected could not be found.'
            ), 404);
        }


        $form = $this->getDi()
            ->newInstance('Tillikum_Form')
            ->setAction($this->_helper->url->url());
        $form->addElement(
            $form->createSubmitElement(
                array(
                    'label' => 'Delete',
                )
            )
        );

        $this->view->facilityHold = $facilityHold;
        $this->view->form = $form;

        if ($this->_request->isPost()) {
            if (!$form->isValid($this->_request->getPost())) {
                return;
            }

            $this->getEntityManager()->remove($facilityHold);
            $this->getEntityManager()->flush();

            $this->_helper->redirector(
                'view',
                'facility',
                'facility',
                array(
                    'id' => $facilityHold->facility->id,
                )
            );
        }
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');

        $facilityHold = $this->getEntityManager()->find(
            'Tillikum\Entity\Facility\Hold\Hold',
            $id
        );

        if ($facilityHold === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The hold you selected could not be found.'
            ), 404);
        }

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Facility\Hold')
            ->setAction($this->_helper->url->url())
            ->bind($facilityHold);

        $this->view->facilityHold = $facilityHold;
        $this->view->form = $form;

        if ($this->_request->isPost()) {
            if (!$form->isValid($this->_request->getPost())) {
                return;
            }

            $form->bindValues();

            $this->getEntityManager()->flush();

            $this->_helper->redirector(
                'view',
                'facility',
                'facility',
                array(
                    'id' => $facilityHold->facility->id,
                )
            );
        }
    }
}
