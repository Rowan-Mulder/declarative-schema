<?php

namespace RowanMulder\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;

class DateColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        return "date('{$column->getName()}')";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return null;
    }
}
