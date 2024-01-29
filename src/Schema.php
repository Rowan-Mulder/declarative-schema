<?php

namespace MichelJonkman\DeclarativeSchema;

use MichelJonkman\DeclarativeSchema\Exceptions\Exception;

class Schema
{
    protected array $schemaPaths = [];
    protected array $config = [];
    protected ?string $basePath = null;

    public function loadSchemaFrom(string|array $paths): void
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        $this->schemaPaths = array_merge($this->schemaPaths, $paths);
    }

    /**
     * @throws Exception
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
        $this->basePath = realpath($this->config('base_path', '.'));

        if(!$this->basePath) {
            throw new Exception('Invalid "base_path" config');
        }
    }

    public function config(string $key, mixed $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . '/' . $path;
    }

    public function getSchemaPaths(): array
    {
        return $this->schemaPaths;
    }

}
