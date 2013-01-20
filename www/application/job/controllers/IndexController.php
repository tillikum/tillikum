<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Job_IndexController extends Tillikum_Controller_Job
{
    public function indexAction()
    {
        $sm = $this->getServiceManager();

        $jobsByName = array();
        foreach ($sm->get('Jobs') as $jobName) {
            $jobsByName[$jobName] = $sm->get($jobName);
        }

        $this->view->jobSummary = $this->_helper->dataTableJobSummary(
            $jobsByName
        );
    }
}
