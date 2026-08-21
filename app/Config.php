<?php

return [
    'app' => [
        'name' => 'SisPrestamos',
        'base_url' => '',
        'timezone' => 'America/Lima',
        'currency' => 'S/',
        'session_key' => 'sisprestamos_user',
        'debug' => true,
    ],
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'sisprestamos',
        'charset' => 'utf8mb4',
        'username' => 'root',
        'password' => '',
    ],
    'notifications' => [
        'mail' => [
            'host' => 'smtp.example.com',
            'port' => 587,
            'username' => 'no-reply@example.com',
            'password' => 'change-me',
            'encryption' => 'tls',
            'from_email' => 'no-reply@example.com',
            'from_name' => 'SisPrestamos',
        ],
        'sms' => [
            'provider' => 'log',
            'api_key' => '',
            'sender' => 'SisPrestamos',
        ],
    ],
];
