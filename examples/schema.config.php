<?php

return [
    // If the command should ask to confirm before running, defaults to true
    'production' => false,

    'base_path' => './',

    'load_from' => [
        'database/schema'
    ],

    'connection' => [
        'dbname' => '', // Preferably load these from an .env
        'user' => '', // Preferably load these from an .env
        'password' => '', // Preferably load these from an .env
        'host' => 'localhost', // Preferably load these from an .env
        'driver' => 'pdo_mysql',
    ]
];
