<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Wyb\Lib\DB;

class ConnectionManager
{
    protected ?Connection $connection = null;

    public function getConnection(): Connection
    {
        return $this->connection ?: $this->createConnection();
    }

    protected function createConnection()
    {
        $config = DB::connection()->getConfig();
        $connectionParams = [
            'dbname' => $config['database'],
            'user' => $config['username'],
            'password' => $config['password'],
            'host' => $config['host'],
            'driver' => 'pdo_' . $config['driver'],
        ];

        return $this->connection = DriverManager::getConnection($connectionParams);
    }

}
