<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ApplicationRepository;
use App\Repository\RemoteRepository;
use Doctrine\DBAL\Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class ApplicationService
{
    private ApplicationRepository $applicationRepository;
    private UserPasswordHasherInterface $hasher;

    public function __construct(ApplicationRepository $applicationRepository, UserPasswordHasherInterface $hasher)
    {
        $this->applicationRepository = $applicationRepository;
        $this->hasher = $hasher;
    }

    /** @throws Exception */
    public function setPasswordForAllApplications(UserInterface $user, ?string $password, bool $each): User
    {
        if (!$user instanceof User) {
            throw new UserNotFoundException();
        }

        if (!$password) {
            return $user;
        }

        $user->setPassword($this->hasher->hashPassword($user, $password));

        if (!$each) {
            return $user;
        }

        foreach ($this->applicationRepository->getUserApplications($user) as $application) {
            (new RemoteRepository($application->getDir()))->updateUserPassword($user, $password);
        }

        return $user;
    }
}