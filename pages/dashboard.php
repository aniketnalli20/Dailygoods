<?php
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
$user = Auth::currentUser();
$pdo = DB::conn();
$sub = null;
$stmt = $pdo->prepare('SELECT s.*, a.line1, a.city, a.pincode FROM subscriptions s LEFT JOIN addresses a ON s.address_id = a.id WHERE s.user_id = :uid ORDER BY s.id DESC LIMIT 1');
$stmt->execute([':uid' => $user['id']]);
$sub = $stmt->fetch();
$products = $pdo->query('SELECT id,name,type,milk_type,unit,default_unit_qty,price FROM products WHERE active = true ORDER BY type,name')->fetchAll();
$packs = $pdo->query('SELECT id,name FROM packaging_options ORDER BY id')->fetchAll();
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Dashboard</title><link rel="stylesheet" href="styles.css"></head><body>';
echo '<div class="container">';
echo '<div class="topbar">';
echo '<h2>Welcome, ' . htmlspecialchars($user['name']) . '</h2>';
echo '<form method="post" action="actions/logout.php"><button class="btn secondary" type="submit">Logout</button></form>';
echo '</div>';
if ($sub) {
    echo '<div class="card">';
    echo '<h3>Your Subscription</h3>';
    echo '<p>Status: ' . htmlspecialchars($sub['status']) . '</p>';
    echo '<p>Plan: ' . htmlspecialchars($sub['plan']) . ' | Frequency: ' . htmlspecialchars($sub['frequency']) . '</p>';
    if ($sub['line1']) echo '<p>Address: ' . htmlspecialchars($sub['line1']) . ', ' . htmlspecialchars($sub['city']) . ' ' . htmlspecialchars($sub['pincode']) . '</p>';
    echo '<form method="post" action="actions/pause_resume.php">';
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