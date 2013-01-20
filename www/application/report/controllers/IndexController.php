<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Report_IndexController extends Tillikum_Controller_Report
{
    public function indexAction()
    {
        $sm = $this->getServiceManager();

        $reportsByName = array();
        foreach ($sm->get('Reports') as $reportName) {
            $reportsByName[$reportName] = $sm->get($reportName);
        }

        $this->view->reportSummary = $this->_helper->dataTableReportSummary(
            $reportsByName
        );
    }
}
