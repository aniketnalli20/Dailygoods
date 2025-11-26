<?php
require_once __DIR__ . '/../config/config.php';
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Login</title><link rel="stylesheet" href="styles.css">';
echo '</head><body>';
echo '<div class="auth-wrap">';
echo '<div class="auth-left">';
echo '<div class="auth-card">';
echo '<div class="brand">Dailygoods</div>';
echo '<h2 class="auth-title">Welcome back</h2>';
echo '<p class="muted">Please enter your details</p>';
echo '<form method="post" action="actions/login.php">';
echo '<label>Email address</label><input class="input" type="email" name="email" placeholder="you@gmail.com" required />';
echo '<label>Password</label><input class="input" type="password" name="password" required />';
echo '<div class="row">';
echo '<div><input type="checkbox" id="remember" name="remember" value="1" /> <label for="remember" style="display:inline;color:#374151">Remember for 30 days</label></div>';
echo '<div><a class="link" href="#">Forgot password</a></div>';
echo '</div>';
echo '<button class="btn primary" style="width:100%" type="submit">Sign in</button>';
echo '<button class="btn google" style="width:100%" type="button" onclick="alert(\'Google sign-in coming soon\')">Sign in with Google</button>';
echo '</form>';
echo '<div class="actions"><span>Don\'t have an account?</span> <a class="link" href="index.php?page=register">Sign up</a></div>';
echo '</div>';
echo '</div>';
echo '<div class="auth-right">';
$img = null;
if (defined('CDN_BASE_URL') && CDN_BASE_URL) {
    $img = rtrim(CDN_BASE_URL,'/') . '/login-hero.jpg';
} else {
    $localJpg = __DIR__ . '/../assets/images/login-hero.jpg';
    $localSvg = __DIR__ . '/../assets/images/login-hero.svg';
    if (file_exists($localJpg)) {
        $img = 'assets/images/login-hero.jpg';
    } else if (file_exists($localSvg)) {
        $img = 'assets/images/login-hero.svg';
    } else {
        $img = 'assets/images/login-hero.svg';
    }
}
echo '<img class="auth-illustration" src="' . $img . '" alt="Dailygoods - Fresh Milk" />';
echo '</div>';
echo '</div>';
echo '</body></html>';
?>