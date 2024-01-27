<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Info;
use Illuminate\Console\View\Components\TwoColumnDetail;
use Illuminate\Support\Collection;
use Throwable;
use Wyb\Lib\DB;
use MichelJonkman\DeclarativeSchema\Console\Traits\WritesToOutput;
use MichelJonkman\DeclarativeSchema\Exceptions\DeclarativeSchemaException;
use MichelJonkman\DeclarativeSchema\Models\SchemaTable;

class SchemaMigrator
{
    use WritesToOutput;

    protected Connection $connection;
    protected AbstractSchemaManager $schemaManager;
    protected OutputStyle|null $output = null;

    /**
     * @throws Exception
     */
    public function __construct(protected ConnectionManager $connectionManager, protected \MichelJonkman\DeclarativeSchema\Schema $schema)
    {
        $this->connection = $this->connectionManager->getConnection();
        $this->schemaManager = $this->connection->createSchemaManager();
    }

    protected function getSchemaFiles(): array
    {
        $files = [];

        foreach ($this->schema->getSchemaPaths() as $path) {
            $files = array_merge($files, glob("$path/*.php") ?: []);
        }

        return $files;
    }

    /**
     * @return \MichelJonkman\DeclarativeSchema\Database\Table[]|Collection
     * @throws DeclarativeSchemaException
     */
    public function getDeclarations(): array|Collection
    {
        $tables = collect();

        $this->write(Info::class, 'Gathering declarations.');

        foreach ($this->getSchemaFiles() as $file) {
            $declaration = include $file;

            if (!$declaration instanceof DeclarativeSchema) {
                throw new DeclarativeSchemaException("Declaration $file does not return a valid DeclarativeSchema class.");
            }

            $declaration->init($this->schemaManager);

            $tables[] = $declaration->declare();
        }

        return $tables;
    }


    /**
     * @param \MichelJonkman\DeclarativeSchema\Database\Table[]|Collection $newTables
     *
     * @throws Exception
     * @throws SchemaException
     * @throws Throwable
     */
    public function migrateSchema(array|Collection $newTables): void
    {
        $newTables = collect($newTables);

        $this->write(Info::class, 'Calculating schema diff.');

        $newTables->prepend($this->prepareSchemaTable());

        $oldTables = $this->getOldTables($newTables);

        if ($this->hasOutput()) {
            $this->output->writeln('');
        }

        $this->compareAndExecute($oldTables, $newTables->toArray());

        $this->write(Info::class, 'Saving current tablenames to database.');

        $this->saveTables($newTables);

        $callbacks = [];

        foreach ($newTables as $newTable) {
            $after = $newTable->getAfter();

            if ($after) {
                $callbacks[] = $after;
            }
        }

        if ($callbacks) {
            $this->write(Info::class, 'Running after callbacks.');

            foreach ($callbacks as $callback) {
                $callback();
            }
        }

        $this->write(Info::class, 'Done');
    }

    protected function hasOutput(): bool
    {
        return $this->output !== null;
    }

    public function setOutput(OutputStyle $output): void
    {
        $this->output = $output;
    }

    protected function getOutput(): OutputStyle
    {
        return $this->output;
    }

    /**
     * @param \MichelJonkman\DeclarativeSchema\Database\Table[] $oldTables
     * @param \MichelJonkman\DeclarativeSchema\Database\Table[] $newTables
     *
     * @return void
     * @throws Exception
     * @throws SchemaException
     */
    protected function compareAndExecute(array $oldTables, array $newTables): void
    {
        $oldSchema = new Schema($oldTables);
        $newSchema = new Schema($newTables);

        $comparator = $this->schemaManager->createComparator();
        $diff = $comparator->compareSchemas($oldSchema, $newSchema);

        if ($diff->isEmpty()) {
            $this->write(Info::class, 'Database already up-to-date.');

            return;
        }

        $platform = $this->connection->getDatabasePlatform();

        $sqlLines = $platform->getAlterSchemaSQL($diff);

        $this->write(Info::class, 'Running statements.');

        $pdo = DB::getPdo();
        $pdo->exec(implode(';', $sqlLines));
    }

    /**
     * @param \MichelJonkman\DeclarativeSchema\Database\Table[]|Collection $newTables
     *
     * @throws Exception
     */
    protected function getOldTables(array|Collection $newTables): array
    {
        $oldTables = [];
        $newTableNames = [];

        foreach ($newTables as $newTable) {
            $newTableNames[] = $tableName = $newTable->getName();

            if ($this->schemaManager->tablesExist($tableName)) {
                $this->write(TwoColumnDetail::class, $tableName, '<fg=blue;options=bold>EXISTS</>');

                $oldTables[] = $this->schemaManager->introspectTable($newTable->getName());
            } else {
                $this->write(TwoColumnDetail::class, $tableName, '<fg=green;options=bold>NEW</>');
            }
        }

        if ($this->schemaTableExists()) {
            $schemaTables = SchemaTable::pluck('table');

            foreach ($schemaTables as $schemaTable) {
                if (in_array($schemaTable, $newTableNames)) {
                    continue;
                }

                $oldTables[] = $this->schemaManager->introspectTable($schemaTable);
                $this->write(TwoColumnDetail::class, $schemaTable, '<fg=yellow;options=bold>OLD</>');
            }
        }

        return $oldTables;
    }

    /**
     * @throws Exception
     */
    protected function prepareSchemaTable(): Table
    {
        $table = new \MichelJonkman\DeclarativeSchema\Database\Table($this->getSchemaTableName());
        $table->addId();

        $table->addColumn('table', 'string');

        $table->addTimestamps();

        return $table;
    }

    /**
     * @throws Exception
     */
    protected function schemaTableExists(): bool
    {
        return $this->schemaManager->tablesExist([$this->getSchemaTableName()]);
    }

    public function getSchemaTableName(): string
    {
        return 'schema_tables';
    }

    /**
     * @param \MichelJonkman\DeclarativeSchema\Database\Table[]|Collection $newTables
     *
     * @return void
     */
    protected function saveTables(array|Collection $newTables): void
    {
        $current = SchemaTable::get()->keyBy('table');
        $new = collect();

        foreach ($newTables as $newTable) {
            $new[] = new SchemaTable([
                'table' => $newTable->getName()
            ]);
        }

        $removed = clone $current;
        $added = [];

        foreach ($new as $item) {
            if (!isset($current[$item->table])) {
                $added[] = $item;
                unset($removed[$item->table]);
            }
        }

        foreach ($added as $item) {
            $item->save();
        }

        foreach ($removed as $item) {
            $item->delete();
        }
    }

    public function getSchemaPath(): string
    {
        return base_path('database/schema');
    }
}
