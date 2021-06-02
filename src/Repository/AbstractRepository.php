<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;



abstract class AbstractRepository extends EntityRepository
{
    protected function paginate(QueryBuilder $qb, $limit, $page): Pagerfanta
    {
        $pager = new Pagerfanta(new QueryAdapter($qb));
        $pager->setMaxPerPage((int) $limit);
        $pager->setCurrentPage($page);
        return $pager;
    }
}
