<?php

namespace GameBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PlayerRepository
*/
class PlayerRepository extends EntityRepository
{
    /**
     * Gets all players ordered by wins in descending order
     *
     * @return array
     */
    public function findAllOrderByWins()
    {
        $qb = $this
            ->createQueryBuilder('p')
            ->select(array('p'))
            ->orderBy('p.wins', 'DESC');

        return $qb->getQuery()->execute();
    }
}