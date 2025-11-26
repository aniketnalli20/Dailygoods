<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user) { header('Location: /index.php?page=login'); exit; }
$action = isset($_POST['action']) ? $_POST['action'] : 'pause';
$paused_until = isset($_POST['paused_until']) ? $_POST['paused_until'] : null;
$sid = isset($_POST['subscription_id']) ? (int)$_POST['subscription_id'] : 0;
$pdo = DB::conn();
$own = $pdo->prepare('SELECT id FROM subscriptions WHERE id = :id AND user_id = :uid');
$own->execute([':id'=>$sid, ':uid'=>$user['id']]);
if ($own->fetchColumn()) {
    if ($action === 'pause' && $paused_until) {
        $stmt = $pdo->prepare('UPDATE subscriptions SET status = :status, paused_until = :until WHERE id = :id');
        $stmt->execute([':status'=>'paused', ':until'=>$paused_until, ':id'=>$sid]);
    } else if ($action === 'resume') {
        $stmt = $pdo->prepare('UPDATE subscriptions SET status = :status, paused_until = NULL WHERE id = :id');
        $stmt->execute([':status'=>'active', ':id'=>$sid]);
    }
}
header('Location: /index.php?page=dashboard&sid='.(int)$sid);
?>