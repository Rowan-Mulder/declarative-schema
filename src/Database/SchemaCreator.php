<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Closure;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SchemaCreator
{

    protected array $postCreate = [];

    public function __construct(protected Filesystem $files, protected ?string $customStubPath = null)
    {
    }

    /**
     * @throws FileNotFoundException
     */
    public function create(string $name, string $path, string $table = null): string
    {
        $this->ensureSchemaFileDoesntAlreadyExist($name, $path);

        $stub = $this->getStub();

        $path = $this->getPath($name, $path);

        $this->files->ensureDirectoryExists(dirname($path));

        $this->files->put(
            $path, $this->populateStub($stub, $table)
        );

        $this->firePostCreateHooks($table, $path);

        return $path;
    }


    /**
     * @throws FileNotFoundException
     */
    protected function ensureSchemaFileDoesntAlreadyExist(string $name, string $schemaPath = null): void
    {
        if (!empty($schemaPath)) {
            $schemaFiles = $this->files->glob($schemaPath.'/*.php');

            foreach ($schemaFiles as $migrationFile) {
                $this->files->requireOnce($migrationFile);
            }
        }

        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    /**
     * @throws FileNotFoundException
     */
    protected function getStub(): string
    {
        $stub = $this->files->exists($customPath = $this->customStubPath.'/migration.stub')
            ? $customPath
            : $this->stubPath().'/schema.stub';

        return $this->files->get($stub);
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
        return Str::studly($name);
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
