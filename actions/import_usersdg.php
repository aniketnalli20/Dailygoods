<?php
$isCli = php_sapi_name() === 'cli';
if (!$isCli) session_start();
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/DB.php';
if (!$isCli) {
    $user = Auth::currentUser();
    if (!$user || $user['role'] !== 'admin') { header('Location: /index.php?page=dashboard'); exit; }
}
$path = $isCli && isset($argv[1]) ? $argv[1] : (__DIR__ . '/../pages/usersdg.tsv');
if (!file_exists($path)) { header('Location: /index.php?page=admin'); exit; }
$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!$lines || count($lines) < 2) { header('Location: /index.php?page=admin'); exit; }
$header = array_map('trim', explode("\t", $lines[0]));
$map = [];
foreach ($header as $i => $h) { $map[strtolower($h)] = $i; }
$pdo = DB::conn();
$sel = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$ins = $pdo->prepare('INSERT INTO users(name,email,phone,password_hash,role) VALUES(:name,:email,:phone,:hash,:role)');
$upd = $pdo->prepare('UPDATE users SET name = :name, phone = :phone, password_hash = :hash, role = :role WHERE email = :email');
$inserted = 0;
$updated = 0;
$emailCache = [];
for ($i = 1; $i < count($lines); $i++) {
    $parts = explode("\t", $lines[$i]);
    if (count($parts) === 0) continue;
    $name = isset($map['name']) && isset($parts[$map['name']]) ? trim($parts[$map['name']]) : '';
    if ($name === '') continue;
    $email = isset($map['email']) && isset($parts[$map['email']]) ? strtolower(trim($parts[$map['email']])) : '';
    $phone = isset($map['phone']) && isset($parts[$map['phone']]) ? trim($parts[$map['phone']]) : null;
    $password = 'demo1234';
    $role = isset($map['role']) && isset($parts[$map['role']]) ? trim($parts[$map['role']]) : 'customer';
    if ($email === '') {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $name));
        $base = trim($slug, '.') . '@demo.local';
        $email = $base;
        $ctr = 1;
        while (isset($emailCache[$email])) { $email = trim($slug, '.') . ".$ctr@demo.local"; $ctr++; }
        $emailCache[$email] = true;
    }
    // Force default password for all imported users
    if ($password === '') { $password = 'demo1234'; }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sel->execute([':email' => $email]);
    $exists = $sel->fetchColumn();
    if ($exists) {
        $upd->execute([':name' => $name, ':phone' => $phone, ':hash' => $hash, ':role' => $role, ':email' => $email]);
        $updated++;
    } else {
        $ins->execute([':name' => $name, ':email' => $email, ':phone' => $phone, ':hash' => $hash, ':role' => $role]);
        $inserted++;
    }
}
if ($isCli) {
    echo "Imported TSV. Inserted=$inserted Updated=$updated\n";
} else {
    header('Location: /index.php?page=admin&imported=1&ins=' . $inserted . '&upd=' . $updated);
}
exit;
?>