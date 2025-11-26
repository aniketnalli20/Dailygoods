<?php
require_once __DIR__ . '/lib/DB.php';
$pdo = DB::conn();
$schema = file_get_contents(__DIR__ . '/db/schema.sql');
$seed = file_get_contents(__DIR__ . '/db/seed.sql');
$pdo->exec($schema);
$pdo->exec($seed);
echo 'OK';
?>