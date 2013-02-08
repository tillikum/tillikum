<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Facility_SearchController extends Tillikum_Controller_Facility
{
    public function facilityAction()
    {
        $form = $this->getDi()
            ->newInstance('Tillikum\Form\Facility\Search')
            ->setAction($this->_helper->url->url())
            ->setMethod('GET');

        $form->setMethod('GET');

        if ($this->_request->getQuery('tillikum_submit')) {
            $this->processFacility($form, $this->_request->getQuery());
        } else {
            $form->date->setValue(date('Y-m-d'));
        }

        $this->view->form = $form;
    }

    protected function processFacility($form, $input)
    {
        if (!$form->isValid($input)) {
            return;
        }

        $values = $form->getValues();

        $qb = $this->getEntityManager()
            ->createQueryBuilder();

        $qb->select(
            array(
                'c',
                'c.capacity - COUNT(b) - COALESCE(SUM(h.space), 0) AS availableSpace',
            )
        )
            ->from('Tillikum\Entity\Facility\Config\Config', 'c')
            ->innerJoin('c.facility', 'f')
            ->leftJoin('f.bookings', 'b', 'WITH', $qb->expr()->between(':date', 'b.start', 'b.end'))
            ->leftJoin('f.holds', 'h', 'WITH', $qb->expr()->between(':date', 'h.start', 'h.end'))
            ->where($qb->expr()->between(':date', 'c.start', 'c.end'))
            ->groupBy('f')
            ->setParameter('date', new DateTime($values['date']));

        if (count($values['facilitygroup_ids'])) {
            $qb->innerJoin('f.facility_group', 'fg')
                ->andWhere($qb->expr()->in('fg.id', ':fgIds'))
                ->setParameter('fgIds', $values['facilitygroup_ids']);
        }

        if (strlen($values['gender'])) {
            $qb->andWhere($qb->expr()->eq('c.gender', ':gender'))
                ->setParameter('gender', $values['gender']);
        }

        if (strlen($values['capacity'])) {
            $qb->andWhere($qb->expr()->eq('c.capacity', ':capacity'))
                ->setParameter('capacity', $values['capacity']);
        }

        if (count($values['tags'])) {
            foreach ($values['tags'] as $tag) {
                $qb->innerJoin('c.tags', "tag_{$tag}", 'WITH', $qb->expr()->eq("tag_{$tag}.id", "'$tag'"));
            }
        }

        if (strlen($values['available_space'])) {
            $qb->having($qb->expr()->gte('availableSpace', ':availableSpace'))
                ->setParameter('availableSpace', $values['available_space']);
        }

        // available space

        $this->view->searchData = $this->_helper->dataTableFacilitySearch(
            $qb->getQuery()->getResult()
        );
    }
}
