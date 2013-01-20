<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Facility_FacilitygroupconfigController extends Tillikum_Controller_Facility
{
    public function createAction()
    {
        $facilityGroupId = $this->_request->getParam('facilitygroup_id');

        $facilityGroup = $this->getEntityManager()->find(
            'Tillikum\Entity\FacilityGroup\FacilityGroup',
            $facilityGroupId
        );

        if (null === $facilityGroup) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The facility group you are trying to create the configuration for could not be found.'
            ), 404);
        }

        // @todo dynamic subtype
        $facilityGroupConfig = new \Tillikum\Entity\FacilityGroup\Config\Building\Building;
        $facilityGroupConfig->facility_group = $facilityGroup;

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\FacilityGroup\Config')
            ->setAction($this->_helper->url->url());
        $form->bind($facilityGroupConfig);

        $this->view->facilityGroupConfig = $facilityGroupConfig;
        $this->view->form = $form;

        if ($this->_request->isPost()) {
            $this->processCreate($form, $this->_request->getPost());
        }
    }

    protected function processCreate($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();
        $this->getEntityManager()->persist($form->entity);

        $this->getEntityManager()->flush();

        return $this->_helper->redirector(
            'view',
            'facilitygroup',
            'facility',
            array(
                'id' => $form->entity->facility_group->id,
            )
        );
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');

        $facilityGroupConfig = $this->getEntityManager()->find(
            'Tillikum\Entity\FacilityGroup\Config\Config',
            $id
        );

        if ($facilityGroupConfig === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The facility group configuration you selected could not be found.'
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

        if ($this->_request->isPost()) {
            $this->processDelete($form, $this->_request->getPost(), $facilityGroupConfig);
        }

        $this->view->facilityGroupConfig = $facilityGroupConfig;
        $this->view->form = $form;
    }

    protected function processDelete($form, $input, $facilityGroupConfig)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $this->getEntityManager()->remove($facilityGroupConfig);
        $this->getEntityManager()->flush();

        return $this->_helper->redirector(
            'view',
            'facilitygroup',
            'facility',
            array(
                'id' => $facilityGroupConfig->facility_group->id,
            )
        );
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');

        $facilityGroupConfig = $this->getEntityManager()->find(
            'Tillikum\Entity\FacilityGroup\Config\Config',
            $id
        );

        if ($facilityGroupConfig === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The facility group configuration you selected could not be found.'
            ), 404);
        }

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\FacilityGroup\Config')
            ->setAction($this->_helper->url->url());
        $form->bind($facilityGroupConfig);

        if ($this->_request->isPost()) {
            $this->processEdit($form, $this->_request->getPost());
        }

        $this->view->facilityGroupConfig = $facilityGroupConfig;
        $this->view->form = $form;
    }

    protected function processEdit($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $this->getEntityManager()->flush();

        return $this->_helper->redirector(
            'view',
            'facilitygroup',
            'facility',
            array(
                'id' => $form->entity->facility_group->id,
            )
        );
    }
}
