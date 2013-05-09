<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

use Tillikum\Entity;
use Tillikum\Form;

class Billing_RuleconfigController extends Tillikum_Controller_Billing
{
    protected $ruleConfig;

    public function copyAction()
    {
        $id = $this->_request->getParam('id');

        $this->ruleConfig = $this->getEntityManager()->find(
            'Tillikum\Entity\Billing\Rule\Config\Config',
            $id
        );

        if ($this->ruleConfig === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The rule configuration you selected could not be found.'
                ),
                404
            );
        }

        $this->ruleConfig = clone($this->ruleConfig);

        $configClass = get_class($this->ruleConfig);

        $formClass = $configClass::FORM_CLASS;

        $form = $this
            ->getDi()
            ->newInstance($formClass);

        $form
            ->setAction($this->_helper->url->url())
            ->addElement($form->createSubmitElement(array('label' => 'Copy')))
            ->bind($this->ruleConfig);

        $form
            ->getElement('strategy')
            ->setMultiOptions(
                $this->getBillingStrategyOptions()
            );

        if ($this->_request->isPost()) {
            $this->processCopy($form, $this->_request->getPost());
        }

        $this->view->form = $form;
        $this->view->rule = $this->ruleConfig->rule;
    }

    protected function processCopy($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $this->getEntityManager()->persist($this->ruleConfig);
        $this->getEntityManager()->flush();

        return $this->_helper->redirector(
            'view',
            'rule',
            'billing',
            array(
                'id' => $this->ruleConfig->rule->id,
            )
        );
    }

    public function createAction()
    {
        $ruleId = $this->_request->getParam('rule_id');

        $rule = $this->getEntityManager()->find(
            'Tillikum\Entity\Billing\Rule\Rule',
            $ruleId
        );

        if ($rule === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The rule you are trying to create the configuration for could not be found.'
                ),
                404
            );
        }

        $ruleClass = get_class($rule);

        /**
         * @todo This needs to be cleaned up to use entity metadata somehow
         */
        if ($ruleClass === 'Tillikum\Entity\Billing\Rule\AdHoc') {
            $configClass = 'Tillikum\Entity\Billing\Rule\Config\AdHoc';
        } elseif ($ruleClass === 'Tillikum\Entity\Billing\Rule\FacilityBooking') {
            $configClass = 'Tillikum\Entity\Billing\Rule\Config\FacilityBooking';
        } elseif ($ruleClass === 'Tillikum\Entity\Billing\Rule\MealplanBooking') {
            $configClass = 'Tillikum\Entity\Billing\Rule\Config\MealplanBooking';
        } else {
            $configClass = null;
        }

        if ($configClass === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'Creating a new rule configuration for that rule is unsupported at this time.'
                ),
                404
            );
        }

        $this->ruleConfig = new $configClass();
        $this->ruleConfig->rule = $rule;

        $formClass = $configClass::FORM_CLASS;

        $form = $this
            ->getDi()
            ->newInstance($formClass);

        $form
            ->setAction($this->_helper->url->url())
            ->addElement($form->createSubmitElement(array('label' => 'Create')))
            ->bind($this->ruleConfig);

        $form
            ->getElement('strategy')
            ->setMultiOptions(
                $this->getBillingStrategyOptions()
            );

        if ($this->_request->isPost()) {
            $this->processCreate($form, $this->_request->getPost());
        }

        $this->view->form = $form;
        $this->view->rule = $rule;
    }

    protected function processCreate($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $this->getEntityManager()->persist($this->ruleConfig);
        $this->getEntityManager()->flush();

        return $this->_helper->redirector(
            'view',
            'rule',
            'billing',
            array(
                'id' => $this->ruleConfig->rule->id,
            )
        );
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');

        $this->ruleConfig = $this->getEntityManager()->find(
            'Tillikum\Entity\Billing\Rule\Config\Config',
            $id
        );

        if ($this->ruleConfig === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The rule configuration you selected could not be found.'
                ),
                404
            );
        }

        $form = $this->getDi()
            ->newInstance('Tillikum_Form')
            ->setAction($this->_helper->url->url());

        $form->addElement(
            $form->createSubmitElement(array('label' => 'Delete',))
        );

        if ($this->_request->isPost()) {
            $this->processDelete($form, $this->_request->getPost());
        }

        $this->view->form = $form;
        $this->view->rule = $this->ruleConfig->rule;
    }

    protected function processDelete($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $this->getEntityManager()->remove($this->ruleConfig);
        $this->getEntityManager()->flush();

        return $this->_helper->redirector(
            'view',
            'rule',
            'billing',
            array(
                'id' => $this->ruleConfig->rule->id,
            )
        );
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');

        $this->ruleConfig = $this->getEntityManager()->find(
            'Tillikum\Entity\Billing\Rule\Config\Config',
            $id
        );

        if ($this->ruleConfig === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The rule configuration you selected could not be found.'
                ),
                404
            );
        }

        $configClass = get_class($this->ruleConfig);

        $formClass = $configClass::FORM_CLASS;

        $form = $this
            ->getDi()
            ->newInstance($formClass);

        $form
            ->setAction($this->_helper->url->url())
            ->addElement($form->createSubmitElement(array('label' => 'Save')))
            ->bind($this->ruleConfig);

        $form
            ->getElement('strategy')
            ->setMultiOptions(
                $this->getBillingStrategyOptions()
            );

        if ($this->_request->isPost()) {
            $this->processEdit($form, $this->_request->getPost());
        }

        $this->view->form = $form;
        $this->view->rule = $this->ruleConfig->rule;
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
            'rule',
            'billing',
            array(
                'id' => $this->ruleConfig->rule->id,
            )
        );
    }

    protected function getBillingStrategyOptions()
    {
        $strategies = $this
            ->getServiceManager()
            ->get('BillingStrategies');

        $multiOptions = array();
        foreach ($strategies as $strategy) {
            $multiOptions[$strategy] = $this->getServiceManager()->get($strategy)->getName();
        }
        asort($multiOptions);

        return $multiOptions;
    }
}
