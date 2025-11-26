<?php
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Login</title><link rel="stylesheet" href="/styles.css"></head><body>';
echo '<div class="container">';
echo '<h2>Login</h2>';
echo '<form method="post" action="/actions/login.php">';
echo '<label>Email</label><input type="email" name="email" required />';
echo '<label>Password</label><input type="password" name="password" required />';
echo '<button class="btn" type="submit">Login</button>';
echo '</form>';
echo '<p><a href="/index.php?page=register">Create an account</a></p>';
echo '</div>';
echo '</body></html>';
?>