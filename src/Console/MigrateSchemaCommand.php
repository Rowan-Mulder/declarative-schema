<?php

namespace MichelJonkman\DeclarativeSchema\Console;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaException;
use MichelJonkman\DeclarativeSchema\Database\SchemaMigrator;
use MichelJonkman\DeclarativeSchema\Database\Table;
use MichelJonkman\DeclarativeSchema\Schema;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use MichelJonkman\DeclarativeSchema\Exceptions\DeclarativeSchemaException;

class MigrateSchemaCommand extends AbstractCommand
{

    public function __construct(protected SchemaMigrator $migrator, protected Schema $schema)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('migrate:schema')
            ->setAliases(['migrate'])
            ->setDescription('Migrate the declarative schema\'s')
            ->addOption('force', ['f', 'y'], InputOption::VALUE_NONE, 'Force the operation to run when in production');
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
        if (!$force && $this->schema->config('production', true) && !$this->io->confirm('Are you sure you want to run this command in production?', false)) {
            $this->io->info('Migration aborted');
            return static::INVALID;
        }

        $this->io->info('Gathering declarations');
        $declarations = $this->migrator->getDeclarations();

        $this->io->info('Calculating schema diff');

        $oldDeclarations = $this->migrator->getOldDeclarations($declarations);

        $this->displayDiff($declarations, $oldDeclarations);

        $diff = $this->migrator->getDiff($oldDeclarations, $declarations);

        if ($diff->isEmpty()) {
            $this->io->info('Database already up-to-date');
            return static::SUCCESS;
        }

        $this->io->info('Running statements');

        $this->migrator->run($diff);

        $this->io->info('Saving current tablenames to database');

        $this->migrator->saveTables($declarations);

        $callbacks = $this->migrator->getAfterCallbacks($declarations);

        if ($callbacks) {
            $this->io->info('Running after callbacks');

            foreach ($callbacks as $callback) {
                $callback();
            }
        }

        $this->io->success('Done');

        return static::SUCCESS;
    }

    /**
     * @param Table[] $declarations
     * @param Table[] $oldDeclarations
     * @return void
     */
    protected function displayDiff(array $declarations, array $oldDeclarations): void
    {
        $oldTableNames = [];
        $newTableNames = [];

        $rows = [];

        foreach ($oldDeclarations as $oldDeclaration) {
            $oldTableNames[] = $oldDeclaration->getName();
        }

        foreach ($declarations as $declaration) {
            $newTableNames[] = $declaration->getName();
        }

        foreach ($newTableNames as $newTableName) {
            if(!in_array($newTableName, $oldTableNames)) {
                $rows[] = [$newTableName, '<fg=green;options=bold>NEW</>'];
            }
            else {
                $rows[] = [$newTableName, '<fg=blue;options=bold>EXISTS</>'];
            }
        }

        foreach ($oldTableNames as $oldTableName) {
            if(!in_array($oldTableName, $newTableNames)) {
                $rows[] = [$oldTableName, '<fg=yellow;options=bold>OLD</>'];
            }
        }

        $this->io->table(
            [],
            $rows
        );
    }
}
