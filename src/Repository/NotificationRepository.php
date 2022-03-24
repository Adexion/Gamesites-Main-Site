<?php

namespace App\Repository;

use App\Entity\Notification;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function add(Notification $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Notification $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getCurrentUser(UserInterface $user): ?array
    {
        $date = (new DateTime('-5 days'))->format('Y-m-d');

        return $this->createQueryBuilder('n')
            ->leftJoin('n.users', 'u')
            ->where("u.id = {$user->getId()} OR n.users IS EMPTY")
            ->andWhere("n.datetime >= '$date'")
            ->orderBy('n.datetime', 'DESC')
            ->getQuery()
            ->execute();
    }
}
