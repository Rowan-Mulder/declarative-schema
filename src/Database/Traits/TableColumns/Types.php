<?php

namespace RowanMulder\DeclarativeSchema\Database\Traits\TableColumns;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types as Type;
use RowanMulder\DeclarativeSchema\Database\Columns\Column;

trait Types
{
    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function string(string $column, int $length = 255): Column
    {
        return $this->addColumn($column, Type::STRING, [
            'length' => $length
        ]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function tinyText(string $column): Column
    {
        return $this->addColumn($column, 'text', [
            'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT
        ]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function text(string $column): Column
    {
        return $this->addColumn($column, 'text', [
            'length' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT
        ]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function mediumText(string $column): Column
    {
        return $this->addColumn($column, 'text', [
            'length' => AbstractMySQLPlatform::LENGTH_LIMIT_MEDIUMTEXT
        ]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function longText(string $column): Column
    {
        return $this->addColumn($column, 'text');
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function integer(string $column, bool $autoIncrement = false, bool $unsigned = false): Column
    {
        return $this->addColumn($column, Type::INTEGER, [
            'autoincrement' => $autoIncrement,
            'unsigned' => $unsigned,
        ]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function smallInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): Column
    {
        return $this->addColumn($column, 'smallint', [
            'autoincrement' => $autoIncrement,
            'unsigned' => $unsigned,
        ]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function bigInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): Column
    {
        return $this->addColumn($column, 'bigint', [
            'autoincrement' => $autoIncrement,
            'unsigned' => $unsigned,
        ]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function unsignedInteger(string $column, bool $autoIncrement = false): Column
    {
        return $this->integer($column, $autoIncrement, true);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function unsignedSmallInteger(string $column, bool $autoIncrement = false): Column
    {
        return $this->smallInteger($column, $autoIncrement, true);
    }

    /**
     * @throws Exception
     * @throws SchemaException
     */
    public function unsignedBigInteger(string $column, bool $autoIncrement = false): Column
    {
        return $this->bigInteger($column, $autoIncrement, true);
    }

    /**
     * @throws Exception
     * @throws SchemaException
     */
    public function float(string $column, int $total = 8, int $places = 2, bool $unsigned = false): Column
    {
        return $this->addColumn($column, 'float', [
            'precision' => $total,
            'scale' => $places,
            'unsigned' => $unsigned
        ]);
    }

    /**
     * @throws Exception
     * @throws SchemaException
     */
    public function double(string $column, int $total = 8, int $places = 2, bool $unsigned = false): Column
    {
        return $this->addColumn($column, 'float', [
            'precision' => $total,
            'scale' => $places,
            'unsigned' => $unsigned
        ]);
    }

    /**
     * @throws Exception
     * @throws SchemaException
     */
    public function decimal(string $column, int $total = 8, int $places = 2, bool $unsigned = false): Column
    {
        return $this->addColumn($column, 'decimal', [
            'precision' => $total,
            'scale' => $places,
            'unsigned' => $unsigned
        ]);
    }

    /**
     * @throws Exception
     * @throws SchemaException
     */
    public function unsignedFloat(string $column, int $total = 8, int $places = 2): Column
    {
        return $this->float($column, $total, $places, true);
    }

    /**
     * @throws Exception
     * @throws SchemaException
     */
    public function unsignedDouble(string $column, int $total = 8, int $places = 2): Column
    {
        return $this->double($column, $total, $places, true);
    }

    /**
     * @throws Exception
     * @throws SchemaException
     */
    public function unsignedDecimal(string $column, int $total = 8, int $places = 2): Column
    {
        return $this->decimal($column, $total, $places, true);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function boolean(string $column): Column
    {
        return $this->addColumn($column, Type::BOOLEAN);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function json(string $column): Column
    {
        return $this->addColumn($column, Type::JSON);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function date(string $column): Column
    {
        return $this->addColumn($column, Type::DATE_MUTABLE);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function dateTime(string $column, int $precision = 0): Column
    {
        return $this->addColumn($column, Type::DATETIME_MUTABLE, ['precision' => $precision]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function dateTimeTz(string $column, int $precision = 0): Column
    {
        return $this->addColumn($column, Type::DATETIMETZ_MUTABLE, ['precision' => $precision]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function time(string $column, int $precision = 0): Column
    {
        return $this->addColumn($column, Type::TIME_MUTABLE, ['precision' => $precision]);
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    public function binary(string $column, int $length = 255): Column
    {
        return $this->addColumn($column, Type::BINARY, [
            'length' => $length
        ]);
    }
}
