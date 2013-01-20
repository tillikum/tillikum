<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Report_ReportController extends Tillikum_Controller_Report
{
    public function viewAction()
    {
        $reportName = $this->_request->getParam('name');

        if ($reportName === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'Sorry, you did not specify which report to load.'
                ),
                404
            );
        }

        $report = $this->getServiceManager()->get($reportName);
        $form = $this->getServiceManager()->get($report->getFormClass());
        $form->setAction($this->_helper->url->url());

        if ($this->_request->getQuery('tillikum_submit')) {
            $this->processView($form, $this->_request->getQuery(), $report);
        }

        $this->view->form = $form;
        $this->view->reportName = $report->getName();
        $this->view->reportDescription = $report->getDescription();
    }

    protected function processView($form, $input, $report)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $values = $form->getValues();

        $report->setParameters($values);
        $rows = $report->generate();

        switch ($values['format']) {
            case 'csv':
                return $this->_helper->csv(
                    $rows,
                    str_replace(' ', '_', strtolower($report->getName()))
                );
                break;
            case 'html':
                $this->view->reportData = $this->_helper->dataTableReport($rows);
                break;
            default:
                throw new \Zend_Controller_Exception(
                    $this->getTranslator()->translate(
                        'You need to specify either a CSV or HTML report format.'
                    ),
                    400
                );
                break;
        }
    }
}
