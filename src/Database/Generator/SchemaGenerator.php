<?php

namespace RowanMulder\DeclarativeSchema\Database\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Generator;
use RowanMulder\DeclarativeSchema\Database\ConnectionManager;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\AbstractColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\BinaryColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\BooleanColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\DateColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\DateTimeColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\DateTimeTzColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\FloatColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\IntegerColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\JsonColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\StringColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\TextColumnGenerator;
use RowanMulder\DeclarativeSchema\Database\Generator\Types\TimeColumnGenerator;
use RowanMulder\DeclarativeSchema\Exceptions\Exception;
use RowanMulder\DeclarativeSchema\Schema;

class SchemaGenerator
{
    protected ?Connection $_connection = null;

    protected array $columnGeneratorMap = [
        'bigint' => IntegerColumnGenerator::class,
        'binary' => BinaryColumnGenerator::class,
        'boolean' => BooleanColumnGenerator::class,
        'date' => DateColumnGenerator::class,
        'datetime' => DateTimeColumnGenerator::class,
        'datetimetz' => DateTimeTzColumnGenerator::class,
        'decimal' => FloatColumnGenerator::class,
        'float' => FloatColumnGenerator::class,
        'integer' => IntegerColumnGenerator::class,
        'smallint' => IntegerColumnGenerator::class,
        'string' => StringColumnGenerator::class,
        'text' => TextColumnGenerator::class,
        'time' => TimeColumnGenerator::class,
        'json' => JsonColumnGenerator::class
    ];

    public function __construct(protected Schema $schema, protected ConnectionManager $connectionManager)
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
     * @throws \Doctrine\DBAL\Exception
     */
    public function generate(): Generator
    {
        $schemaManager = $this->connection()->createSchemaManager();

        $tables = $schemaManager->listTables();

        if (!file_exists($this->schema->basePath('database/schema/generated'))) {
            mkdir($this->schema->basePath('database/schema/generated'));
        }

        $stub = file_get_contents(__DIR__ . '/../../stubs/schema_generate.stub');

        foreach ($tables as $table) {
            if ($table->getName() === 'schema_tables') {
                continue;
            }

            $currentStub = str_replace('{{table}}', $table->getName(), $stub);

            $singleIndexes = [];
            $multiIndexes = [];

            foreach ($table->getIndexes() as $index) {
                if (count($index->getColumns()) > 1) {
                    $multiIndexes[] = $index;
                    continue;
                }

                $column = $index->getColumns()[0];
                $singleIndexes[$column] = $index;
            }

            $rows = [];

            foreach ($table->getColumns() as $column) {
                $typeName = Type::lookupName($column->getType());
                /** @var AbstractColumnGenerator $columnGenerator */
                $columnGenerator = new ($this->columnGeneratorMap[$typeName] ?: throw new Exception("Type \"$typeName\" is not supported by the generator"));

                $rows[] = $columnGenerator->generateDefinition($typeName, $column, $singleIndexes[$column->getName()] ?? null);
            }

            foreach ($multiIndexes as $multiIndex) {
                $indexType = $multiIndex->isUnique() ? 'addUniqueIndex' : 'addIndex';
                $indexColumns = implode(', ', array_map(fn(string $column) => "'$column'", $multiIndex->getColumns()));

                $rows[] = "$indexType($indexColumns, '{$multiIndex->getName()}');";
            }

            $currentStub = str_replace('{{content}}', implode("\n        ", $rows), $currentStub);

            $filename = "0000_00_00_000000_{$table->getName()}.php";

            file_put_contents($this->schema->basePath("database/schema/generated/$filename"), $currentStub);

            yield $filename;
        }
    }

}
