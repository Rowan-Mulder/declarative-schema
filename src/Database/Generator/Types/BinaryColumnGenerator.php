<?php

namespace MichelJonkman\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;

class BinaryColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        return "binary('{$column->getName()}')";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return null;
    }
}
