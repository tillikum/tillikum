<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class ErrorController extends Tillikum_Controller_Action
{
    public function postDispatch()
    {
        parent::postDispatch();

        $this->view->headTitle('Error');
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        $bootstrap = $this->getInvokeArg('bootstrap');

        $exception = $errors->exception;
        $exceptionClass = get_class($exception);

        $this->view->exc_class = $exceptionClass;
        $this->view->exc_code = $exception->getCode();
        $this->view->exc_file = $exception->getFile();
        $this->view->exc_line = $exception->getLine();
        $this->view->exc_message = $exception->getMessage();
        $this->view->exc_trace = $exception->getTraceAsString();

        // Basic ZF routing errors
        switch ($errors->type) {
            case \Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
            case \Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case \Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
                $this->getLog()->warn(sprintf(
                    '%s: %s',
                    $exceptionClass,
                    $this->view->exc_message
                ));
                $this->getResponse()->setHttpResponseCode(404);

                if ((bool) ini_get('display_errors')) {
                    $this->render('error-development');
                } else {
                    $this->render('404');
                }

                return;
                break;
            default:
                break;
        }

        // Non-routing controller exceptions, e.g. an entity in the database
        // could not be found
        if ($exceptionClass === 'Zend_Controller_Exception') {
            $this->getLog()->warn(sprintf(
                '%s, code %s: %s',
                $exceptionClass,
                $exception->getCode(),
                $exception->getMessage()
            ));
            $this->getResponse()->setHttpResponseCode(
                $exception->getCode()
            );
        // Nastier stuff
        } else {
            $this->getLog()->err(sprintf(
                '%s, code %s: %s',
                $exceptionClass,
                $exception->getCode(),
                $exception->getMessage()
            ));
            $this->getResponse()->setHttpResponseCode(500);
        }

        if ((bool) ini_get('display_errors')) {
            $this->render('error-development');
        } else {
            $this->render('error-production');
        }
    }
}
