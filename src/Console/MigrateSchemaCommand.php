<?php

namespace RowanMulder\DeclarativeSchema\Console;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\SchemaException;
use RowanMulder\DeclarativeSchema\Database\SchemaMigrator;
use RowanMulder\DeclarativeSchema\Database\Table;
use RowanMulder\DeclarativeSchema\Schema;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use RowanMulder\DeclarativeSchema\Exceptions\DeclarativeSchemaException;

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
//            ->addOption('verbose', ['v'], InputOption::VALUE_NONE, 'Verbose output, shows the queries that will run before confirmation')
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
        $this->io->info('Gathering declarations');
        $declarations = $this->migrator->getDeclarations();

        $this->io->info('Calculating schema diff');

        $oldDeclarations = $this->migrator->getOldDeclarations($declarations);

        $diff = $this->migrator->getDiff($oldDeclarations, $declarations);

        if ($diff->isEmpty()) {
            $this->io->info('Database already up-to-date');
            return static::SUCCESS;
        }

        $this->displayDiffNew($oldDeclarations, $diff);

        $verbose = $input->getOption('verbose');
        if ($verbose) {
            foreach ($this->migrator->getSqlLines($diff) as $sqlLine) {
                $this->io->note($sqlLine);
            }
        }

        $force = $input->getOption('force');
        if (!$force && $this->schema->config('production', true) && !$this->io->confirm('Are you sure you want to run this command in production?', false)) {
            $this->io->info('Migration aborted');
            return static::INVALID;
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
     * @param Table[] $oldDeclarations
     * @param SchemaDiff $diff
     * @return void
     */
    protected function displayDiffNew(array $oldDeclarations, SchemaDiff $diff): void
    {
        $addedTableNames = [];
        $changedTableNames = [];
        $removedTableNames = [];

        foreach ($diff->getCreatedTables() as $newTable) {
            $addedTableNames[] = $newTable->getName();
        }

        foreach ($diff->getAlteredTables() as $changedTable) {
            $changedTableNames[] = $changedTable->getOldTable()->getName();
        }

        foreach ($diff->getDroppedTables() as $removedTable) {
            $removedTableNames[] = $removedTable->getName();
        }

        $rows = [];

        foreach ($oldDeclarations as $oldDeclaration) {
            $oldName = $oldDeclaration->getName();

            if (in_array($oldName, $changedTableNames)) {
                $rows[] = [$oldName, '<fg=blue;options=bold>CHANGED</>'];
            } elseif (in_array($oldName, $removedTableNames)) {
                $rows[] = [$oldName, '<fg=red;options=bold>REMOVED</>'];
            } else {
                $rows[] = [$oldName, '<options=bold>UNCHANGED</>'];
            }
        }

        foreach ($addedTableNames as $addedTableName) {
            $rows[] = [$addedTableName, '<fg=green;options=bold>ADDED</>'];
        }

        $this->io->table(
            [],
            $rows
        );
    }
}
