<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

use Zend\Authentication;

class AuthController extends Tillikum_Controller_Action
{
    public function loginAction()
    {
        $authService = $this->getAuthenticationService();

        $form = null;
        if (!$authService->hasIdentity()) {
            $form = $this->getDi()
                ->newInstance('Tillikum\Form\Login')
                ->setAction($this->_helper->url->url());
        }

        if ($this->_request->isPost()) {
            $this->processLogin($form, $this->_request->getPost());
        }

        $this->view->form = $form;
    }

    protected function processLogin($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $authService = $this->getAuthenticationService();

        $values = $form->getValues();

        $adapter = $authService->getAdapter();

        if (method_exists($adapter, 'setUsername')) {
            $adapter->setUsername($values['username']);
        }

        if (method_exists($adapter, 'setPassword')) {
            $adapter->setPassword($values['password']);
        }

        $result = $authService->authenticate();

        if (!$result->isValid()) {
            $form->password->setErrors(
                array(
                    $this->getTranslator()->translate(
                        'Could not authenticate using your supplied username' .
                        ' and password combination.'
                    ),
                )
            );

            foreach ($result->getMessages() as $message) {
                $this->getLog()->info($message);
            }

            return;
        }

        $this->_helper->redirector(
            'index',
            'index',
            'person'
        );
    }

    public function logoutAction()
    {
        $authService = $this->getAuthenticationService();

        if ($authService->hasIdentity()) {
            $authService->clearIdentity();
        }
    }
}
