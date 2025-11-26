<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$password = 'demo1234';
if (!$name || !$email || !$phone) {
    header('Location: /index.php?page=register');
    exit;
}
$id = Auth::register($name, $email, $phone, $password);
if ($id) {
    $_SESSION['user_id'] = $id;
    header('Location: /index.php?page=dashboard');
    exit;
}
header('Location: /index.php?page=register');
?>