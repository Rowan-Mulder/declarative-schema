<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Closure;
use MichelJonkman\DeclarativeSchema\Database\Traits\TableColumns\StoresColumns;

class Table extends \Doctrine\DBAL\Schema\Table
{
    use StoresColumns;

    protected ?Closure $after = null;

    /**
     * A callback to run after running the migrator, will run even there were no changes
     *
     * @param Closure $callback
     * @return $this
     */
    public function after(Closure $callback): static
    {
        $this->after = $callback;

        return $this;
    }

    public function getAfter(): ?Closure
    {
        return $this->after;
    }
}
