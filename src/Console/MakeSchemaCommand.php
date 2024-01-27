<?php

namespace MichelJonkman\DeclarativeSchema\Console;

use Exception;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Info;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MichelJonkman\DeclarativeSchema\Console\Traits\WritesToOutput;
use MichelJonkman\DeclarativeSchema\Database\SchemaCreator;
use MichelJonkman\DeclarativeSchema\Database\SchemaMigrator;

class MakeSchemaCommand extends Command implements PromptsForMissingInput
{
    use WritesToOutput;

    protected ?OutputStyle $output = null;

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

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->output = new OutputStyle($input, $output);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = $input->getArgument('table');
        if (!$table) {
            $table = $this->output->ask('What should the schema file be named?');
        }

        $name = Str::snake(trim($table));

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

        $this->write(Info::class, "Schema file [$file] created successfully.");
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

    protected function getOutput(): OutputStyle
    {
        return $this->output;
    }
}
