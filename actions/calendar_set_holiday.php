<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user) { header('Location: /index.php?page=login'); exit; }
$date = isset($_POST['date']) ? $_POST['date'] : null;
$sid = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
if (!$date) { header('Location: /index.php?page=dashboard'); exit; }
$pdo = DB::conn();
$own = $pdo->prepare('SELECT id FROM subscriptions WHERE id = :id AND user_id = :uid');
$own->execute([':id'=>$sid, ':uid'=>$user['id']]);
if ($own->fetchColumn()) {
    $stmt = $pdo->prepare('INSERT INTO delivery_dates(subscription_id, delivery_date, status) VALUES(:sid, :d, :s) ON DUPLICATE KEY UPDATE status = VALUES(status)');
    $stmt->execute([':sid'=>$sid, ':d'=>$date, ':s'=>'holiday']);
}
header('Location: /index.php?page=dashboard&sid=' . $sid . '&month=' . substr($date,0,7));
?>