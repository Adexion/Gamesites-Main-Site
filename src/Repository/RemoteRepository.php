<?php

namespace App\Repository;

use App\Entity\Server;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

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
}