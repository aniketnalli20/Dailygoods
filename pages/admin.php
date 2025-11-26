<?php
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
$pdo = DB::conn();
$totalSubs = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions")->fetchColumn();
$newSubs = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE date_trunc('month', start_date) = date_trunc('month', current_date)")->fetchColumn();
$activeSubs = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'")->fetchColumn();
$pausedSubs = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'paused'")->fetchColumn();
$customers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$walletTotal = (float)$pdo->query("SELECT COALESCE(SUM(wallet_balance),0) FROM subscriptions")->fetchColumn();
$freqDist = $pdo->query("SELECT frequency, COUNT(*) AS c FROM subscriptions GROUP BY frequency ORDER BY c DESC")->fetchAll();
$popular = $pdo->query("SELECT p.name, SUM(si.quantity) AS qty FROM subscription_items si JOIN products p ON si.product_id = p.id GROUP BY p.name ORDER BY qty DESC LIMIT 10")->fetchAll();
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Admin</title><link rel="stylesheet" href="styles.css"></head><body>';
echo '<div class="container">';
echo '<div class="topbar">';
echo '<h2>Admin Dashboard</h2>';
echo '<div><a class="btn secondary" href="index.php?page=dashboard">Back</a></div>';
echo '</div>';
echo '<div class="card">';
echo '<h3>Key Metrics</h3>';
echo '<p>Total Subscriptions: ' . $totalSubs . '</p>';
echo '<p>New This Month: ' . $newSubs . '</p>';
echo '<p>Active: ' . $activeSubs . ' | Paused: ' . $pausedSubs . '</p>';
echo '<p>Customers: ' . $customers . '</p>';
echo '<p>Wallet Total: ₹' . number_format($walletTotal,2) . '</p>';
echo '</div>';
echo '<div class="card">';
echo '<h3>Frequency Distribution</h3>';
foreach ($freqDist as $f) {
    echo '<p>' . htmlspecialchars($f['frequency']) . ': ' . (int)$f['c'] . '</p>';
}
echo '</div>';
echo '<div class="card">';
echo '<h3>Popular Products</h3>';
echo '<div class="items">';
foreach ($popular as $row) {
    echo '<div class="item"><div><strong>' . htmlspecialchars($row['name']) . '</strong></div><div>Qty: ' . htmlspecialchars($row['qty']) . '</div><div></div></div>';
}
echo '</div>';
echo '</div>';
echo '</body></html>';
?>