<?php

declare(strict_types=1);

date_default_timezone_set('America/Lima');

if (!is_dir(__DIR__ . '/../storage/sessions')) {
    mkdir(__DIR__ . '/../storage/sessions', 0777, true);
}

session_save_path(__DIR__ . '/../storage/sessions');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

require __DIR__ . '/Helpers.php';

date_default_timezone_set(config('app.timezone', 'America/Lima'));

clearstatcache();
