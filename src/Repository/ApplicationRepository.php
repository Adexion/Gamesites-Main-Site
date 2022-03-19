<?php

namespace App\Repository;

use App\Entity\Application;
use App\Entity\Workspace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    public function getCurrentApplications(?Workspace $workspace, UserInterface $user): array
    {
        $qb = $this->createQueryBuilder('s')
            ->join('s.client', 'c')
            ->where('c.id = :cid AND s.workspace is null')
            ->setParameter(':cid', $user->getId());

        if ($workspace) {
            $qb->join('s.workspace', 'w')
                ->orWhere('w = :id')
                ->setParameter(':id', $workspace);
        }

        return $qb
            ->getQuery()
            ->execute();
    }

    public function getUserApplications(UserInterface $user): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.workspace', 'w')
            ->join('w.users', 'u1')
            ->join('s.client', 'u2')
            ->where('u1.id = ' . $user->getId())
            ->orWhere('u2.id = ' . $user->getId())
            ->getQuery()
            ->execute();
    }
}
