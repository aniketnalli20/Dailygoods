<?php
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
$pdo = DB::conn();
$subs = $pdo->query("SELECT s.id, s.frequency, s.status, a.line1, a.city, a.pincode FROM subscriptions s LEFT JOIN addresses a ON s.address_id = a.id WHERE s.status IN ('active','paused')")->fetchAll();
$deliverToday = [];
$doy = (int)date('z');
$dow = (int)date('N');
foreach ($subs as $s) {
    if ($s['status'] !== 'active') continue;
    $freq = $s['frequency'];
    $ok = false;
    if ($freq === 'daily') $ok = true;
    else if ($freq === 'alternate') $ok = ($doy % 2) === 0;
    else if ($freq === 'weekly') $ok = ($dow === 1);
    if ($ok) $deliverToday[] = $s;
}
$manifest = [];
foreach ($deliverToday as $s) {
    $key = ($s['city'] ?: 'Unknown') . ' ' . ($s['pincode'] ?: '');
    if (!isset($manifest[$key])) $manifest[$key] = [];
    $items = $pdo->prepare('SELECT p.name, si.quantity FROM subscription_items si JOIN products p ON si.product_id = p.id WHERE si.subscription_id = :sid');
    $items->execute([':sid'=>$s['id']]);
    $manifest[$key][] = ['address'=>$s['line1'], 'items'=>$items->fetchAll()];
}
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Vendor</title><link rel="stylesheet" href="styles.css"></head><body>';
echo '<div class="container">';
echo '<div class="topbar">';
echo '<h2>Delivery Manifest</h2>';
echo '<div><a class="btn secondary" href="index.php?page=dashboard">Back</a></div>';
echo '</div>';
foreach ($manifest as $zone => $rows) {
    echo '<div class="card">';
    echo '<h3>' . htmlspecialchars($zone) . '</h3>';
    foreach ($rows as $row) {
        echo '<p><strong>' . htmlspecialchars($row['address'] ?: 'No address') . '</strong></p>';
        foreach ($row['items'] as $it) {
            echo '<p>- ' . htmlspecialchars($it['name']) . ' x ' . htmlspecialchars($it['quantity']) . '</p>';
        }
        echo '<hr />';
    }
    echo '</div>';
}
if (!$manifest) echo '<p>No deliveries scheduled today.</p>';
echo '</div>';
echo '</body></html>';
?>