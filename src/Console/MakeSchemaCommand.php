<?php

namespace MichelJonkman\DeclarativeSchema\Console;

use Exception;
use Jawira\CaseConverter\Convert;
use MichelJonkman\DeclarativeSchema\Database\SchemaMigratorFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MichelJonkman\DeclarativeSchema\Database\SchemaCreator;
use MichelJonkman\DeclarativeSchema\Database\SchemaMigrator;
use Symfony\Component\Console\Question\Question;

class MakeSchemaCommand extends Command
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
            $helper = $this->getHelper('question');
            $question = new Question('What should the schema file be named? ');

            do {
                $table = $helper->ask($input, $output, $question);
            } while (!$table);
        }

        $convert = new Convert(trim($table));
        $name = $convert->toSnake();

        $this->writeSchema($output, $name, $table);

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    protected function writeSchema(OutputInterface $output, string $name, string $table): void
    {
        $file = $this->creator->create(
            $name, $this->getSchemaPath(), $table
        );

        $output->writeLn("Schema file [$file] created successfully.");
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
