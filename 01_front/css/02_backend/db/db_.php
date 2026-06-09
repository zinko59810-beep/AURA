<?php
// DB接続ファイル


define('DB_USERNAME', 'aura');
define('DB_PASSWORD', 'aura');
define('DSN', 'mysql:host=localhost;dbname=student_db;charset=utf8mb4');

function db_connect(): PDO
{
    $dbh = new PDO(
        DSN,
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    return $dbh;
}

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}