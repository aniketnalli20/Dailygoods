<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user) { header('Location: /index.php?page=login'); exit; }
$action = isset($_POST['action']) ? $_POST['action'] : 'pause';
$paused_until = isset($_POST['paused_until']) ? $_POST['paused_until'] : null;
$pdo = DB::conn();
$sidStmt = $pdo->prepare('SELECT id FROM subscriptions WHERE user_id = :uid ORDER BY id DESC LIMIT 1');
$sidStmt->execute([':uid'=>$user['id']]);
$sid = (int)$sidStmt->fetchColumn();
if ($sid) {
    if ($action === 'pause' && $paused_until) {
        $stmt = $pdo->prepare('UPDATE subscriptions SET status = :status, paused_until = :until WHERE id = :id');
        $stmt->execute([':status'=>'paused', ':until'=>$paused_until, ':id'=>$sid]);
    } else if ($action === 'resume') {
        $stmt = $pdo->prepare('UPDATE subscriptions SET status = :status, paused_until = NULL WHERE id = :id');
        $stmt->execute([':status'=>'active', ':id'=>$sid]);
    }
}
header('Location: /index.php?page=dashboard');
?>