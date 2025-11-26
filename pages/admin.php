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
$products = $pdo->query("SELECT * FROM products ORDER BY type, name")->fetchAll();
$packaging = $pdo->query("SELECT * FROM packaging_options ORDER BY id")->fetchAll();
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
echo '<div class="card">';
echo '<h3>Manage Products</h3>';
echo '<form method="post" action="actions/products_create.php" style="margin-bottom:10px">';
echo '<h4>Add Product</h4>';
echo '<label>Name</label><input type="text" name="name" required />';
echo '<label>Type</label><select name="type"><option value="milk">milk</option><option value="addon">addon</option></select>';
echo '<label>Milk Type</label><input type="text" name="milk_type" placeholder="whole/skim/organic/A2/flavored" />';
echo '<label>Unit</label><input type="text" name="unit" placeholder="L/ml/g/unit" required />';
echo '<label>Default Unit Qty</label><input type="number" name="default_unit_qty" required />';
echo '<label>Price</label><input type="number" step="0.01" name="price" required />';
echo '<button class="btn" type="submit">Create</button>';
echo '</form>';
echo '<h4>Existing Products</h4>';
foreach ($products as $pr) {
    echo '<form method="post" action="actions/products_update.php" style="border:1px solid #eee;padding:8px;border-radius:6px;margin-bottom:8px">';
    echo '<input type="hidden" name="id" value="'.(int)$pr['id'].'" />';
    echo '<label>Name</label><input type="text" name="name" value="'.htmlspecialchars($pr['name']).'" />';
    echo '<label>Type</label><select name="type"><option value="milk"'.($pr['type']==='milk'?' selected':'').'>milk</option><option value="addon"'.($pr['type']==='addon'?' selected':'').'>addon</option></select>';
    echo '<label>Milk Type</label><input type="text" name="milk_type" value="'.htmlspecialchars($pr['milk_type']).'" />';
    echo '<label>Unit</label><input type="text" name="unit" value="'.htmlspecialchars($pr['unit']).'" />';
    echo '<label>Default Unit Qty</label><input type="number" name="default_unit_qty" value="'.htmlspecialchars($pr['default_unit_qty']).'" />';
    echo '<label>Price</label><input type="number" step="0.01" name="price" value="'.htmlspecialchars($pr['price']).'" />';
    echo '<button class="btn" type="submit">Save</button>';
    echo '</form>';
    echo '<form method="post" action="actions/products_toggle.php" style="margin-top:-6px;margin-bottom:10px">';
    echo '<input type="hidden" name="id" value="'.(int)$pr['id'].'" />';
    echo '<button class="btn secondary" type="submit">'.($pr['active']?'Deactivate':'Activate').'</button>';
    echo '</form>';
}
echo '</div>';
echo '<div class="card">';
echo '<h3>Manage Packaging Options</h3>';
echo '<form method="post" action="actions/packaging_create.php" style="margin-bottom:10px">';
echo '<label>Name</label><input type="text" name="name" required />';
echo '<button class="btn" type="submit">Add</button>';
echo '</form>';
foreach ($packaging as $pk) {
    echo '<div style="display:flex;align-items:center;justify-content:space-between;border:1px solid #eee;padding:8px;border-radius:6px;margin-bottom:8px">';
    echo '<div>'.htmlspecialchars($pk['name']).'</div>';
    echo '<form method="post" action="actions/packaging_delete.php">';
    echo '<input type="hidden" name="id" value="'.(int)$pk['id'].'" />';
    echo '<button class="btn secondary" type="submit">Delete</button>';
    echo '</form>';
    echo '</div>';
}
echo '</div>';
echo '<div class="card">';
echo '<h3>Import Users (usersdg.tsv)</h3>';
if (isset($_GET['imported'])) {
    $ins = isset($_GET['ins']) ? (int)$_GET['ins'] : 0;
    $upd = isset($_GET['upd']) ? (int)$_GET['upd'] : 0;
    echo '<p>Imported. Inserted: ' . $ins . ' Updated: ' . $upd . '</p>';
}
echo '<form method="post" action="actions/import_usersdg.php">';
echo '<p>This reads <code>pages/usersdg.tsv</code> with columns: <code>name</code>, <code>email</code>, <code>phone</code>, <code>password</code>, <code>role</code>. Missing fields auto-filled.</p>';
echo '<button class="btn" type="submit">Run Import</button>';
echo '</form>';
echo '<form method="get" action="actions/enrich_usersdg.php" style="margin-top:8px">';
echo '<input type="hidden" name="import" value="1" />';
echo '<button class="btn secondary" type="submit">Enrich TSV (emails/phones/passwords) + Import</button>';
echo '</form>';
echo '</div>';
echo '</body></html>';
?>