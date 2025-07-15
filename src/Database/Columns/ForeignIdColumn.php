<?php

namespace RowanMulder\DeclarativeSchema\Database\Columns;

use Doctrine\DBAL\Schema\SchemaException;

class ForeignIdColumn extends Column
{
    /**
     * @throws SchemaException
     */
    public function constrained(?string $table = null, string $column = 'id', ?string $indexName = null, array $options = []): void
    {
        if (!$table) {
            $table = rtrim($this->name, "_$column") . 's';
        }

        $this->table->addForeignKeyConstraint($table, [$this->name], [$column], $options, $indexName);
    }
}
