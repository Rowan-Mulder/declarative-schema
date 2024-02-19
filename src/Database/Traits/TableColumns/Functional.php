<?php

namespace MichelJonkman\DeclarativeSchema\Database\Traits\TableColumns;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use MichelJonkman\DeclarativeSchema\Database\Columns\Column;

trait Functional
{
    /**
     * @throws SchemaException|Exception
     */
    public function timestamps(bool $nullable = true): void
    {
        $this->dateTime('created_at')->default('CURRENT_TIMESTAMP')->nullable($nullable);
        $this->dateTime('updated_at')->default('CURRENT_TIMESTAMP')->nullable($nullable);
    }

    /**
     * @throws SchemaException|Exception
     * @deprecated Use timestamps()
     */
    public function addTimestamps(): void
    {
        $this->timestamps();
    }

    /**
     * @throws SchemaException|Exception
     */
    public function timestampsTz(bool $nullable = true): void
    {
        $this->dateTimeTz('created_at')->default('CURRENT_TIMESTAMP')->nullable($nullable);
        $this->dateTimeTz('updated_at')->default('CURRENT_TIMESTAMP')->nullable($nullable);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function softDeletes($column = 'deleted_at', $precision = 0): Column
    {
        return $this->dateTime($column, $precision)->nullable();
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function softDeletesTz($column = 'deleted_at', $precision = 0): Column
    {
        return $this->dateTimeTz($column, $precision)->nullable();
    }
}
