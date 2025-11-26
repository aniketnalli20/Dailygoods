<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user || $user['role'] !== 'admin') { header('Location: /index.php?page=dashboard'); exit; }
$name = trim($_POST['name'] ?? '');
if (!$name) { header('Location: /index.php?page=admin'); exit; }
$pdo = DB::conn();
$stmt = $pdo->prepare('INSERT INTO packaging_options(name) VALUES(:name) ON CONFLICT(name) DO NOTHING');
$stmt->execute([':name'=>$name]);
header('Location: /index.php?page=admin');
?>