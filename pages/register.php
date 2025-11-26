<?php
require_once __DIR__ . '/../config/config.php';
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Sign up</title><link rel="icon" href="https://img.icons8.com/ios-filled/50/laguardia.png"><link rel="stylesheet" href="styles.css">';
echo '</head><body>';
echo '<div class="auth-wrap">';
echo '<div class="auth-left">';
echo '<div class="auth-card">';
echo '<div class="brand"><img class="brand-logo" src="https://img.icons8.com/ios-filled/50/6d28d9/laguardia.png" alt="Logo" />Dailygoods</div>';
echo '<h2 class="auth-title">Create your account</h2>';
echo '<p class="muted">Please enter your details</p>';
echo '<form method="post" action="actions/register.php">';
echo '<label>Name</label><input class="input" type="text" name="name" required />';
echo '<label>Email address</label><input class="input" type="email" name="email" placeholder="you@gmail.com" required />';
echo '<label>Phone</label><input class="input" type="text" name="phone" required />';
echo '<button class="btn primary" style="width:100%" type="submit">Create account</button>';
echo '</form>';
echo '<div class="actions"><span>Already have an account?</span> <a class="link" href="index.php?page=login">Login</a></div>';
echo '</div>';
echo '</div>';
echo '<div class="auth-right"';
$img = null;
if (defined('CDN_BASE_URL') && CDN_BASE_URL) { $img = rtrim(CDN_BASE_URL,'/') . '/login-hero.jpg'; }
else {
    $candidates = [
        __DIR__ . '/../assets/images/login-hero.jpg',
        __DIR__ . '/../assets/images/login-hero.jpeg',
        __DIR__ . '/../assets/images/login-hero.png',
    ];
    $webMap = [
        __DIR__ . '/../assets/images/login-hero.jpg' => 'assets/images/login-hero.jpg',
        __DIR__ . '/../assets/images/login-hero.jpeg' => 'assets/images/login-hero.jpeg',
        __DIR__ . '/../assets/images/login-hero.png' => 'assets/images/login-hero.png',
    ];
    $found = null;
    foreach ($candidates as $p) { if (file_exists($p)) { $found = $p; break; } }
    if ($found) { $img = $webMap[$found]; }
    else { $img = 'assets/images/login-hero.svg'; }
}
echo ' style="background-image:url(' . htmlspecialchars($img) . ');background-size:cover;background-position:center"></div>';
echo '</div>';
echo '<div class="footer">Icons by <a href="https://icons8.com" target="_blank" rel="noopener">Icons8</a></div>';
echo '</body></html>';
?>