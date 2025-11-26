<?php
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Register</title><link rel="stylesheet" href="/styles.css"></head><body>';
echo '<div class="container">';
echo '<h2>Register</h2>';
echo '<form method="post" action="/actions/register.php">';
echo '<label>Name</label><input type="text" name="name" required />';
echo '<label>Email</label><input type="email" name="email" required />';
echo '<label>Phone</label><input type="text" name="phone" required />';
echo '<label>Password</label><input type="password" name="password" required />';
echo '<button class="btn" type="submit">Create Account</button>';
echo '</form>';
echo '<p><a href="/index.php?page=login">Already have an account? Login</a></p>';
echo '</div>';
echo '</body></html>';
?>