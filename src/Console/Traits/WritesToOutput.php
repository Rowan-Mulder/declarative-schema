<?php

namespace MichelJonkman\DeclarativeSchema\Console\Traits;

use Illuminate\Console\OutputStyle;

trait WritesToOutput
{
    protected abstract function getOutput(): OutputStyle;

    protected function write(string $component, ...$arguments): void
    {
        if ($this->getOutput() && class_exists($component)) {
            (new $component($this->getOutput()))->render(...$arguments);
        }
    }
}
