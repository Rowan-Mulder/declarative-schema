<?php

namespace RowanMulder\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;

class FloatColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        $method = $typeName;

        if ($column->getUnsigned()) {
            $method = 'unsigned' . ucfirst($method);
        }

        $total = '';

        if ($precision = $column->getPrecision()) {
            $total = ", $precision";
        }

        $places = '';

        if ($scale = $column->getScale()) {
            if (!$total) {
                $total = ', 8';
            }

            $places = ", $scale";
        }

        return "$method('{$column->getName()}'$total$places)";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return null;
    }
}
