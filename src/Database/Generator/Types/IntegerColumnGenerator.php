<?php

namespace MichelJonkman\DeclarativeSchema\Database\Generator\Types;

use Doctrine\DBAL\Schema\Column;

class IntegerColumnGenerator extends AbstractColumnGenerator
{
    public function getDefinition(string $typeName, Column $column): string
    {
        $method = match ($typeName) {
            'integer' => 'integer',
            'bigint' => 'bigInteger',
            'smallint' => 'smallInteger',
        };

        if($column->getUnsigned()) {
            $method = 'unsigned' . ucfirst($method);
        }

        $autoincrement = '';

        if ($column->getAutoincrement()) {
            $autoincrement = ', true';
        }

        return "$method('{$column->getName()}'$autoincrement)";
    }

    protected function getDefaultLength(Column $column): ?int
    {
        return null;
    }
}
