<?php

namespace MichelJonkman\DeclarativeSchema\Models;

use Illuminate\Database\Eloquent\Model;
use Wyb\App;
use MichelJonkman\DeclarativeSchema\Database\SchemaMigrator;

class SchemaTable extends Model
{

    protected $fillable = [
        'table'
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = App::make(SchemaMigrator::class)->getSchemaTableName();

        parent::__construct($attributes);
    }
}
