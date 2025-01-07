<?php
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_ENV['EMAIL_USER']) || !isset($_ENV['EMAIL_PASS'])) {
    die('Email credentials are missing! Please set EMAIL_USER and EMAIL_PASS in the .env file.');
}

echo 'EMAIL_USER: ' . $_ENV['EMAIL_USER'] . PHP_EOL;
echo 'EMAIL_PASS: ' . ($_ENV['EMAIL_PASS'] ? '********' : 'NOT SET') . PHP_EOL;
