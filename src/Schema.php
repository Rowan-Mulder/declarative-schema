<?php

namespace MichelJonkman\DeclarativeSchema;

class Schema
{
    protected array $schemaPaths = [];

    public function loadSchemaFrom(string|array $paths): void
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        $this->schemaPaths = array_merge($this->schemaPaths, $paths);
    }

    public function getSchemaPaths(): array
    {
        return $this->schemaPaths;
    }

}
