<?php
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
$pdo = DB::conn();
$subs = $pdo->query("SELECT s.id, s.frequency, s.status, s.paused_until, a.line1, a.city, a.pincode FROM subscriptions s LEFT JOIN addresses a ON s.address_id = a.id WHERE s.status IN ('active','paused')")->fetchAll();
$deliverToday = [];
$doy = (int)date('z');
$dow = (int)date('N');
foreach ($subs as $s) {
    if ($s['status'] !== 'active') continue;
    if (!empty($s['paused_until']) && date('Y-m-d') <= $s['paused_until']) continue;
    // override by calendar
    $ov = $pdo->prepare('SELECT status FROM delivery_dates WHERE subscription_id = :sid AND delivery_date = :d');
    $ov->execute([':sid'=>$s['id'], ':d'=>date('Y-m-d')]);
    $override = $ov->fetchColumn();
    if ($override === 'holiday' || $override === 'skipped') continue;
    if ($override === 'scheduled') { $deliverToday[] = $s; continue; }
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
    $items = $pdo->prepare('SELECT p.id, p.name, si.quantity FROM subscription_items si JOIN products p ON si.product_id = p.id WHERE si.subscription_id = :sid');
    $items->execute([':sid'=>$s['id']]);
    $base = $items->fetchAll();
    $ext = $pdo->prepare('SELECT p.id, p.name, de.quantity FROM delivery_extras de JOIN products p ON de.product_id = p.id WHERE de.subscription_id = :sid AND de.delivery_date = :d');
    $ext->execute([':sid'=>$s['id'], ':d'=>date('Y-m-d')]);
    $extras = $ext->fetchAll();
    // merge quantities by product
    $merged = [];
    foreach ($base as $it) { $merged[$it['id']] = ['name'=>$it['name'], 'quantity'=>$it['quantity']]; }
    foreach ($extras as $it) { if (!isset($merged[$it['id']])) $merged[$it['id']] = ['name'=>$it['name'], 'quantity'=>0]; $merged[$it['id']]['quantity'] += $it['quantity']; }
    $final = [];
    foreach ($merged as $pid=>$val) { $final[] = ['name'=>$val['name'], 'quantity'=>$val['quantity']]; }
    $manifest[$key][] = ['address'=>$s['line1'], 'items'=>$final];
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