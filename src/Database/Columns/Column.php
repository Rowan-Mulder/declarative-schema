<?php

namespace RowanMulder\DeclarativeSchema\Database\Columns;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use RowanMulder\DeclarativeSchema\Database\Table;

class Column extends \Doctrine\DBAL\Schema\Column
{
    public function __construct(protected string $name, Type $type, protected Table $table, array $options = [])
    {
        parent::__construct($name, $type, $options);
    }

    public function nullable(bool $nullable = true): static
    {
        return parent::setNotnull(!$nullable);
    }

    /**
     * @deprecated Use nullable()
     */
    public function setNotnull($notnull): static
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

    public function comment(string $comment): static
    {
        return $this->setComment($comment);
    }

    public function default(mixed $value): static
    {
        return $this->setDefault($value);
    }

    public function unsigned(bool $unsigned = true): static
    {
        return $this->setUnsigned($unsigned);
    }

    public function length(int $length): static
    {
        return $this->setLength($length);
    }

    /**
     * For example: string('created_at_year')->virtual('YEAR(created_at)')
     */
    public function virtualAs(string $expression): void
    {
        $columnDeclaration = $this->getType()->getSQLDeclaration($this->toArray(), new MySQLPlatform());

        $this->nullable()->setColumnDefinition("$columnDeclaration AS ($expression) VIRTUAL");
    }
}
