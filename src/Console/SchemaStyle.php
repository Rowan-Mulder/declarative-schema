<?php

namespace MichelJonkman\DeclarativeSchema\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

class SchemaStyle extends SymfonyStyle
{
    public function success(array|string $message): void
    {
        $this->block($message, 'Success', 'fg=green', ' ');
    }

    public function info(string|array $message): void
    {
        $this->block($message, 'Info', 'fg=bright-blue', ' ');
    }
}
