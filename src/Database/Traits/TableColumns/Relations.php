<?php

namespace MichelJonkman\DeclarativeSchema\Database\Traits\TableColumns;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use MichelJonkman\DeclarativeSchema\Database\Columns\Column;
use MichelJonkman\DeclarativeSchema\Database\Columns\ForeignIdColumn;

trait Relations
{
    /**
     * @throws SchemaException|Exception
     */
    public function id(string $name = null): Column
    {
        $name = $name ?: 'id';

        $column = $this->unsignedBigInteger($name, true);
        $this->setPrimaryKey([$name]);

        return $column;
    }

    /**
     * @throws SchemaException|Exception
     * @deprecated use id()
     */
    public function addId(string $name = null): Column
    {
        return $this->id($name);
    }

    /**
     * @throws Exception
     * @throws SchemaException
     */
    public function foreignId(string $column)
    {
        return $this->addColumn($column, 'bigint', [
            'unsigned' => true,
        ], ForeignIdColumn::class);
    }
}
