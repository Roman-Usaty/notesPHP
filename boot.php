<?php
session_name('token');
session_start();

function pdo() : PDO
{
    static $pdo;

    if (!$pdo) {
        $config = include __DIR__.'/configs/config.php';

        $dsn = 'mysql:dbname='.$config['db_name'].';host='.$config['db_host'];

        $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    return $pdo;
}

function check_token() : bool
{
    return isset($_SESSION['token']);
}
