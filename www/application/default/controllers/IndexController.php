<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class IndexController extends Tillikum_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->redirector('index', 'index', 'person');
    }
}
