<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Facility_FacilityController extends Tillikum_Controller_Facility
{
    public function autocompleteAction()
    {
        $dateQuery = $this->_request->getQuery('date');
        $date = $dateQuery ? new DateTime($dateQuery) : null;

        $limit = (int) ($this->_request->getQuery('limit') ?: 15);

        $input = trim($this->_request->getQuery('q'));
        $input = preg_replace('/\s{2,}/', ' ', $input);

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('f.id, fc.name fname, fgc.name fgname')
            ->from('Tillikum\Entity\Facility\Facility', 'f')
            ->join('f.configs', 'fc')
            ->join('f.facility_group', 'fg')
            ->join('fg.configs', 'fgc')
            ->andWhere('f.facility_group = fg')
            ->groupBy('fgname, fname')
            ->orderBy('fgname, fname')
            ->setMaxResults($limit);

        if ($date) {
            $qb = $qb
                ->andWhere('fc.start <= :date')
                ->andWhere('fgc.start <= :date')
                ->andWhere('fc.end >= :date')
                ->andWhere('fgc.end >= :date')
                ->setParameter(':date', $date);
        }

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->like(
                    $qb->expr()->concat(
                        'fgc.name', $qb->expr()->concat(
                            $qb->expr()->literal(' '), 'fc.name'
                        )
                    ),
                    ':input'
                ),
                $qb->expr()->like(
                    $qb->expr()->concat(
                        'fc.name', $qb->expr()->concat(
                            $qb->expr()->literal(' '), 'fgc.name'
                        )
                    ),
                    ':input'
                )
            )
        )
        ->setParameter('input', $input . '%');

        $ret = array();
        foreach ($qb->getQuery()->getResult() as $facility) {
            $name = implode(' ', array($facility['fgname'], $facility['fname']));
            $ret[] = array(
                'key' => $facility['id'],
                'value' => $name,
                'label' => $name,
                'uri' => $this->_helper->url('view', 'facility', 'facility', array('id' => $facility['id']))
            );
        }

        $this->_helper->json($ret);
    }

    public function create1Action()
    {
        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Facility\Type')
            ->setAction($this->_helper->url->url());

        if ($this->_request->isPost()) {
            $this->processCreate1($form, $this->_request->getPost());
        }

        $this->view->form = $form;
    }

    protected function processCreate1($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $values = $form->getValues();

        $this->_helper->redirector(
            'create2',
            'facility',
            'facility',
            array('type' => $values['type'])
        );
    }

    public function create2Action()
    {
        $type = $this->_request->getParam('type');

        if ($type === null) {
            throw new \Zend_Controller_Exception(
                'You did not specify the type of facility to create.',
                404
            );
        }

        $entity = $this->getDi()
            ->newInstance($type);

        $form = $this->getDi()
            ->newInstance($entity::FORM_CLASS)
            ->setAction($this->_helper->url->url());
        $form->bind($entity);

        if ($this->_request->isPost()) {
            $this->processCreate2($form, $this->_request->getPost());
        }

        $this->view->form = $form;
    }

    protected function processCreate2($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $form->bindValues();

        $this->getEntityManager()->persist($form->facility);
        $this->getEntityManager()->flush();

        $this->_helper->redirector(
            'view',
            'facility',
            'facility',
            array('id' => $form->facility->id)
        );
    }

    public function defaultruleAction()
    {
        $facilityId = $this->_request->getParam('id');
        $start = new DateTime($this->_request->getQuery('start') ?: date('Y-m-d'));
        $end = new DateTime($this->_request->getQuery('end') ?: date('Y-m-d'));

        $ruleIds = $this->getEntityManager()
            ->createQuery(
                "
                SELECT r.id FROM Tillikum\Entity\Facility\Config\Config c
                JOIN c.facility f
                JOIN Tillikum\Entity\Billing\Rule\FacilityBooking r WITH r = c.default_billing_rule
                WHERE f.id = :id AND c.end >= :start AND c.start <= :end
                "
            )
            ->setParameter('id', $facilityId)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getResult();

        if (count($ruleIds) === 0) {
            return $this->_helper->json('');
        }

        return $this->_helper->json($ruleIds[0]['id']);
    }

    public function viewAction()
    {
        $id = $this->_request->getParam('id');

        $facility = $this->getEntityManager()->find(
            'Tillikum\Entity\Facility\Facility',
            $id
        );

        if ($facility === null) {
            throw new \Zend_Controller_Exception(
                $this->getTranslator()->translate(
                    'The facility you selected could not be found.'
                ),
                404
            );
        }

        $this->view->bookingRows = $this->_helper->dataTableFacilityBooking($facility);
        $this->view->configRows = $this->_helper->dataTableFacilityConfiguration($facility);
        $this->view->holdRows = $this->_helper->dataTableFacilityHold($facility);

        $this->view->facility = $facility;
    }
}
