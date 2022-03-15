<?php

namespace App\Repository;

use App\Entity\Server;
use App\Entity\User;
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
            ['url' =>"mysql://symfony:8bb725a4w3K*@127.0.0.1:3306/$dir"],
            new Configuration()
        );
    }

    /** @throws Exception */
    public function insertConfiguration(Server $server)
    {
        $this->con->insert('configuration', [
            'server_name' => $server->getName(),
            'ip' => $server->getDomain(),
            'template' => 'client'
        ]);
    }

    public function insertUsers(UserInterface $user)
    {
        $this->con->insert('user', [
            'email' => 'biuro@gamesites.pl',
            'roles' => '["ROLE_ADMIN"]',
            'password' => '$2y$13$qGrP.kZHAj0zXXVj5E9ASereKEtXl25ii0ofqJ41jduB2clDKaA9y'
        ]);

        $this->con->insert('user', [
            'email' => $user->getUserIdentifier(),
            'roles' => '["ROLE_ADMIN"]',
            'password' => $user->getPassword()
        ]);
    }
}