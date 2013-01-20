<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

use Tillikum\Entity,
    Tillikum\Form,
    Zend_Session as Session,
    Zend_Session_Namespace as SessionNamespace;

class Facility_FacilityconfigController extends Tillikum_Controller_Facility
{
    public function create1Action()
    {
        $facilityId = $this->_request->getParam('facility_id');

        $facility = $this->getEntityManager()->find(
            'Tillikum\Entity\Facility\Facility',
            $facilityId
        );

        if ($facility === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The facility you are trying to create the configuration for could not be found.'
            ), 404);
        }

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Facility\ConfigType')
            ->setAction($this->_helper->url->url());

        if ($this->_request->isPost()) {
            $this->processCreate1($form, $this->_request->getPost());
        }

        $this->view->form = $form;
    }

    protected function processCreate1($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $facilityId = $this->_request->getParam('facility_id');

        $values = $form->getValues();

        $this->_helper->redirector(
            'create2',
            'facilityconfig',
            'facility',
            array(
                'facility_id' => $facilityId,
                'type' => $values['type'],
            )
        );
    }

    public function create2Action()
    {
        $facilityId = $this->_request->getParam('facility_id');
        $type = $this->_request->getParam('type');

        $facility = $this->getEntityManager()->find('Tillikum\Entity\Facility\Facility', $facilityId);

        if ($facility === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The facility you are trying to create the configuration for could not be found.'
            ), 404);
        }

        if ($type === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'You did not specify the facility configuration type to create.'
                ),
                404
            );
        }

        $entity = $this->getDi()
            ->newInstance($type);
        $entity->facility = $facility;

        $form = $this->getDi()
            ->newInstance($entity::FORM_CLASS)
            ->setAction($this->_helper->url->url());
        $form->bind($entity);

        if ($this->_request->isPost()) {
            $this->processCreate2($form, $this->_request->getPost());
        }

        $this->view->form = $form;
    }

    protected function processCreate2($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $facilityId = $this->_request->getParam('facility_id');
        $type = $this->_request->getParam('type');

        $form->bindValues();

        $facilityConfig = $form->entity;

        $this->getEntityManager()->persist($facilityConfig);
        $this->getEntityManager()->flush();

        $this->_helper->redirector(
            'view',
            'facility',
            'facility',
            array('id' => $facilityConfig->facility->id)
        );
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');

        $facilityConfig = $this->getEntityManager()->find(
            'Tillikum\Entity\Facility\Config\Config',
            $id
        );

        if ($facilityConfig === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The facility configuration you selected could not be found.'
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
            $this->processDelete($form, $this->_request->getPost(), $facilityConfig);
        }

        $this->view->facilityConfig = $facilityConfig;
        $this->view->form = $form;
    }

    protected function processDelete($form, $input, $facilityConfig)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $this->getEntityManager()->remove($facilityConfig);
        $this->getEntityManager()->flush();

        return $this->_helper->redirector(
            'view',
            'facility',
            'facility',
            array(
                'id' => $facilityConfig->facility->id,
            )
        );
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');

        $facilityConfig = $this->getEntityManager()->find(
            'Tillikum\Entity\Facility\Config\Config',
            $id
        );

        if ($facilityConfig === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The facility configuration you selected could not be found.'
            ), 404);
        }

        $form = $this->getDi()
            ->newInstance($facilityConfig::FORM_CLASS)
            ->setAction($this->_helper->url->url())
            ->bind($facilityConfig);

        if ($this->_request->isPost()) {
            $this->processEdit($form, $this->_request->getPost(), $facilityConfig);
        }

        $this->view->facilityConfig = $facilityConfig;
        $this->view->form = $form;
    }

    protected function processEdit($form, $input, $facilityConfig)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $this->getEntityManager()->flush();

        return $this->_helper->redirector(
            'view',
            'facility',
            'facility',
            array(
                'id' => $facilityConfig->facility->id
            )
        );
    }
}
