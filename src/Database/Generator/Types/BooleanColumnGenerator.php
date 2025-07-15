<?php

namespace RowanMulder\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;

class BooleanColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        return "boolean('{$column->getName()}')";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return null;
    }
}
