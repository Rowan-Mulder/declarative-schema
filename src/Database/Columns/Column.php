<?php

namespace MichelJonkman\DeclarativeSchema\Database\Columns;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use MichelJonkman\DeclarativeSchema\Database\Table;

class Column extends \Doctrine\DBAL\Schema\Column
{
    public function __construct(protected string $name, Type $type, protected Table $table, array $options = [])
    {
        parent::__construct($name, $type, $options);
    }

    public function nullable(bool $nullable = true): Column
    {
        return parent::setNotnull(!$nullable);
    }

    /**
     * @deprecated Use nullable()
     */
    public function setNotnull($notnull): Column
    {
        return parent::setNotnull($notnull);
    }

    /**
     * @throws SchemaException
     */
    public function primary(string $indexName = null): static
    {
        $this->table->setPrimaryKey([$this->name], $indexName);

        return $this;
    }

    /**
     * @throws SchemaException
     */
    public function index(string $indexName = null): static
    {
        $this->table->addIndex([$this->name], $indexName);

        return $this;
    }

    /**
     * @throws SchemaException
     */
    public function unique(string $indexName = null): static
    {
        $this->table->addUniqueIndex([$this->name], $indexName);

        return $this;
    }

    public function comment(string $comment): Column
    {
        return $this->setComment($comment);
    }

    public function default(mixed $value): Column
    {
        return $this->setDefault($value);
    }

    public function unsigned(bool $unsigned = true): Column
    {
        return $this->setUnsigned($unsigned);
    }
}
