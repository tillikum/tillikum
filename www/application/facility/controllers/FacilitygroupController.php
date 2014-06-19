<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Facility_FacilitygroupController extends Tillikum_Controller_Facility
{
    public function autocompleteAction()
    {
        $dateQuery = $this->_request->getQuery('date');
        $date = $dateQuery ? new DateTime($dateQuery) : null;

        $limit = (int) ($this->_request->getQuery('limit') ?: 15);

        $input = trim($this->_request->getQuery('q'));
        $input = preg_replace('/\s{2,}/', ' ', $input);

        $qb = $this->getEntityManager()->createQueryBuilder()
        ->select('fg.id, fgc.name')
        ->from('Tillikum\Entity\FacilityGroup\FacilityGroup', 'fg')
        ->join('fg.configs', 'fgc')
        ->orderBy('fgc.name')
        ->setMaxResults($limit);

        if ($date) {
            $qb = $qb
                ->andWhere('fgc.start <= :date')
                ->andWhere('fgc.end >= :date')
                ->setParameter(':date', $date);
        }

        $qb->andWhere(
            $qb->expr()->like('fgc.name', ':input')
        )
        ->setParameter('input', $input . '%');

        $ret = array();
        foreach ($qb->getQuery()->getResult() as $facilityGroup) {
            $ret[] = array(
                'key' => $facilityGroup['id'],
                'value' => $facilityGroup['name'],
                'label' => $facilityGroup['name'],
                'uri' => $this->_helper->url('view', 'facilitygroup', 'facility', array('id' => $facilityGroup['id']))
            );
        }

        $this->_helper->json($ret);
    }

    public function viewAction()
    {
        $id = $this->_request->getParam('id');

        $facilityGroup = $this->getEntityManager()->find('Tillikum\Entity\FacilityGroup\FacilityGroup', $id);

        if (null === $facilityGroup) {
            throw new \Zend_Controller_Exception($this->getTranslator()->translate(
                'The facility group you selected could not be found.'
            ), 404);
        }

        $this->view->configRows = $this->_helper->dataTableFacilityGroupConfiguration($facilityGroup);
        $this->view->facilityGroup = $facilityGroup;
    }
}
