<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Mealplan_MealplanController extends Tillikum_Controller_Mealplan
{
    public function defaultruleAction()
    {
        $mealplanId = $this->_request->getParam('id');
        $start = new DateTime($this->_request->getQuery('start') ?: date('Y-m-d'));
        $end = new DateTime($this->_request->getQuery('end') ?: date('Y-m-d'));

        $ruleIds = $this->getEntityManager()
            ->createQuery(
                "
                SELECT r.id FROM Tillikum\Entity\Mealplan\Mealplan m
                JOIN Tillikum\Entity\Billing\Rule\MealplanBooking r WITH r = m.default_billing_rule
                WHERE m.id = :id AND m.is_active = true
                "
            )
            ->setParameter('id', $mealplanId)
            ->getResult();

        if (count($ruleIds) === 0) {
            return $this->_helper->json('');
        }

        return $this->_helper->json($ruleIds[0]['id']);
    }
}
