<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

abstract class DeclarativeSchema
{
    abstract function declare(): Table;
}
