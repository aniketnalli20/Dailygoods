<?php
session_start();
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/DB.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$user = Auth::currentUser();

if ($page === 'home') {
    $pdo = DB::conn();
    $products = $pdo->query('SELECT id,name,type,price FROM products WHERE active = 1 ORDER BY type,name')->fetchAll();
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Dailygoods</title><link rel="icon" href="https://img.icons8.com/ios-filled/50/laguardia.png"><link rel="stylesheet" href="styles.css"></head><body>';
    echo '<div class="shell">';
    echo '<div class="header">';
    echo '<div class="logo"><img class="brand-logo" src="https://img.icons8.com/ios-filled/50/6d28d9/laguardia.png" alt="Logo" />Dailygoods</div>';
    echo '<div class="search"><img src="https://img.icons8.com/ios-glyphs/24/search--v1.png" alt="" /><input type="text" placeholder="Search milk, yogurt, butter..." /></div>';
    echo '<div>';
    if ($user) echo '<a class="btn secondary" href="?page=dashboard">Dashboard</a>'; else echo '<a class="btn secondary" href="?page=login">Login</a>'; 
    echo '</div>';
    echo '</div>';
    echo '<div class="pillbar">';
    echo '<div class="pill">Milk</div><div class="pill secondary">Add-ons</div><div class="pill">A2</div><div class="pill">Organic</div><div class="pill">Skim</div>';
    echo '</div>';
    echo '<div class="hero">Morning essentials delivered daily</div>';
    echo '<h3 style="margin:8px 0">Popular Products</h3>';
    echo '<div class="grid">';
    foreach ($products as $p) {
        $img = ($p['type']==='milk') ? 'https://img.icons8.com/fluency/96/milk-bottle.png' : 'https://img.icons8.com/fluency/96/shopping-basket.png';
        echo '<div class="card shop">';
        echo '<img src="'.$img.'" alt="" />';
        echo '<div class="name">'.htmlspecialchars($p['name']).'</div>';
        echo '<div class="meta">'.htmlspecialchars(strtoupper($p['type'])).'</div>';
        echo '<div class="price">₹'.number_format((float)$p['price'],2).'</div>';
        if ($user) {
            echo '<a class="btn add" href="?page=dashboard">Manage Subscription</a>';
        } else {
            echo '<a class="btn add" href="?page=login">Login to Subscribe</a>';
        }
        echo '</div>';
    }
    echo '</div>';
    echo '<div class="footer">Icons by <a href="https://icons8.com" target="_blank" rel="noopener">Icons8</a></div>';
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

if ($page === 'admin') {
    if (!$user || ($user['role'] !== 'admin')) {
        header('Location: /index.php?page=dashboard');
        exit;
    }
    require __DIR__ . '/pages/admin.php';
    exit;
}

if ($page === 'vendor') {
    if (!$user || !in_array($user['role'], ['vendor','admin'])) {
        header('Location: /index.php?page=dashboard');
        exit;
    }
    require __DIR__ . '/pages/vendor.php';
    exit;
}

http_response_code(404);
echo 'Not Found';
?>
