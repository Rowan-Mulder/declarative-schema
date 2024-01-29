<?php

namespace MichelJonkman\DeclarativeSchema\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

class SchemaStyle extends SymfonyStyle
{
    public function success(array|string $message): void
    {
        $this->block($message, 'Success', 'fg=green', ' ');
    }
}
