<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user) { header('Location: /index.php?page=login'); exit; }
$plan = isset($_POST['plan']) ? $_POST['plan'] : 'monthly';
$frequency = isset($_POST['frequency']) ? $_POST['frequency'] : 'daily';
$line1 = isset($_POST['line1']) ? trim($_POST['line1']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
$items = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];
if (!$line1 || !$city || !$pincode) { header('Location: /index.php?page=dashboard'); exit; }
$pdo = DB::conn();
$pdo->beginTransaction();
try {
    $addrStmt = $pdo->prepare('INSERT INTO addresses(user_id,line1,city,pincode) VALUES(:uid,:line1,:city,:pincode) RETURNING id');
    $addrStmt->execute([':uid'=>$user['id'], ':line1'=>$line1, ':city'=>$city, ':pincode'=>$pincode]);
    $address_id = (int)$addrStmt->fetchColumn();
    $subStmt = $pdo->prepare('INSERT INTO subscriptions(user_id,address_id,plan,frequency,status,start_date) VALUES(:uid,:addr,:plan,:freq,:status,current_date) RETURNING id');
    $subStmt->execute([':uid'=>$user['id'], ':addr'=>$address_id, ':plan'=>$plan, ':freq'=>$frequency, ':status'=>'active']);
    $sid = (int)$subStmt->fetchColumn();
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