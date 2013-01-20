<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

use Tillikum\Entity;

class Job_JobController extends Tillikum_Controller_Job
{
    public function createAction()
    {
        $jobName = $this->_request->getParam('name');

        if ($jobName === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'Sorry, you did not specify which job to load.'
                ),
                404
            );
        }

        $jobEntity = $this->getDi()
            ->newInstance('Tillikum\Entity\Job\Job');

        $job = $this->getDi()
            ->newInstance($jobName);

        $form = $this->getDi()
            ->newInstance($job->getFormClass());

        $form->setAction($this->_helper->url->url())
            ->bind($jobEntity);

        if ($this->_request->isPost()) {
            $this->processCreate($form, $this->_request->getPost());
        } else {
            $form->identity->setValue(
                $this->getAuthenticationService()->getIdentity()
            );
        }

        $this->view->form = $form;
        $this->view->jobName = $job->getName();
        $this->view->jobDescription = $job->getDescription();
    }

    protected function processCreate($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $jobEntity = $form->entity;

        $jobEntity->class_name = $this->_request->getParam('name');
        $jobEntity->run_state = Entity\Job\Job::RUN_STATE_SUBMITTED;
        $jobEntity->job_state = Entity\Job\Job::JOB_STATE_SUCCESS;

        foreach ($form->getValues() as $key => $value) {
            $parameter = new Entity\Job\Parameter\Parameter();
            $parameter->job = $jobEntity;
            $parameter->label = $form->getElement($key)->getLabel() ?: $key;
            $parameter->key = $key;
            $parameter->value = $value;

            $this->getEntityManager()->persist($parameter);
        }

        $this->getEntityManager()->persist($jobEntity);
        $this->getEntityManager()->flush();

        $this->_helper->redirector(
            'view',
            'job',
            'job',
            array(
                'id' => $jobEntity->id,
            )
        );
    }

    public function viewAction()
    {
        $id = $this->_request->getParam('id');

        $jobEntity = $this->getEntityManager()
            ->find('Tillikum\Entity\Job\Job', $id);

        if ($jobEntity === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The job you were looking for could not be found.'
                ),
                404
            );
        }

        $this->view->jobEntity = $jobEntity;
        $this->view->job = $this->getDi()
            ->newInstance($jobEntity->class_name);
    }

    public function historyAction()
    {
        $jobs = $this->getEntityManager()
            ->createQuery(
                "
                SELECT j
                FROM Tillikum\Entity\Job\Job j
                "
            )
            ->getResult();

        $this->view->jobHistoryData = $this->_helper->dataTableJobHistory($jobs);
    }
}
