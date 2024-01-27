<?php

namespace MichelJonkman\DeclarativeSchema\Console;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use Illuminate\Console\OutputStyle;
use Illuminate\Console\View\Components\Info;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Wyb\App;
use MichelJonkman\DeclarativeSchema\Console\Traits\WritesToOutput;
use MichelJonkman\DeclarativeSchema\Database\SchemaMigrator;
use MichelJonkman\DeclarativeSchema\Exceptions\DeclarativeSchemaException;

class MigrateSchemaCommand extends Command
{
    use WritesToOutput;

    protected ?OutputStyle $output = null;

    protected function configure(): void
    {
        $this->setName('migrate:schema')
            ->setAliases(['migrate'])
            ->setDescription('Migrate the declarative schema\'s')
            ->addOption('force', ['f', 'y'], InputOption::VALUE_NONE, 'Force the operation to run when in production');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->output = new OutputStyle($input, $output);
    }

    /**
     * @throws Throwable
     * @throws SchemaException
     * @throws DeclarativeSchemaException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');
        if (!$force && !is_dev() && !$this->output->confirm('Are you sure you want to run this command in production?', false)) {
            $this->write(Info::class, 'Migration aborted');
            return Command::INVALID;
        }

        $migrator = App::make(SchemaMigrator::class);
        $migrator->setOutput($this->getOutput());

        $migrator->migrateSchema($migrator->getDeclarations());

        return Command::SUCCESS;
    }

    protected function getOutput(): OutputStyle
    {
        return $this->output;
    }
}
