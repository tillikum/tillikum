<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

use Tillikum\Form,
    Zend_Session as Session,
    Zend_Session_Namespace as SessionNamespace;

class Booking_BulkController extends Tillikum_Controller_Booking
{
    protected $session;

    public function init()
    {
        parent::init();

        $this->session = new SessionNamespace(__CLASS__);
        $this->session->setExpirationHops(2);
    }

    public function indexAction()
    {
        $form = new Form\Bulk\Input(array(
            'action' => $this->_helper->url('index')
        ));

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

        $values = $form->getValues();

        $this->session->parsed = $values['parsed'];

        $this->_helper->redirector('verify');
    }

    public function templateAction()
    {
        $data = array(array(
            $this->getTranslator()->translate('Person ID'),
            $this->getTranslator()->translate('Facility ID'),
            $this->getTranslator()->translate('Facility group name'),
            $this->getTranslator()->translate('Facility name'),
            $this->getTranslator()->translate('Booking start date'),
            $this->getTranslator()->translate('Booking end date'),
            $this->getTranslator()->translate('Booking rate 1 ID'),
            $this->getTranslator()->translate('Booking rate 1 start date'),
            $this->getTranslator()->translate('Booking rate 1 end date'),
            $this->getTranslator()->translate('Booking rate 2 ID'),
            $this->getTranslator()->translate('Booking rate 2 start date'),
            $this->getTranslator()->translate('Booking rate 2 end date'),
            $this->getTranslator()->translate('Booking rate 3 ID'),
            $this->getTranslator()->translate('Booking rate 3 start date'),
            $this->getTranslator()->translate('Booking rate 3 end date'),
            $this->getTranslator()->translate('Booking billed through date'),
            $this->getTranslator()->translate('Meal plan'),
            $this->getTranslator()->translate('Meal plan start date'),
            $this->getTranslator()->translate('Meal plan end date'),
            $this->getTranslator()->translate('Meal plan rate 1 ID'),
            $this->getTranslator()->translate('Meal plan rate 1 start date'),
            $this->getTranslator()->translate('Meal plan rate 1 end date'),
            $this->getTranslator()->translate('Meal plan billed through date')
        ));

        $rows = $this->getEntityManager()->createQuery(
            'SELECT f.id AS fid, fc.name AS fcname, fgc.name AS fgcname'
          . ' FROM Tillikum\Entity\Facility\Facility f'
          . ' JOIN f.configs fc'
          . ' JOIN f.facility_group fg'
          . ' JOIN fg.configs fgc'
          . ' WHERE fc.end >= :now AND fgc.end >= :now'
          . ' ORDER BY fgc.name, fc.name'
        )
        ->setParameter('now', new DateTime(date('Y-m-d')))
        ->getResult();


        foreach ($rows as $row) {
            $data[] = array('', $row['fid'], $row['fgcname'], $row['fcname']);
        }

        return $this->_helper->csv($data, 'mass_booking_template');
    }

    public function verifyAction()
    {
        set_time_limit(0);

        $form = new Tillikum_Form_MassBookingInputVerifier();
        // XXX: Make this configurable
        $form->setRequiredElements(array('id', 'booking_facility', 'booking_start', 'booking_end'));
        $s = $this->getSession();
        $sess = $s->input;

        $validHeaders = array(
            'id' => 'OSU ID',
            'booking_facility' => 'Facility ID',
            'booking_start' => 'Booking start date',
            'booking_end' => 'Booking end date',
            'booking_rate1_id' => 'Booking rate 1 ID',
            'booking_rate1_start' => 'Booking rate 1 start date',
            'booking_rate1_end' => 'Booking rate 1 end date',
            'booking_rate2_id' => 'Booking rate 2 ID',
            'booking_rate2_start' => 'Booking rate 2 start date',
            'booking_rate2_end' => 'Booking rate 2 end date',
            'booking_rate3_id' => 'Booking rate 3 ID',
            'booking_rate3_start' => 'Booking rate 3 start date',
            'booking_rate3_end' => 'Booking rate 3 end date',
            'booking_billing_effective' => 'Booking billing effective date',
            'booking_billing_through' => 'Booking billed through date',
            'mealplan_plan' => 'Meal plan',
            'mealplan_start' => 'Meal plan start date',
            'mealplan_end' => 'Meal plan end date',
            'mealplan_rate1_id' => 'Meal plan rate 1 ID',
            'mealplan_rate1_start' => 'Meal plan rate 1 start date',
            'mealplan_rate1_end' => 'Meal plan rate 1 end date',
            'mealplan_billing_effective' => 'Meal plan billing effective date',
            'mealplan_billing_through' => 'Meal plan billed through date'
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

        $s->data = $form->getData();

        $this->_helper->redirector('confirm');
    }

    public function confirmAction()
    {
        set_time_limit(0);

        $s = $this->getSession();
        $sess = $s->data;

        // Set up our form
        $form = new \Tillikum_Form();
        $form->addElement(
            new Tillikum_Form_Element_Submit(
                'tillikum_submit',
                array(
                    'attribs' => array(
                        'onclick' => 'javascript:this.disabled = true;'
                    ),
                    'label' => 'Save'
                )
            )
        );

        if ($this->_request->isPost()) {
            $this->processConfirm($form, $this->_request->getPost());
        }

        $this->view->bookingCount = 0;
        $this->view->mealplanCount = 0;
        foreach ($sess as $personId => $data) {
            if (isset($data['bookings'])) {
                $this->view->bookingCount += count($data['bookings']);
            }

            if (isset($data['mealplans'])) {
                $this->view->mealplanCount += count($data['mealplans']);
            }
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

        $values = $form->getValues(true);

        foreach ($sess as $personId => $data) {
            $person = $this->personGateway->fetch((string)$personId);

            if (isset($data['charges'])) {
                foreach ($data['charges'] as $charge) {
                    $person->charges->add($charge);
                }
            }

            if (isset($data['bookings'])) {
                foreach ($data['bookings'] as $booking) {
                    $person->bookings->add($booking);
                }
            }

            if (isset($data['mealplans'])) {
                foreach ($data['mealplans'] as $mealplan) {
                    $person->mealplans->add($mealplan);
                }
            }

            $person->save();
        }

        unset($s->data);

        $this->_helper->redirector('index', 'index');
    }

    public function getSession()
    {
        if ($this->_session === null) {
            $this->_session = new Zend_Session_Namespace(__CLASS__);
        }

        return $this->_session;
    }
}
