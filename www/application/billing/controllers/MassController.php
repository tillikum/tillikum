<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Billing_MassController extends Tillikum_Controller_Billing
{
    protected $session;

    public function indexAction()
    {
        $form = new Tillikum_Form_MassInput();

        if ($this->_request->isPost()) {
            $this->processIndex($form, $this->_request->getPost());
        }

        $this->view->form = $form;
    }

    protected function processIndex($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $values = $form->getValues(true);

        $sess = array();

        $sess['header'] = $values['header'];
        $sess['rows'] = $values['rows'];

        $s = $this->getSession();
        $s->input = $sess;

        $this->_helper->redirector('verify');
    }

    public function verifyAction()
    {
        set_time_limit(0);

        $form = new Tillikum_Form_MassBillingInputVerifier();
        // XXX: Make this configurable
        $form->setRequiredElements(array('id', 'code', 'amount'));
        $s = $this->getSession();
        $sess = $s->input;

        $validHeaders = array(
            'id' => 'OSU ID',
            'code' => 'Rate code',
            'amount' => 'Amount',
            'note' => 'Notes'
        );

        foreach ($sess['header'] as $column => $header) {
            $form->addRow(
                $column,
                $header,
                $validHeaders
            );
        }

        $this->view->dataErrors = array();

        if ($this->_request->isPost()) {
            $this->processVerify($form, $this->_request->getPost());
        } else {
            foreach ($validHeaders as $validColumn => $validHeader) {
                $i = 'A';
                foreach ($sess['header'] as $column) {
                    if (strcasecmp($validHeader, $column) === 0) {
                        $form->getSubForm('map')->$i->setValue($validColumn);
                        break;
                    }
                    $i++;
                }
            }
        }

        $this->view->json_table_header = json_encode($sess['header']);
        $this->view->json_table_body = json_encode($sess['rows']);

        $this->view->form = $form;
    }

    protected function processVerify($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $s = $this->getSession();
        $sess = $s->input;

        $values = $form->getValues(true);

        // Map column ids to their CSV offsets
        $columnIdToCsvOffsetMap = array_flip(array_values($values['map']));
        // Check CSV data
        $result = $form->checkMappedData($columnIdToCsvOffsetMap, $sess['rows']);

        if (!$result['result']) {
            $this->view->dataErrors[] = "Row {$result['row']}: {$result['reason']}";
            return;
        }

        unset($s->input);

        $s->data = array('charges' => $form->getCharges());

        $this->_helper->redirector('confirm');
    }

    public function confirmAction()
    {
        set_time_limit(0);

        $s = $this->getSession();
        $sess = $s->data;

        // Set up our form
        $form = new Tillikum_Form();
        $form->addElement(
            new Tillikum_Form_Element_Submit(
                'tillikum_submit',
                array(
                    'label' => 'Submit…',
                    'onclick' => 'javascript:this.disabled = true;'
                )
            )
        );

        $this->view->personCount = count(array_keys($sess['charges']));
        $this->view->chargeCount = 0;

        foreach ($sess['charges'] as $personId => $charges) {
            $this->view->chargeCount += count($charges);
        }

        if ($this->_request->isPost()) {
            $this->processConfirm($form, $this->_request->getPost());
        }

        $this->view->form = $form;
    }

    protected function processConfirm($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $s = $this->getSession();
        $sess = $s->data;

        $personGateway = new \TillikumX\Model\PersonGateway();

        foreach ($sess['charges'] as $personId => $charges) {
            $person = $personGateway->fetch((string)$personId);

            foreach ($charges as $charge) {
                $person->charges->add($charge);
            }

            $person->save();
        }

        unset($s->data);
        $this->_helper->redirector('index', 'index');
    }

    public function getSession()
    {
        if ($this->session === null) {
            $this->session = new Zend_Session_Namespace(__CLASS__);
        }

        return $this->session;
    }
}
