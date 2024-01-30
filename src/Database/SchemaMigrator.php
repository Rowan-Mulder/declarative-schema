<?php

namespace MichelJonkman\DeclarativeSchema\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\SchemaException;
use MichelJonkman\DeclarativeSchema\Exceptions\DeclarativeSchemaException;

class SchemaMigrator
{
    protected Connection $connection;
    protected AbstractSchemaManager $schemaManager;

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
     * @return Table[]
     * @throws DeclarativeSchemaException
     */
    public function getDeclarations(): array
    {
        $tables = [];

        foreach ($this->getSchemaFiles() as $file) {
            $declaration = include $file;

            if (!$declaration instanceof DeclarativeSchema) {
                throw new DeclarativeSchemaException("Declaration $file does not return a valid DeclarativeSchema class.");
            }

            $tables[] = $declaration->declare();
        }

        array_unshift($tables, $this->prepareSchemaTable());

        return $tables;
    }

    /**
     * @param Table[] $oldDeclarations
     * @param Table[] $declarations
     * @return SchemaDiff
     * @throws SchemaException
     */
    public function getDiff(array $oldDeclarations, array $declarations): SchemaDiff
    {
        $oldSchema = new Schema($oldDeclarations);
        $newSchema = new Schema($declarations);

        $comparator = $this->schemaManager->createComparator();
        return $comparator->compareSchemas($oldSchema, $newSchema);
    }

    /**
     * @throws Exception
     */
    public function run(SchemaDiff $diff): void
    {
        $platform = $this->connection->getDatabasePlatform();

        $sqlLines = $platform->getAlterSchemaSQL($diff);

        $this->connection->executeStatement(implode(';', $sqlLines));
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    protected function prepareSchemaTable(): Table
    {
        $table = new Table($this->getSchemaTableName());
        $table->id();

        $table->addColumn('table', 'string');
        $table->addUniqueIndex(['table']);

        $table->timestamps();

        return $table;
    }

    /**
     * @return Table[]
     * @throws Exception
     */
    public function getOldDeclarations(array $declarations): array
    {
        $oldTables = [];
        $newTableNames = [];

        foreach ($declarations as $newTable) {
            $newTableNames[] = $tableName = $newTable->getName();

            if ($this->schemaManager->tablesExist($tableName)) {
                $oldTables[] = $this->schemaManager->introspectTable($newTable->getName());
            }
        }

        if ($this->schemaTableExists()) {
            $result = $this->connection->executeQuery("
                SELECT id, `table` FROM `{$this->getSchemaTableName()}`
            ");

            $schemaTables = $result->fetchAllKeyValue();

            foreach ($schemaTables as $schemaTable) {
                if (in_array($schemaTable, $newTableNames)) {
                    continue;
                }

                $oldTables[] = $this->schemaManager->introspectTable($schemaTable);
            }
        }

        return $oldTables;
    }

    /**
     * @param Table[] $declarations
     * @throws Exception
     */
    public function saveTables(array $declarations): void
    {
        $result = $this->connection->executeQuery("
            SELECT id, `table` FROM `{$this->getSchemaTableName()}`
        ");

        $currentTables = $result->fetchAllKeyValue();
        $newTables = array_map(fn(Table $declaration) => $declaration->getName(), $declarations);

        foreach ($newTables as $newTable) {
            if (!in_array($newTable, $currentTables)) {
                $this->connection->executeQuery("
                    INSERT INTO `{$this->getSchemaTableName()}` SET `table` = ? ON DUPLICATE KEY UPDATE `table` = ?
                ", [$newTable, $newTable]);
            }
        }

        foreach ($currentTables as $currentTable) {
            if (!in_array($currentTable, $newTables)) {
                $this->connection->executeQuery("
                    DELETE FROM `{$this->getSchemaTableName()}` WHERE `table` = ?
                ", [$currentTable]);
            }
        }
    }

    /**
     * @param Table[] $declarations
     */
    public function getAfterCallbacks(array $declarations): array
    {
        $callbacks = [];

        foreach ($declarations as $declaration) {
            $after = $declaration->getAfter();

            if ($after) {
                $callbacks[] = $after;
            }
        }

        return $callbacks;
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

    public function getSchemaPath(): string
    {
        return $this->schema->basePath('database/schema');
    }
}
