<?php

namespace RowanMulder\DeclarativeSchema\Console;

use RowanMulder\DeclarativeSchema\Database\Generator\SchemaGenerator;
use RowanMulder\DeclarativeSchema\Exceptions\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSchemaCommand extends AbstractCommand
{
    public function __construct(protected SchemaGenerator $schemaGenerator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('schema:generate')->setDescription('Generate schema files from the database. WARNING: might not generate everything');
    }

    /**
     * @throws Exception|\Doctrine\DBAL\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->io->confirm('WARNING: This will not export constraints and things that the migrator does not support!', false)) {
            $this->io->info('Generation aborted');
            return static::INVALID;
        }

        foreach ($this->schemaGenerator->generate() as $filename) {
            $this->io->info($filename);
        }

        return static::SUCCESS;
    }
}
