<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user) { header('Location: /index.php?page=login'); exit; }
$date = isset($_POST['date']) ? $_POST['date'] : null;
if (!$date) { header('Location: /index.php?page=dashboard'); exit; }
$pdo = DB::conn();
$sidStmt = $pdo->prepare('SELECT id FROM subscriptions WHERE user_id = :uid ORDER BY id DESC LIMIT 1');
$sidStmt->execute([':uid'=>$user['id']]);
$sid = (int)$sidStmt->fetchColumn();
if ($sid) {
    $stmt = $pdo->prepare('INSERT INTO delivery_dates(subscription_id, delivery_date, status) VALUES(:sid, :d, :s) ON CONFLICT (subscription_id, delivery_date) DO UPDATE SET status = EXCLUDED.status');
    $stmt->execute([':sid'=>$sid, ':d'=>$date, ':s'=>'holiday']);
}
header('Location: /index.php?page=dashboard&month=' . substr($date,0,7));
?>