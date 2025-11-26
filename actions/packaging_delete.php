<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
if (!$user || $user['role'] !== 'admin') { header('Location: /index.php?page=dashboard'); exit; }
$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: /index.php?page=admin'); exit; }
$pdo = DB::conn();
try {
    $stmt = $pdo->prepare('DELETE FROM packaging_options WHERE id = :id');
    $stmt->execute([':id'=>$id]);
} catch (Exception $e) {
    // ignore if referenced
}
header('Location: /index.php?page=admin');
?>