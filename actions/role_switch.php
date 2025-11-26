<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
$user = Auth::currentUser();
if (!$user || $user['role'] !== 'admin') { header('Location: /index.php?page=dashboard'); exit; }
$to = isset($_POST['to']) ? $_POST['to'] : 'admin';
if (!in_array($to, ['admin','vendor','customer'])) { $to = 'admin'; }
$_SESSION['role_override'] = $to;
header('Location: /index.php?page=dashboard');
?>