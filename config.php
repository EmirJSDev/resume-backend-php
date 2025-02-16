<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Получаем переменные окружения безопасно
$emailUser = getenv('EMAIL_USER') ?: ($_SERVER['EMAIL_USER'] ?? null);
$emailPass = getenv('EMAIL_PASS') ?: ($_SERVER['EMAIL_PASS'] ?? null);

if (!$emailUser || !$emailPass) {
    die('Email credentials are missing! Please set EMAIL_USER and EMAIL_PASS in the .env file.');
}

// Включить вывод данных только в режиме отладки
$debug = getenv('DEBUG') ?: false;

if ($debug) {
    echo 'EMAIL_USER: ' . ($emailUser ?: 'NOT SET') . PHP_EOL;
    echo 'EMAIL_PASS: ' . ($emailPass ? '********' : 'NOT SET') . PHP_EOL;
}
