<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Closure;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;

class Table extends \Doctrine\DBAL\Schema\Table
{
    protected ?Closure $after = null;

    /**
     * @param string  $name
     * @param string  $typeName
     * @param array $options
     *
     * @return Column
     *
     * @throws SchemaException|Exception
     */
    public function addColumn($name, $typeName, array $options = []): Column
    {
        $column = new Column($name, Type::getType($typeName), $this, $options);

        $this->_addColumn($column);

        return $column;
    }

    /**
     * @throws SchemaException|Exception
     */
    public function id(string $name = null): Column
    {
        $name = $name ?: 'id';

        $column = $this->addColumn($name, 'integer', ['unsigned' => true])->setAutoincrement(true);
        $this->setPrimaryKey([$name]);

        return $column;
    }

    /**
     * @deprecated use id()
     * @throws SchemaException|Exception
     */
    public function addId(string $name = null): Column
    {
        return $this->id($name);
    }

    /**
     * @throws SchemaException|Exception
     */
    public function timestamps(): void
    {
        $this->addColumn('created_at', 'datetime')->setDefault('CURRENT_TIMESTAMP');
        $this->addColumn('updated_at', 'datetime')->setColumnDefinition('DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }


    /**
     * @deprecated Use timestamps()
     * @throws SchemaException|Exception
     */
    public function addTimestamps(): void
    {
        $this->timestamps();
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function string($column, $length = null): Column
    {
        return $this->addColumn($column, 'string', [
            'length' => $length
        ]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function integer($column, $autoIncrement = false, $unsigned = false): Column
    {
        return $this->addColumn($column, 'integer', [
            'autoincrement' => $autoIncrement,
            'unsigned' => $unsigned,
        ]);
    }

    /**
     * A callback to run after running the migrator, will run even there were no changes
     *
     * @param Closure $callback
     * @return $this
     */
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
