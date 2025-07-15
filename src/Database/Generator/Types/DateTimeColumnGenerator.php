<?php

namespace RowanMulder\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;

class DateTimeColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        $precision = '';

        if ($column->getPrecision()) {
            $precision = ", {$column->getPrecision()}";
        }

        return "dateTime('{$column->getName()}'$precision)";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return null;
    }
}
