<?php

namespace MichelJonkman\DeclarativeSchema\Console;

use Exception;
use Jawira\CaseConverter\Convert;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MichelJonkman\DeclarativeSchema\Database\SchemaCreator;
use MichelJonkman\DeclarativeSchema\Database\SchemaMigrator;

class MakeSchemaCommand extends AbstractCommand
{
    public function __construct(protected SchemaCreator $creator, protected SchemaMigrator $migrator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('make:schema')
            ->setDescription('Create a new migration file')
            ->addArgument('table', InputArgument::OPTIONAL, 'The table name');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = $input->getArgument('table');

        if (!$table) {
            do {
                $table = $this->io->ask('What should the schema file be named? ');
            } while (!$table);
        }

        $convert = new Convert(trim($table));
        $name = $convert->toSnake();

        $this->writeSchema($name, $table);

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    protected function writeSchema(string $name, string $table): void
    {
        $file = $this->creator->create(
            $name, $this->getSchemaPath(), $table
        );

        $this->io->success("Schema file [$file] created");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getSchemaPath(): string
    {
        return $this->migrator->getSchemaPath();
    }
}
