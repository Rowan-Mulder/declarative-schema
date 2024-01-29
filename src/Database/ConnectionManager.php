<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use MichelJonkman\DeclarativeSchema\Schema;

class ConnectionManager
{
    protected ?Connection $connection = null;

    public function __construct(protected Schema $schema)
    {
    }

    public function getConnection(): Connection
    {
        return $this->connection ?: $this->createConnection();
    }

    protected function createConnection()
    {
        return $this->connection = DriverManager::getConnection($this->schema->config('connection', []));
    }

}
