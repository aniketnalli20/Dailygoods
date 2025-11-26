<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user || $user['role'] !== 'admin') { header('Location: /index.php?page=dashboard'); exit; }
$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$milk_type = $_POST['milk_type'] ?? null;
$unit = trim($_POST['unit'] ?? '');
$default_unit_qty = (int)($_POST['default_unit_qty'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
if (!$id || !$name || !$type || !$unit || !$default_unit_qty || !$price) { header('Location: /index.php?page=admin'); exit; }
$pdo = DB::conn();
$stmt = $pdo->prepare('UPDATE products SET name=:name, type=:type, milk_type=:milk, unit=:unit, default_unit_qty=:qty, price=:price WHERE id=:id');
$stmt->execute([':id'=>$id, ':name'=>$name, ':type'=>$type, ':milk'=>$milk_type, ':unit'=>$unit, ':qty'=>$default_unit_qty, ':price'=>$price]);
header('Location: /index.php?page=admin');
?>