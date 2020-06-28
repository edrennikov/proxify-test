<?php
require_once 'vendor/autoload.php';

$config = require 'config/worker.php';

$pdo = new \PDO(
    $config['worker']['database']['dsn'],
    $config['worker']['database']['user'],
    $config['worker']['database']['password']
);
$urlRepository = new \App\Repositories\MySqlUrlRepository($pdo);

$urlChecker = new \App\Checkers\CurlUrlChecker($config['worker']['timeout']);

$logger = new \App\Loggers\ConsoleLogger();

$worker = new \App\Worker($urlRepository, $urlChecker, $logger);
$worker->run();