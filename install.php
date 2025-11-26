<?php
require_once __DIR__ . '/lib/DB.php';
$schema = file_get_contents(__DIR__ . '/db/schema.sql');
$seed = file_get_contents(__DIR__ . '/db/seed.sql');
try {
    $pdo = DB::conn();
} catch (PDOException $e) {
    // Attempt to create database and reconnect
    require_once __DIR__ . '/config/config.php';
    $host = getenv('DB_HOST') ?: DB_HOST;
    $port = getenv('DB_PORT') ?: DB_PORT;
    $name = getenv('DB_NAME') ?: DB_NAME;
    $user = getenv('DB_USER') ?: DB_USER;
    $pass = getenv('DB_PASS') ?: DB_PASS;
    $serverDsn = 'mysql:host=' . $host . ';port=' . $port . ';charset=utf8mb4';
    $server = new PDO($serverDsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $server->exec('CREATE DATABASE IF NOT EXISTS `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo = DB::conn();
}
$pdo->exec($schema);
$pdo->exec($seed);
echo 'OK';
?>