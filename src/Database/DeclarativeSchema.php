<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

abstract class DeclarativeSchema
{
    protected ?AbstractSchemaManager $schemaManager = null;

    public function init(AbstractSchemaManager $schemaManager): void
    {
        $this->schemaManager = $schemaManager;
    }

    /**
     * @throws Exception
     */
    protected function existing(string $name): Table
    {
        $table = $this->schemaManager->introspectTable($name);

        return new Table($table->getName(), $table->getColumns(), $table->getIndexes(), $table->getUniqueConstraints(), $table->getForeignKeys(), $table->getOptions());
    }

    abstract function declare(): Table;
}
