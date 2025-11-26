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
// prepared statement to fetch coordinates per subscription
$coordStmt = $pdo->prepare('SELECT a.lat, a.lng, a.line1 FROM subscriptions s LEFT JOIN addresses a ON s.address_id = a.id WHERE s.id = :sid');
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
    $coordStmt->execute([':sid'=>$s['id']]);
    $c = $coordStmt->fetch();
    $manifest[$key][] = ['address'=>$s['line1'], 'items'=>$final, 'lat'=>($c['lat'] ?? null), 'lng'=>($c['lng'] ?? null)];
}
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Vendor</title><link rel="icon" href="https://img.icons8.com/ios-filled/50/laguardia.png"><link rel="stylesheet" href="styles.css"></head><body>';
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
// Vendor product upload
if (in_array($user['role'], ['vendor','admin'])) {
    echo '<div class="card">';
    echo '<h3>Upload Product to Feed</h3>';
    echo '<p>New items uploaded by vendors are hidden until an admin activates them.</p>';
    echo '<form method="post" action="actions/products_create_vendor.php">';
    echo '<label>Name</label><input type="text" name="name" required />';
    echo '<label>Type</label><select name="type"><option value="milk">milk</option><option value="addon">addon</option></select>';
    echo '<label>Milk Type</label><input type="text" name="milk_type" placeholder="whole/skim/organic/A2/flavored" />';
    echo '<label>Unit</label><input type="text" name="unit" placeholder="L/ml/g/unit" required />';
    echo '<label>Default Unit Qty</label><input type="number" name="default_unit_qty" required />';
    echo '<label>Price</label><input type="number" step="0.01" name="price" required />';
    echo '<button class="btn" type="submit">Upload</button>';
    echo '</form>';
    echo '</div>';
}
// Map view card
echo '<div class="card">';
echo '<h3>Map View</h3>';
echo '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />';
echo '<div id="map" style="height:400px;border:1px solid #eee;border-radius:6px"></div>';
echo '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>';
echo '<script>'; 
echo 'var map = L.map("map").setView([28.6139, 77.2090], 11);';
echo 'L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {maxZoom: 19, attribution: "&copy; OpenStreetMap contributors"}).addTo(map);';
foreach ($manifest as $zone => $rows) {
    foreach ($rows as $row) {
        $lat = isset($row['lat']) ? (float)$row['lat'] : 0;
        $lng = isset($row['lng']) ? (float)$row['lng'] : 0;
        if ($lat && $lng) {
            $addr = htmlspecialchars($row['address'] ?: '');
            $itemsText = '';
            foreach ($row['items'] as $it) { $itemsText .= htmlspecialchars($it['name']).' x '.htmlspecialchars($it['quantity'])."<br>"; }
            echo 'L.marker(['.$lat.','.$lng.']).addTo(map).bindPopup("<strong>'.$addr.'</strong><br>'.$itemsText.'");';
        }
    }
}
echo '</script>';
echo '</div>';
echo '</body></html>';
?>