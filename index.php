<?php
session_start();
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/DB.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$user = Auth::currentUser();

if ($page === 'home') {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Milkride</title><link rel="stylesheet" href="/styles.css"></head><body>';
    echo '<div class="container">';
    echo '<h1>Milkride</h1>';
    echo '<p>Subscribe to fresh milk and add-ons with flexible delivery.</p>';
    if ($user) {
        echo '<a class="btn" href="?page=dashboard">Go to Dashboard</a>';
    } else {
        echo '<div class="actions">';
        echo '<a class="btn" href="?page=login">Login</a>';
        echo '<a class="btn" href="?page=register">Register</a>';
        echo '</div>';
    }
    echo '</div>';
    echo '</body></html>';
    exit;
}

if ($page === 'login') {
    require __DIR__ . '/pages/login.php';
    exit;
}

if ($page === 'register') {
    require __DIR__ . '/pages/register.php';
    exit;
}

if ($page === 'dashboard') {
    if (!$user) {
        header('Location: /index.php?page=login');
        exit;
    }
    require __DIR__ . '/pages/dashboard.php';
    exit;
}

http_response_code(404);
echo 'Not Found';
?>