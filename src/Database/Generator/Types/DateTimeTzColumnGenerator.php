<?php

namespace MichelJonkman\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;

class DateTimeTzColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        $precision = '';

        if ($column->getPrecision()) {
            $precision = ", {$column->getPrecision()}";
        }

        return "dateTimeTz('{$column->getName()}'$precision)";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return null;
    }
}
