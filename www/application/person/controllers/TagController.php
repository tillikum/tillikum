<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Person_TagController extends Tillikum_Controller_Person
{
    public function createAction()
    {
        $tag = $this->getDi()
            ->newInstance('Tillikum\Entity\Person\Tag');

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Person\Tag')
            ->setAction($this->_helper->url->url())
            ->bind($tag);

        $this->view->tag = $tag;
        $this->view->form = $form;

        if ($this->_request->isPost()) {
            if (!$form->isValid($this->_request->getPost())) {
                return;
            }

            $form->bindValues();
            $this->getEntityManager()->persist($tag);

            $this->getEntityManager()->flush();

            return $this->_helper->redirector('index');
        }
    }

    public function deleteAction()
    {
        $tagId = $this->_request->getParam('id');

        $tag = $this->getEntityManager()->find(
            'Tillikum\Entity\Person\Tag',
            $tagId
        );

        if ($tag === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The tag you are trying to delete could not be found.'
            ), 404);
        }

        $form = $this->getDi()
            ->newInstance('Tillikum_Form')
            ->setAction($this->_helper->url->url());
        $form->addElement(
            $form->createSubmitElement(
                array(
                    'label' => 'Delete',
                )
            )
        );

        $this->view->tag = $tag;
        $this->view->form = $form;

        if ($this->_request->isPost()) {
            if (!$form->isValid($this->_request->getPost())) {
                return;
            }

            $this->getEntityManager()->remove($tag);

            $this->getEntityManager()->flush();

            return $this->_helper->redirector('index');
        }
    }

    public function editAction()
    {
        $tagId = $this->_request->getParam('id');

        $tag = $this->getEntityManager()->find(
            'Tillikum\Entity\Person\Tag',
            $tagId
        );

        if ($tag === null) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The tag you are trying to edit could not be found.'
            ), 404);
        }

        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Person\Tag')
            ->setAction($this->_helper->url->url())
            ->bind($tag);

        $this->view->tag = $tag;
        $this->view->form = $form;

        if ($this->_request->isPost()) {
            if (!$form->isValid($this->_request->getPost())) {
                return;
            }

            $form->bindValues();

            $this->getEntityManager()->persist($tag);

            $this->getEntityManager()->flush();

            return $this->_helper->redirector('index');
        }
    }

    public function indexAction()
    {
        $rows = array();
        $tags = $this->getEntityManager()
            ->getRepository('Tillikum\Entity\Person\Tag')
            ->findAll();

        $this->view->canEdit = $this->getAcl()->isAllowed('_user', 'person', 'write');
        $this->view->canDelete = $this->getAcl()->isAllowed('_user', 'person', 'write');
        $this->view->tags = $tags;
        $this->view->warnings = array();
    }

    public function searchAction()
    {
        $tags = explode(' ', $this->_request->getParam('ids'));

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from('Tillikum\Entity\Person\Person', 'p');

        if (count($tags) > 0) {
            foreach ($tags as $i => $tag) {
                $qb->join('p.tags', "t$i")
                ->andWhere(sprintf("t$i.id = ?$i", $i))
                ->setParameter($i, $tag);
            }

            $people = $qb->getQuery()->getResult();
        } else {
            $people = array();
        }

        $this->view->people = $people;
        $this->view->tags = $tags;
    }
}
