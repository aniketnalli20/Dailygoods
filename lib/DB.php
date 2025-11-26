<?php
require_once __DIR__ . '/../config/config.php';

class DB {
    public static function conn(): PDO {
        static $pdo = null;
        if ($pdo) return $pdo;
        $host = getenv('DB_HOST') ?: DB_HOST;
        $port = getenv('DB_PORT') ?: DB_PORT;
        $name = getenv('DB_NAME') ?: DB_NAME;
        $user = getenv('DB_USER') ?: DB_USER;
        $pass = getenv('DB_PASS') ?: DB_PASS;
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }
}
?>