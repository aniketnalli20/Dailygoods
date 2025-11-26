<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user) { header('Location: /index.php?page=login'); exit; }
$date = isset($_POST['date']) ? $_POST['date'] : null;
$sid = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$packaging_option_id = isset($_POST['packaging_option_id']) ? (int)$_POST['packaging_option_id'] : 0;
$quantity = isset($_POST['quantity']) ? (float)$_POST['quantity'] : 0;
if (!$date || !$product_id || !$packaging_option_id || $quantity <= 0) { header('Location: /index.php?page=dashboard'); exit; }
$pdo = DB::conn();
$own = $pdo->prepare('SELECT id FROM subscriptions WHERE id = :id AND user_id = :uid');
$own->execute([':id'=>$sid, ':uid'=>$user['id']]);
if ($own->fetchColumn()) {
    $stmt = $pdo->prepare('INSERT INTO delivery_extras(subscription_id, delivery_date, product_id, packaging_option_id, quantity) VALUES(:sid, :d, :pid, :pack, :qty) ON CONFLICT (subscription_id, delivery_date, product_id, packaging_option_id) DO UPDATE SET quantity = EXCLUDED.quantity');
    $stmt->execute([':sid'=>$sid, ':d'=>$date, ':pid'=>$product_id, ':pack'=>$packaging_option_id, ':qty'=>$quantity]);
}
header('Location: /index.php?page=dashboard&sid=' . $sid . '&month=' . substr($date,0,7));
?>