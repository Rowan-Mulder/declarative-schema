<?php

namespace RowanMulder\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;

class TimeColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        $precision = '';

        if ($column->getPrecision()) {
            $precision = ", {$column->getPrecision()}";
        }

        return "time('{$column->getName()}'$precision)";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return null;
    }
}
