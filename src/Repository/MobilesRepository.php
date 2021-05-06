<?php

namespace App\Repository;

use App\Entity\Mobiles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Mobiles|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mobiles|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mobiles[]    findAll()
 * @method Mobiles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MobilesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mobiles::class);
    }

    // /**
    //  * @return Mobiles[] Returns an array of Mobiles objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Mobiles
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
