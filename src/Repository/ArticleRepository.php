<?php

namespace App\Repository;

class ArticleRepository extends AbstractRepository
{
    public function search($term, $order = 'asc', $limit = 5, $offset = 0)
    {
        $qb = $this
            ->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.id', $order)

        ;

        if ($term) {
            $qb
                ->where('c.name LIKE ?1')
                ->setParameter(1, '%'.$term.'%')
            ;
        }
        return $this->paginate($qb, $limit, $offset);
    }
}
