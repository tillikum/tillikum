<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Person_IndexController extends Tillikum_Controller_Person
{
    public function indexAction()
    {
        $form = $this->getDi()
            ->newInstance('PersonSearchForm');

        $form->setAction($this->_helper->url->url());

        $this->view->isSearch = false;
        if ($this->_request->getQuery($form->tillikum_submit->getFullyQualifiedName())) {
            $this->processIndex($form, $this->_request->getQuery());
        }

        $this->view->searchForm = $form;
    }

    public function processIndex($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $this->view->isSearch = true;

        $values = $form->getValues();

        $personClass = get_class(
            $this->getDi()
                ->newInstance('PersonEntity')
        );

        $query = $this->getEntityManager()->getRepository($personClass)
            ->getAutocompleteQuery($values['search']);

        $people = $query->setMaxResults(10)
            ->getResult();

        $this->view->people = array();
        foreach ($people as $person) {
            $this->view->people[] = $person;
        }

        if (count($this->view->people) === 1) {
            $this->_helper->redirector(
                'view',
                'person',
                'person',
                array(
                    'id' => $this->view->people[0]->id,
                )
            );
        }

        usort(
            $this->view->people,
            function($a, $b) {
                return strnatcmp($a->display_name, $b->display_name);
            }
        );
    }
}
