<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user || $user['role'] !== 'admin') { header('Location: /index.php?page=dashboard'); exit; }
$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: /index.php?page=admin'); exit; }
$pdo = DB::conn();
$cur = $pdo->prepare('SELECT active FROM products WHERE id = :id');
$cur->execute([':id'=>$id]);
$active = (int)$cur->fetchColumn();
$upd = $pdo->prepare('UPDATE products SET active = :a WHERE id = :id');
$upd->execute([':a'=>($active?0:1), ':id'=>$id]);
header('Location: /index.php?page=admin');
?>