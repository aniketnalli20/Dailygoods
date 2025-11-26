<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user || $user['role'] !== 'admin') { header('Location: /index.php?page=dashboard'); exit; }
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$milk_type = $_POST['milk_type'] ?? null;
$unit = trim($_POST['unit'] ?? '');
$default_unit_qty = (int)($_POST['default_unit_qty'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
if (!$name || !$type || !$unit || !$default_unit_qty || !$price) { header('Location: /index.php?page=admin'); exit; }
$pdo = DB::conn();
$stmt = $pdo->prepare('INSERT INTO products(name,type,milk_type,unit,default_unit_qty,price,active) VALUES(:name,:type,:milk,:unit,:qty,:price,1)');
$stmt->execute([':name'=>$name, ':type'=>$type, ':milk'=>$milk_type, ':unit'=>$unit, ':qty'=>$default_unit_qty, ':price'=>$price]);
header('Location: /index.php?page=admin');
?>