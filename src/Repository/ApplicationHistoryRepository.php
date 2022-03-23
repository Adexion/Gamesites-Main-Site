<?php

namespace App\Repository;

use App\Entity\ApplicationHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ApplicationHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApplicationHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApplicationHistory[]    findAll()
 * @method ApplicationHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApplicationHistory::class);
    }
}
