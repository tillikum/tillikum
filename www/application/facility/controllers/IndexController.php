<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Facility_IndexController extends Tillikum_Controller_Facility
{
    public function indexAction()
    {
        $facilityGroupForm = $this->getDi()
            ->newInstance('Tillikum\Form\FacilityGroup\Find')
            ->setElementsBelongTo('facilitygroup');

        $facilityForm = $this->getDi()
            ->newInstance('Tillikum\Form\Facility\Find')
            ->setElementsBelongTo('facility');

        $this->view->facilityGroupForm = $facilityGroupForm;
        $this->view->facilityForm = $facilityForm;
    }
}
