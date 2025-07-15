<?php

use RowanMulder\DeclarativeSchema\Database\DeclarativeSchema;
use RowanMulder\DeclarativeSchema\Database\Table;

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

        $table->foreignId('test_id');

        // Constrained is not fluid and instead uses an options array
        $table->foreignId('item_id')->constrained(options: [
            'onDelete' => 'CASCADE'
        ]);

        $table->string('string');
        $table->tinyText('tinyText');
        $table->text('text');
        $table->mediumText('mediumText');
        $table->longText('longText');
        $table->integer('integer');
        $table->smallInteger('smallInteger');
        $table->bigInteger('bigInteger');
        $table->unsignedInteger('unsignedInteger');
        $table->unsignedSmallInteger('unsignedSmallInteger');
        $table->unsignedBigInteger('unsignedBigInteger');
        $table->float('float');
        $table->double('double');
        $table->decimal('decimal');
        $table->unsignedFloat('unsignedFloat');
        $table->unsignedDouble('unsignedDouble');
        $table->unsignedDecimal('unsignedDecimal');
        $table->boolean('boolean');
        $table->json('json');
        $table->date('date');
        $table->dateTime('dateTime');
        $table->dateTimeTz('dateTimeTz');
        $table->time('time');
        $table->binary('binary');

        $table->softDeletes();

        $table->timestamps();

        // This closure gets executed each time the migrate:schema command updates the database
        $table->after(function() {
            // Do something...
        });

        return $table;
    }
};
