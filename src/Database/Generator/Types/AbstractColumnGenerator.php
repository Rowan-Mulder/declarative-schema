<?php

namespace MichelJonkman\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;

abstract class AbstractColumnGenerator
{
    abstract protected function getDefaultLength(Column $column): ?int;

    abstract public function getDefinition(string $typeName, Column $column): string;

    public function generateDefinition(string $typeName, Column $column, ?Index $index): string
    {
        $definition = '$table->' . $this->getDefinition($typeName, $column);

        if ($this->getDefaultLength($column) && $column->getLength() !== $this->getDefaultLength($column)) {
            $definition .= "->length({$column->getLength()})";
        }

        if (!$column->getNotnull()) {
            $definition .= '->nullable()';
        }

        if ($column->getDefault() !== null) {
            $default = var_export($column->getDefault(), true);
            $definition .= "->default($default)";
        }

        if ($index) {
            if ($index->isPrimary()) {
                $definition .= '->primary()';
            } elseif ($index->isUnique()) {
                $definition .= "->unique('{$index->getName()}')";
            } else {
                $definition .= "->index('{$index->getName()}')";
            }
        }

        if ($comment = $column->getComment()) {
            $comment = str_replace("'", "\\'", $comment);
            $definition .= "->comment('$comment')";
        }

        return "$definition;";
    }
}
