<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user) { header('Location: /index.php?page=login'); exit; }

$sid = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
$pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$pack = isset($_POST['packaging_option_id']) ? (int)$_POST['packaging_option_id'] : 0;
$qty = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 0;
if (!$sid || !$pid || !$pack || $qty <= 0) { header('Location: /index.php?page=dashboard'); exit; }

$pdo = DB::conn();
$own = $pdo->prepare('SELECT id FROM subscriptions WHERE id = :id AND user_id = :uid');
$own->execute([':id'=>$sid, ':uid'=>$user['id']]);
if (!$own->fetchColumn()) { header('Location: /index.php?page=dashboard'); exit; }

$priceStmt = $pdo->prepare('SELECT price FROM products WHERE id = :pid');
$priceStmt->execute([':pid'=>$pid]);
$unit = (float)$priceStmt->fetchColumn();
if ($unit <= 0) { header('Location: /index.php?page=dashboard&sid='.$sid); exit; }

$pdo->beginTransaction();
try {
    $find = $pdo->prepare('SELECT id, quantity FROM subscription_items WHERE subscription_id = :sid AND product_id = :pid AND packaging_option_id = :pack');
    $find->execute([':sid'=>$sid, ':pid'=>$pid, ':pack'=>$pack]);
    $row = $find->fetch();
    if ($row) {
        $newQty = (float)$row['quantity'] + $qty;
        $upd = $pdo->prepare('UPDATE subscription_items SET quantity = :q, unit_price = :u, total_price = :t WHERE id = :id');
        $upd->execute([':q'=>$newQty, ':u'=>$unit, ':t'=>($unit*$newQty), ':id'=>$row['id']]);
    } else {
        $ins = $pdo->prepare('INSERT INTO subscription_items(subscription_id,product_id,packaging_option_id,quantity,unit_price,total_price) VALUES(:sid,:pid,:pack,:qty,:unit,:total)');
        $ins->execute([':sid'=>$sid, ':pid'=>$pid, ':pack'=>$pack, ':qty'=>$qty, ':unit'=>$unit, ':total'=>($unit*$qty)]);
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
}
header('Location: /index.php?page=dashboard&sid='.$sid);
?>