<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$_SERVER['APP_ENV'] ??= 'test';
$_ENV['APP_ENV'] ??= $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] ??= '1';
$_ENV['APP_DEBUG'] ??= $_SERVER['APP_DEBUG'];

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
