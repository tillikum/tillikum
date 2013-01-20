<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

use Tillikum\Loader\PluginLoader;

class Facility_SearchController extends Tillikum_Controller_Facility
{
    public function availabilityAction()
    {
        $loader = new PluginLoader();

        $form = newv($loader->load('Tillikum\Form\Facility\Availability'), array(
            array(
                'em' => $this->getEntityManager()
            )
        ));

        $form->setMethod('GET');
        $form->date->setValue(date('Y-m-d'));

        if ($this->_request->getQuery('tillikum_submit')) {
            $this->processAvailability($form, $this->_request->getQuery());
        }

        $this->view->form = $form;
    }

    protected function processAvailability($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $values = $form->getValues(true);

        $date = new DateTime($values['date']);
        unset($values['date']);

        if (empty($values['limit'])) {
            $limit = null;
        } else {
            $limit = $values['limit'];
        }

        unset($values['limit']);

        foreach ($values as $key => $value) {
            if (empty($value)) {
                unset($values[$key]);
            }
        }

        $this->view->bookingSearchDataTable = $this->view->dataTableBookingSearch(
            $this->_helper->dataTableBookingSearch($values, $date, $limit)
        );
    }
}
