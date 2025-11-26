<?php
require_once __DIR__ . '/lib/DB.php';
require_once __DIR__ . '/config/config.php';
$schema = file_get_contents(__DIR__ . '/db/schema.sql');
$seed = file_get_contents(__DIR__ . '/db/seed.sql');

// Server-level connection (not bound to a specific DB)
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

// Ensure target DB exists, then connect via DB::conn()
try {
    $pdo = DB::conn();
} catch (PDOException $e) {
    $server->exec('CREATE DATABASE IF NOT EXISTS `' . $name . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $pdo = DB::conn();
}

$pdo->exec($schema);

// Migrate from legacy DB 'milkride' if present
try {
    $oldName = 'milkride';
    $check = $server->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '".$oldName."'");
    $exists = $check && $check->fetchColumn();
    if ($exists && $oldName !== $name) {
        $tables = ['users','addresses','packaging_options','products','subscriptions','subscription_items','delivery_dates','delivery_extras'];
        foreach ($tables as $t) {
            $server->exec('INSERT IGNORE INTO `'.$name.'`.`'.$t.'` SELECT * FROM `'.$oldName.'`.`'.$t.'`');
        }
    }
} catch (Throwable $migrEx) {
    // ignore migration errors to keep install idempotent
}

$pdo->exec($seed);
echo 'OK';
?>