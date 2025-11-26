<?php
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
$pdo = DB::conn();
$subsStmt = $pdo->prepare('SELECT s.*, a.line1, a.city, a.pincode FROM subscriptions s LEFT JOIN addresses a ON s.address_id = a.id WHERE s.user_id = :uid ORDER BY s.id DESC');
$subsStmt->execute([':uid' => $user['id']]);
$subs = $subsStmt->fetchAll();
$selectedSid = isset($_GET['sid']) ? (int)$_GET['sid'] : (isset($subs[0]) ? (int)$subs[0]['id'] : 0);
$sub = null;
if ($selectedSid) {
    foreach ($subs as $s) { if ((int)$s['id'] === $selectedSid) { $sub = $s; break; } }
    if (!$sub) { $sub = $subs[0] ?? null; $selectedSid = $sub ? (int)$sub['id'] : 0; }
}
$products = $pdo->query('SELECT id,name,type,milk_type,unit,default_unit_qty,price FROM products WHERE active = true ORDER BY type,name')->fetchAll();
$packs = $pdo->query('SELECT id,name FROM packaging_options ORDER BY id')->fetchAll();
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Dashboard</title><link rel="stylesheet" href="styles.css"></head><body>';
echo '<div class="container">';
echo '<div class="topbar">';
echo '<h2>Welcome, ' . htmlspecialchars($user['name']) . '</h2>';
echo '<div>';
if ($user['role'] === 'admin') echo '<a class="btn secondary" href="index.php?page=admin">Admin</a> ';
if (in_array($user['role'], ['vendor','admin'])) echo '<a class="btn secondary" href="index.php?page=vendor">Vendor</a> ';
echo '<form style="display:inline" method="post" action="actions/logout.php"><button class="btn secondary" type="submit">Logout</button></form>';
echo '</div>';
echo '</div>';
if (!empty($subs)) {
    echo '<div class="card">';
    echo '<label>Choose Subscription</label>';
    echo '<select onchange="location.href=\'index.php?page=dashboard&sid=\'+this.value+'"'>;
    foreach ($subs as $s) {
        $sel = ((int)$s['id'] === $selectedSid) ? ' selected' : '';
        $label = 'ID#'.$s['id'].' - '.htmlspecialchars($s['plan']).' / '.htmlspecialchars($s['frequency']).' - '.htmlspecialchars($s['city'] ?: '').' '.htmlspecialchars($s['pincode'] ?: '');
        echo '<option value="'.$s['id'].'"'.$sel.'>'.$label.'</option>';
    }
    echo '</select>';
    echo '</div>';
}
if ($sub) {
    echo '<div class="card">';
    echo '<h3>Your Subscription</h3>';
    echo '<p>Status: ' . htmlspecialchars($sub['status']) . '</p>';
    echo '<p>Plan: ' . htmlspecialchars($sub['plan']) . ' | Frequency: ' . htmlspecialchars($sub['frequency']) . '</p>';
    if ($sub['line1']) echo '<p>Address: ' . htmlspecialchars($sub['line1']) . ', ' . htmlspecialchars($sub['city']) . ' ' . htmlspecialchars($sub['pincode']) . '</p>';
    echo '<form method="post" action="actions/pause_resume.php">';
    echo '<input type="hidden" name="subscription_id" value="' . (int)$selectedSid . '" />';
    if ($sub['status'] === 'active') {
        echo '<input type="hidden" name="action" value="pause" />';
        echo '<label>Pause until</label><input type="date" name="paused_until" required />';
        echo '<button class="btn" type="submit">Pause</button>';
    } else {
        echo '<input type="hidden" name="action" value="resume" />';
        echo '<button class="btn" type="submit">Resume</button>';
    }
    echo '</form>';
    echo '<h4>Update Subscription</h4>';
    echo '<form method="post" action="actions/update_subscription.php">';
    echo '<input type="hidden" name="subscription_id" value="' . (int)$sub['id'] . '" />';
    echo '<label>Plan</label><select name="plan"><option value="monthly"' . ($sub['plan']==='monthly'?' selected':'') . '>Monthly</option><option value="pay_per_delivery"' . ($sub['plan']==='pay_per_delivery'?' selected':'') . '>Pay per Delivery</option></select>';
    echo '<label>Frequency</label><select name="frequency"><option value="daily"' . ($sub['frequency']==='daily'?' selected':'') . '>Daily</option><option value="alternate"' . ($sub['frequency']==='alternate'?' selected':'') . '>Alternate-day</option><option value="weekly"' . ($sub['frequency']==='weekly'?' selected':'') . '>Weekly</option></select>';
    echo '<div class="items">';
    $items = $pdo->prepare('SELECT si.*, p.name FROM subscription_items si JOIN products p ON si.product_id = p.id WHERE si.subscription_id = :sid');
    $items->execute([':sid' => $sub['id']]);
    $existing = $items->fetchAll();
    foreach ($existing as $idx => $it) {
        echo '<div class="item">';
        echo '<label>Product</label><select name="items['.$idx.'][product_id]">';
        foreach ($products as $p) {
            $sel = ((int)$p['id'] === (int)$it['product_id']) ? ' selected' : '';
            $label = htmlspecialchars($p['name']);
            echo '<option value="'.$p['id'].'"'.$sel.'>'.$label.'</option>';
        }
        echo '</select>';
        echo '<label>Packaging</label><select name="items['.$idx.'][packaging_option_id]">';
        foreach ($packs as $pk) {
            $sel = ((int)$pk['id'] === (int)$it['packaging_option_id']) ? ' selected' : '';
            echo '<option value="'.$pk['id'].'"'.$sel.'>'.htmlspecialchars($pk['name']).'</option>';
        }
        echo '</select>';
        echo '<label>Quantity (L)</label><input type="number" step="0.1" min="0.1" name="items['.$idx.'][quantity]" value="'.htmlspecialchars($it['quantity']).'" />';
        echo '</div>';
    }
    echo '</div>';
    echo '<button class="btn" type="submit">Save Changes</button>';
    echo '</form>';
    echo '</div>';
    // Delivery calendar
    $monthParam = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
    $firstDay = $monthParam . '-01';
    $startTs = strtotime($firstDay);
    $daysInMonth = (int)date('t', $startTs);
    echo '<div class="card">';
    echo '<h3>Delivery Calendar (' . htmlspecialchars($monthParam) . ')</h3>';
    echo '<p><a class="btn secondary" href="index.php?page=dashboard&sid='.(int)$selectedSid.'&month=' . date('Y-m', strtotime('-1 month', $startTs)) . '">Prev</a> ';
    echo '<a class="btn secondary" href="index.php?page=dashboard&sid='.(int)$selectedSid.'&month=' . date('Y-m', strtotime('+1 month', $startTs)) . '">Next</a></p>';
    $datesStmt = $pdo->prepare('SELECT delivery_date, status FROM delivery_dates WHERE subscription_id = :sid AND delivery_date BETWEEN :from AND :to');
    $datesStmt->execute([':sid'=>$sub['id'], ':from'=>$firstDay, ':to'=>date('Y-m-t', $startTs)]);
    $overrides = [];
    foreach ($datesStmt->fetchAll() as $row) { $overrides[$row['delivery_date']] = $row['status']; }
    $extrasStmt = $pdo->prepare('SELECT de.id, de.delivery_date, p.name, de.quantity FROM delivery_extras de JOIN products p ON de.product_id=p.id WHERE de.subscription_id = :sid AND de.delivery_date BETWEEN :from AND :to ORDER BY de.delivery_date');
    $extrasStmt->execute([':sid'=>$sub['id'], ':from'=>$firstDay, ':to'=>date('Y-m-t', $startTs)]);
    $extrasByDate = [];
    foreach ($extrasStmt->fetchAll() as $ex) { $d=$ex['delivery_date']; if (!isset($extrasByDate[$d])) $extrasByDate[$d]=[]; $extrasByDate[$d][]=$ex; }
    echo '<div class="calendar" style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px">';
    for ($d=1; $d<=$daysInMonth; $d++) {
        $date = date('Y-m-d', strtotime("$monthParam-" . str_pad($d,2,'0',STR_PAD_LEFT)));
        $dow = date('N', strtotime($date));
        $status = $overrides[$date] ?? null;
        $deliver = false;
        if ($status === 'holiday') { $deliver=false; }
        else if ($sub['status'] !== 'active') { $deliver=false; }
        else if (!empty($sub['paused_until']) && $date <= $sub['paused_until']) { $deliver=false; }
        else {
            if ($sub['frequency']==='daily') $deliver=true;
            else if ($sub['frequency']==='alternate') $deliver=((int)date('z', strtotime($date)) % 2)===0;
            else if ($sub['frequency']==='weekly') $deliver=($dow===1);
        }
        $cls = $deliver ? 'background:#ecf2ff' : 'background:#fff';
        echo '<div style="border:1px solid #eee;border-radius:6px;padding:8px;'.$cls.'">';
        echo '<div><strong>' . $d . '</strong> ' . ($deliver ? '<span style="color:#2563eb">Deliver</span>' : '<span style="color:#6b7280">No Delivery</span>') . '</div>';
        echo '<form method="post" action="actions/calendar_set_holiday.php" style="margin-top:6px">';
        echo '<input type="hidden" name="subscription_id" value="' . (int)$selectedSid . '" />';
        echo '<input type="hidden" name="date" value="' . $date . '" />';
        echo '<button class="btn secondary" type="submit">Mark Holiday</button>';
        echo '</form>';
        echo '<form method="post" action="actions/calendar_add_extra.php" style="margin-top:6px">';
        echo '<input type="hidden" name="subscription_id" value="' . (int)$selectedSid . '" />';
        echo '<input type="hidden" name="date" value="' . $date . '" />';
        echo '<label style="margin-top:6px">Extra Item</label><select name="product_id">';
        foreach ($products as $p) { echo '<option value="'.$p['id'].'">'.htmlspecialchars($p['name']).'</option>'; }
        echo '</select>';
        echo '<label>Packaging</label><select name="packaging_option_id">';
        foreach ($packs as $pk) { echo '<option value="'.$pk['id'].'">'.htmlspecialchars($pk['name']).'</option>'; }
        echo '</select>';
        echo '<label>Qty</label><input type="number" step="0.1" min="0.1" name="quantity" />';
        echo '<button class="btn" type="submit">Add Extra</button>';
        echo '</form>';
        if (!empty($extrasByDate[$date])) {
            echo '<div style="margin-top:6px">';
            foreach ($extrasByDate[$date] as $ex) {
                echo '<div>' . htmlspecialchars($ex['name']) . ' x ' . htmlspecialchars($ex['quantity']) . ' '; 
                echo '<form style="display:inline" method="post" action="actions/calendar_remove_extra.php">';
                echo '<input type="hidden" name="id" value="' . (int)$ex['id'] . '" />';
                echo '<input type="hidden" name="date" value="' . $date . '" />';
                echo '<button class="btn secondary" type="submit">Remove</button>';
                echo '</form></div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';
} else {
    echo '<div class="card">';
    echo '<h3>Create Subscription</h3>';
    echo '<form method="post" action="actions/create_subscription.php">';
    echo '<label>Plan</label><select name="plan"><option value="monthly">Monthly</option><option value="pay_per_delivery">Pay per Delivery</option></select>';
    echo '<label>Frequency</label><select name="frequency"><option value="daily">Daily</option><option value="alternate">Alternate-day</option><option value="weekly">Weekly</option></select>';
    echo '<fieldset><legend>Delivery Address</legend>';
    echo '<label>Line 1</label><input type="text" name="line1" required />';
    echo '<label>City</label><input type="text" name="city" required />';
    echo '<label>Pincode</label><input type="text" name="pincode" required />';
    echo '</fieldset>';
    echo '<h4>Select Products</h4>';
    echo '<div id="items">';
    for ($i=0;$i<2;$i++) {
        echo '<div class="item">';
        echo '<label>Product</label><select name="items['.$i.'][product_id]">';
        foreach ($products as $p) {
            $label = htmlspecialchars($p['name']);
            echo '<option value="'.$p['id'].'">'.$label.'</option>';
        }
        echo '</select>';
        echo '<label>Packaging</label><select name="items['.$i.'][packaging_option_id]">';
        foreach ($packs as $pk) {
            echo '<option value="'.$pk['id'].'">'.htmlspecialchars($pk['name']).'</option>';
        }
        echo '</select>';
        echo '<label>Quantity (L)</label><input type="number" step="0.1" min="0.1" name="items['.$i.'][quantity]" />';
        echo '</div>';
    }
    echo '</div>';
    echo '<button class="btn" type="submit">Create</button>';
    echo '</form>';
    echo '</div>';
}
echo '</div>';
echo '</body></html>';
?>