<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Facility_FlagController extends Tillikum_Controller_Facility
{
    public function indexAction()
    {
    }

    public function searchAction()
    {
        $flag = $this->_request->getParam('flag');

        $roomGateway = new \Tillikum\Model\Facility\RoomGateway();
        $rooms = $roomGateway->fetchManyByFlag($flag);

        $configs = new \Tillikum\Model\Facility\Configs();
        foreach ($rooms as $room) {
            foreach ($room->configs as $config) {
                if ($config->flags->exists(function($k, $e) use($flag) { return (string)$e === $flag; })) {
                    $configs->add($config);
                }
            }
        }

        $this->view->configLivedata = $this->_helper->dataTableRoomConfiguration($configs);
    }
}
