<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user) { header('Location: /index.php?page=login'); exit; }
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$date = isset($_POST['date']) ? $_POST['date'] : null;
if (!$id) { header('Location: /index.php?page=dashboard'); exit; }
$pdo = DB::conn();
$chk = $pdo->prepare('SELECT de.id FROM delivery_extras de JOIN subscriptions s ON de.subscription_id = s.id WHERE de.id = :id AND s.user_id = :uid');
$chk->execute([':id'=>$id, ':uid'=>$user['id']]);
if ($chk->fetchColumn()) {
    $del = $pdo->prepare('DELETE FROM delivery_extras WHERE id = :id');
    $del->execute([':id'=>$id]);
}
header('Location: /index.php?page=dashboard' . ($date ? '&month=' . substr($date,0,7) : ''));
?>