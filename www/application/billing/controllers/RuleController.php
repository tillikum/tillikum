<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Billing_RuleController extends Tillikum_Controller_Billing
{
    public function create1Action()
    {
        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Billing\RuleType')
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

        $values = $form->getValues();

        $this->_helper->redirector(
            'create2',
            'rule',
            'billing',
            array(
                'type' => $values['type'],
            )
        );
    }

    public function create2Action()
    {
        $type = $this->_request->getParam('type');

        if ($type === null) {
            throw new \Zend_Controller_Exception(
                'You did not specify the type of billing rule to create.',
                404
            );
        }

        $entity = $this->getDi()
            ->newInstance($type);

        $form = $this->getDi()
            ->newInstance($entity::FORM_CLASS)
            ->setAction($this->_helper->url->url())
            ->bind($entity);

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

        $form->bindValues();

        /**
         * @deprecated remove old_id when migration is complete
         */
        $form->entity->old_id = 'DELETEME';

        $this->getEntityManager()->persist($form->entity);
        $this->getEntityManager()->flush();

        $this->_helper->redirector(
            'view',
            'rule',
            'billing',
            array(
                'id' => $form->entity->id,
            )
        );
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');

        $entity = $this->getEntityManager()
            ->find('Tillikum\Entity\Billing\Rule\Rule', $id);

        if ($entity === null) {
            throw new \Zend_Controller_Exception(
                'Could not find the billing rule you were trying to edit.',
                404
            );
        }

        $form = $this->getDi()
            ->newInstance($entity::FORM_CLASS)
            ->setAction($this->_helper->url->url())
            ->bind($entity);

        if ($this->_request->isPost()) {
            $this->processEdit($form, $this->_request->getPost());
        }

        $this->view->form = $form;
    }

    protected function processEdit($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $this->getEntityManager()->flush();

        $this->_helper->redirector(
            'view',
            'rule',
            'billing',
            array(
                'id' => $form->entity->id,
            )
        );
    }

    public function indexAction()
    {
        $adHocRules = $this->getEntityManager()->createQuery(
            "
            SELECT r
            FROM Tillikum\Entity\Billing\Rule\AdHoc r
            "
        )
            ->getResult();

        $facilityBookingRules = $this->getEntityManager()->createQuery(
            "
            SELECT r
            FROM Tillikum\Entity\Billing\Rule\FacilityBooking r
            "
        )
            ->getResult();

        $mealplanBookingRules = $this->getEntityManager()->createQuery(
            "
            SELECT r
            FROM Tillikum\Entity\Billing\Rule\MealplanBooking r
            "
        )
            ->getResult();

        $this->view->adHocRuleData = $this->_helper->dataTableBillingRuleAdHoc(
            $adHocRules
        );

        $this->view->facilityBookingRuleData = $this->_helper->dataTableBillingRuleFacilityBooking(
            $facilityBookingRules
        );

        $this->view->mealplanBookingRuleData = $this->_helper->dataTableBillingRuleMealplanBooking(
            $mealplanBookingRules
        );
    }

    public function viewAction()
    {
        $id = $this->_request->getParam('id');

        $rule = $this->getEntityManager()
            ->find('Tillikum\Entity\Billing\Rule\Rule', $id);

        if ($rule === null) {
            throw new \Zend_Controller_Exception(
                'Could not find the billing rule you were trying to view.',
                404
            );
        }

        switch (get_class($rule)) {
            case 'Tillikum\Entity\Billing\Rule\FacilityBooking':
                $helper = 'dataTableBillingRuleFacilityBookingConfig';
                break;
            case 'Tillikum\Entity\Billing\Rule\MealplanBooking':
                $helper = 'dataTableBillingRuleMealplanBookingConfig';
                break;
            default:
                $helper = 'dataTableBillingRuleAdHocConfig';
                break;
        }

        $this->view->configData = $this->_helper->$helper(
            $rule->configs
        );

        $this->view->rule = $rule;

        $this->view->viewHelper = $helper;
    }
}
