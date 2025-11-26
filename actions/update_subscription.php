<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user) { header('Location: /index.php?page=login'); exit; }
$sid = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
$plan = isset($_POST['plan']) ? $_POST['plan'] : 'monthly';
$frequency = isset($_POST['frequency']) ? $_POST['frequency'] : 'daily';
$items = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];
if (!$sid) { header('Location: /index.php?page=dashboard'); exit; }
$pdo = DB::conn();
$pdo->beginTransaction();
try {
    $chk = $pdo->prepare('SELECT id FROM subscriptions WHERE id = :id AND user_id = :uid');
    $chk->execute([':id'=>$sid, ':uid'=>$user['id']]);
    if (!$chk->fetchColumn()) throw new Exception('not-found');
    $upd = $pdo->prepare('UPDATE subscriptions SET plan = :plan, frequency = :freq WHERE id = :id');
    $upd->execute([':plan'=>$plan, ':freq'=>$frequency, ':id'=>$sid]);
    $del = $pdo->prepare('DELETE FROM subscription_items WHERE subscription_id = :sid');
    $del->execute([':sid'=>$sid]);
    $priceStmt = $pdo->prepare('SELECT price FROM products WHERE id = :pid');
    $insItem = $pdo->prepare('INSERT INTO subscription_items(subscription_id,product_id,packaging_option_id,quantity,unit_price,total_price) VALUES(:sid,:pid,:pack,:qty,:unit,:total)');
    foreach ($items as $it) {
        $pid = isset($it['product_id']) ? (int)$it['product_id'] : 0;
        $pack = isset($it['packaging_option_id']) ? (int)$it['packaging_option_id'] : 0;
        $qty = isset($it['quantity']) ? (float)$it['quantity'] : 0;
        if ($pid && $pack && $qty > 0) {
            $priceStmt->execute([':pid'=>$pid]);
            $unit = (float)$priceStmt->fetchColumn();
            $total = $unit * $qty;
            $insItem->execute([':sid'=>$sid, ':pid'=>$pid, ':pack'=>$pack, ':qty'=>$qty, ':unit'=>$unit, ':total'=>$total]);
        }
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
}
header('Location: /index.php?page=dashboard');
?>