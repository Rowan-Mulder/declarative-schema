<?php

namespace RowanMulder\DeclarativeSchema\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Type;
use RowanMulder\DeclarativeSchema\Exceptions\DeclarativeSchemaException;

class SchemaMigrator
{
    protected ?Connection $_connection = null;
    protected ?AbstractSchemaManager $_schemaManager = null;

    public function __construct(
        protected ConnectionManager $connectionManager,
        protected \RowanMulder\DeclarativeSchema\Schema $schema
    )
    {
    }

    protected function connection(): Connection
    {
        if (!$this->_connection) {
            $this->_connection = $this->connectionManager->getConnection();
        }

        return $this->_connection;
    }

    /**
     * @throws Exception
     */
    public function schemaManager(): AbstractSchemaManager
    {
        if (!$this->_schemaManager) {
            $this->_schemaManager = $this->connection()->createSchemaManager();
        }

        return $this->_schemaManager;
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
     * @throws Exception
     * @throws SchemaException
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
     * @throws SchemaException|Exception
     */
    public function getDiff(array $oldDeclarations, array $declarations): SchemaDiff
    {
        $oldSchema = new Schema($oldDeclarations);
        $newSchema = new Schema($declarations);

        $comparator = $this->schemaManager()->createComparator();
        return $comparator->compareSchemas($oldSchema, $newSchema);
    }

    public function getSqlLines(SchemaDiff $diff): array
    {
        $platform = $this->connection()->getDatabasePlatform();
        return $platform->getAlterSchemaSQL($diff);
    }

    /**
     * @throws Exception
     */
    public function run(SchemaDiff $diff): void
    {
        $this->connection()->executeStatement('SET FOREIGN_KEY_CHECKS = 0;');
        $this->connection()->executeStatement(implode(';', $this->getSqlLines($diff)));
        $this->connection()->executeStatement('SET FOREIGN_KEY_CHECKS = 1;');
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    protected function prepareSchemaTable(): Table
    {
        $table = new Table($this->getSchemaTableName());
        $table->id();

        $table->string('table')->unique();

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

            if ($this->schemaManager()->tablesExist([$tableName])) {
                $oldTables[] = $this->schemaManager()->introspectTable($newTable->getName());
            }
        }

        if ($this->schemaTableExists()) {
            $result = $this->connection()->executeQuery("
                SELECT id, `table` FROM `{$this->getSchemaTableName()}`
            ");

            $schemaTables = $result->fetchAllKeyValue();

            foreach ($schemaTables as $schemaTable) {
                if (in_array($schemaTable, $newTableNames)) {
                    continue;
                }

                $oldTables[] = $this->schemaManager()->introspectTable($schemaTable);
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
        $result = $this->connection()->executeQuery("
            SELECT id, `table` FROM `{$this->getSchemaTableName()}`
        ");

        $currentTables = $result->fetchAllKeyValue();
        $newTables = array_map(fn(Table $declaration) => $declaration->getName(), $declarations);

        foreach ($newTables as $newTable) {
            if (!in_array($newTable, $currentTables)) {
                $this->connection()->executeQuery("
                    INSERT INTO `{$this->getSchemaTableName()}` SET `table` = ? ON DUPLICATE KEY UPDATE `table` = ?
                ", [$newTable, $newTable]);
            }
        }

        foreach ($currentTables as $currentTable) {
            if (!in_array($currentTable, $newTables)) {
                $this->connection()->executeQuery("
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
        return $this->schemaManager()->tablesExist([$this->getSchemaTableName()]);
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
