<?php

namespace MichelJonkman\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Schema\Column;

class TextColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        $method = $this->getMethod($column->getLength());

        return "$method('{$column->getName()}')";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return match ($this->getMethod($column->getLength())) {
            'tinyText' => AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT,
            'text' => AbstractMySQLPlatform::LENGTH_LIMIT_TEXT,
            'mediumText' => AbstractMySQLPlatform::LENGTH_LIMIT_MEDIUMTEXT,
            'longText' => 4294967295
        };
    }

    protected function getMethod(?int $length): string
    {
        if ($length) {
            if ($length <= AbstractMySQLPlatform::LENGTH_LIMIT_TINYTEXT) {
                return 'tinyText';
            }

            if ($length <= AbstractMySQLPlatform::LENGTH_LIMIT_TEXT) {
                return 'text';
            }

            if ($length <= AbstractMySQLPlatform::LENGTH_LIMIT_MEDIUMTEXT) {
                return 'mediumText';
            }
        }

        return 'longText';
    }
}
