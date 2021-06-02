<?php

namespace App\Repository;

class ArticleRepository extends AbstractRepository
{
    public function search($keyword, $order = 'asc', $limit, $page, $param)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.id', $order)

        ;
        if ($keyword) {
            $qb
                ->where('c.name LIKE ?1')
                ->setParameter(1, '%'.$keyword.'%')
            ;
        }

        if ($param) {
            $qb
                ->where('c.client = :param')
                ->setParameter('param', $param)
            ;
        }
        return $this->paginate($qb, $limit, $page);
    }
}
