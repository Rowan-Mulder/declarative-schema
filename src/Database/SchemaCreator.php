<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Closure;
use InvalidArgumentException;
use Jawira\CaseConverter\Convert;

class SchemaCreator
{

    protected array $postCreate = [];

    public function __construct(protected ?string $customStubPath = null)
    {
    }

    public function create(string $name, string $path, string $table = null): string
    {
        $this->ensureSchemaFileDoesntAlreadyExist($name, $path);

        $stub = $this->getStub();

        $path = $this->getPath($name, $path);

        if(!is_dir(dirname($path))) {
            mkdir(dirname($path), recursive: true);
        }

        file_put_contents($path, $this->populateStub($stub, $table));

        $this->firePostCreateHooks($table, $path);

        return $path;
    }

    protected function ensureSchemaFileDoesntAlreadyExist(string $name, string $schemaPath = null): void
    {
        if (!empty($schemaPath)) {
            $schemaFiles = glob($schemaPath.'/*.php');

            foreach ($schemaFiles as $migrationFile) {
                require_once $migrationFile;
            }
        }

        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    protected function getStub(): string
    {
        $stub = file_exists($customPath = $this->customStubPath.'/migration.stub')
            ? $customPath
            : $this->stubPath().'/schema.stub';

        return file_get_contents($stub);
    }

    /**
     * Populate the place-holders in the migration stub.
     */
    protected function populateStub(string $stub, ?string $table): string
    {
        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
        if (!is_null($table)) {
            $stub = str_replace(
                ['DummyTable', '{{ table }}', '{{table}}'],
                $table, $stub
            );
        }

        return $stub;
    }

    protected function getClassName(string $name): string
    {
        return (new Convert($name))->toPascal();
    }

    protected function getPath(string $name, string $path): string
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }

    protected function firePostCreateHooks(?string $table, string $path): void
    {
        foreach ($this->postCreate as $callback) {
            $callback($table, $path);
        }
    }

    public function afterCreate(Closure $callback): void
    {
        $this->postCreate[] = $callback;
    }

    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }

    public function stubPath(): string
    {
        return __DIR__.'/../stubs';
    }
}
