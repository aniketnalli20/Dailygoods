<?php
session_start();
require_once __DIR__ . '/../lib/Auth.php';
$email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
if (!$email || !$password) {
    header('Location: /index.php?page=login');
    exit;
}
if (Auth::login($email, $password)) {
    header('Location: /index.php?page=dashboard');
    exit;
}
header('Location: /index.php?page=login');
?>