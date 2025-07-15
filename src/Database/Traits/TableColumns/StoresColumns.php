<?php

namespace RowanMulder\DeclarativeSchema\Database\Traits\TableColumns;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use RowanMulder\DeclarativeSchema\Database\Columns\Column;

trait StoresColumns
{
    use Types, Functional, Relations;

    /**
     * @template T
     *
     * @param string $name
     * @param string $typeName
     * @param array $options
     * @param class-string<T>|null $columnClass
     * @return T|Column
     *
     * @throws Exception
     * @throws SchemaException
     */
    public function addColumn($name, $typeName, array $options = [], ?string $columnClass = null): Column
    {
        if (!$columnClass) {
            $columnClass = Column::class;
        }

        $column = new $columnClass($name, Type::getType($typeName), $this, $options);

        $this->_addColumn($column);

        return $column;
    }
}
