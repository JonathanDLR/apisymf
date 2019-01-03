<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

abstract class AbstractRepository extends EntityRepository
{
    protected function paginate(QueryBuilder $qb, $limit = 20, $offset = 0)
    {
        if (0 == $limit || 0 == $offset) {
            throw new \LogicException('$limit & $offset doivent être supérieur à 0. limit: '.$limit.' offset: '.$offset.'.');
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));  

        $pager->setMaxPerPage((int) $limit);
        $currentPage = ceil(($offset +1) / $limit);
        $pager->setCurrentPage($currentPage);


        return $pager;
    }
}