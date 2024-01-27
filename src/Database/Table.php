<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Closure;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\SchemaException;

class Table extends \Doctrine\DBAL\Schema\Table
{
    protected ?Closure $after = null;

    /**
     * @throws SchemaException
     */
    public function addId(string $name = null): Column
    {
        $name = $name ?: 'id';

        $column = $this->addColumn($name, 'integer', ['unsigned' => true])->setAutoincrement(true);
        $this->setPrimaryKey([$name]);

        return $column;
    }

    /**
     * @throws SchemaException
     */
    public function addTimestamps(): void
    {
        $this->addColumn('created_at', 'datetime')->setNotnull(false);
        $this->addColumn('updated_at', 'datetime')->setNotnull(false);
    }

    public function after(Closure $callback): static
    {
        $this->after = $callback;

        return $this;
    }

    public function getAfter(): ?Closure
    {
        return $this->after;
    }
}
