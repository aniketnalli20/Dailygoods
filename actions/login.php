<?php
$remember = isset($_POST['remember']) && $_POST['remember'] == '1';
if ($remember) {
    session_set_cookie_params(['lifetime' => 60*60*24*30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
}
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