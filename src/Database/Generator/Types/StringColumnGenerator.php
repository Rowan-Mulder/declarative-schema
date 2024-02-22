<?php

namespace MichelJonkman\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;

class StringColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        return "string('{$column->getName()}')";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return 255;
    }
}
