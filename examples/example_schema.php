<?php

use Doctrine\DBAL\Types\Types;
use MichelJonkman\DeclarativeSchema\Database\DeclarativeSchema;
use MichelJonkman\DeclarativeSchema\Database\Table;

return new class extends DeclarativeSchema {
    function declare(): Table
    {
        $table = new Table('example');

        $table->id();

        // Laravel like notation
        $table->integer('integer_column')->index()->nullable();

        // Equivalent DBAL notation
        $table->addColumn('integer_column_manual', 'integer')->setNotnull(false);
        $table->addIndex(['integer_column_manual']);

        $table->timestamps();

        // This closure gets executed each time the migrate:schema command updates the database
        $table->after(function() {
            // Do something...
        });

        return $table;
    }
};
