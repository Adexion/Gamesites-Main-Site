<?php

namespace App\Repository;

use App\Entity\Application;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Symfony\Component\Security\Core\User\UserInterface;

class RemoteRepository
{
    private Connection $con;

    /** @throws Exception */
    public function __construct(string $dir)
    {
        $this->con = DriverManager::getConnection(
            ['url' => "mysql://symfony:8bb725a4w3K*@127.0.0.1:3306/$dir"],
            new Configuration()
        );
    }

    /** @throws Exception */
    public function insertConfiguration(Application $application)
    {
        $this->con->insert('configuration', [
            'ip' => $application->getDomain(),
            'template' => 'client',
            'logo' => 'minecraft.png',
        ]);
    }

    public function insertUsers(UserInterface $user)
    {
        $this->con->insert('user', [
            'email' => 'biuro@gamesites.pl',
            'roles' => '["ROLE_ADMIN"]',
            'password' => '$2y$13$qGrP.kZHAj0zXXVj5E9ASereKEtXl25ii0ofqJ41jduB2clDKaA9y',
        ]);

        $this->con->insert('user', [
            'email' => $user->getUserIdentifier(),
            'roles' => '["ROLE_ADMIN"]',
            'password' => $user->getPassword(),
        ]);
    }

    public function updateUserPassword(UserInterface $user, string $password)
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        try {
            $this->con->insert('user', [
                'email' => $user->getUserIdentifier(),
                'roles' => '["ROLE_ADMIN"]',
                'password' => $hash,
            ]);
        } catch (Exception $e) {}

        $this->con->update('user', ['password' => $hash], [
            'email' => $user->getUserIdentifier(),
        ]);
    }

    /** @throws Exception */
    public function isUserExist(string $email): bool
    {
        return (bool)$this->con->createQueryBuilder()
            ->select('email')
            ->from('user')
            ->where('email = "' . $email .'"')
            ->fetchOne();
    }
}