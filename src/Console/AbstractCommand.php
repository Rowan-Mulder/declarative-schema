<?php

namespace RowanMulder\DeclarativeSchema\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AbstractCommand extends Command
{
    protected ?SymfonyStyle $io = null;

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SchemaStyle($input, $output);
    }
}
