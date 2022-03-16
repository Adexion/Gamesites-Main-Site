<?php

namespace App\Repository;

use App\Entity\Server;
use App\Entity\User;
use App\Entity\Workspace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @method Server|null find($id, $lockMode = null, $lockVersion = null)
 * @method Server|null findOneBy(array $criteria, array $orderBy = null)
 * @method Server[]    findAll()
 * @method Server[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Server::class);
    }

    public function getWorkspaceServer(Workspace $workspace, User $user){
        return $this->createQueryBuilder('s')
            ->join(Workspace::class, 'w', 's.workspace = w.id')
            ->join(User::class, 'c', 's.client = c.id')
            ->where('w.id = :id')
            ->orWhere('c.id = :cid')
            ->setParameter(':id', $workspace->getId())
            ->setParameter(':cid', $user->getId())
            ->getQuery()
            ->execute();
    }
}
