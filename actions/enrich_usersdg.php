<?php
session_start();
$isCli = php_sapi_name() === 'cli';
require_once __DIR__ . '/../lib/DB.php';
if (!$isCli) {
    require_once __DIR__ . '/../lib/Auth.php';
    $user = Auth::currentUser();
    if (!$user || $user['role'] !== 'admin') { header('Location: /index.php?page=dashboard'); exit; }
}

$path = __DIR__ . '/../pages/usersdg.tsv';
if (!file_exists($path)) { echo 'usersdg.tsv not found'; if (!$isCli) header('Location: /index.php?page=admin'); exit; }
$lines = file($path, FILE_IGNORE_NEW_LINES);
if (!$lines || count($lines) < 2) { echo 'usersdg.tsv empty'; if (!$isCli) header('Location: /index.php?page=admin'); exit; }

function slugify($s) {
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/i', '.', $s);
    $s = preg_replace('/\.+/', '.', $s);
    return trim($s, '.');
}
function genPhone($seedIdx) {
    $start = [6,7,8,9][($seedIdx % 4)];
    $num = (string)$start;
    $rest = str_pad((string)(100000000 + ($seedIdx % 900000000)), 9, '0', STR_PAD_LEFT);
    return $num . substr($rest, 0, 9);
}
function genPassword($name, $idx) {
    $base = strtoupper(substr(preg_replace('/[^a-z]/i','', $name), 0, 2));
    $tail = substr(hash('sha256', $name . '|' . $idx), 0, 8);
    $mix = $base . $tail;
    return preg_replace('/[^A-Za-z0-9]/', 'x', $mix);
}

$header = array_map('trim', explode("\t", $lines[0]));
$map = [];
foreach ($header as $i => $h) { $map[strtolower($h)] = $i; }

$hasEmail = isset($map['email']);
$hasPhone = isset($map['phone']);
$hasPassword = isset($map['password']);
$hasRole = isset($map['role']);

$emails = [];
$phones = [];
$enriched = [];
$enriched[] = 'name\temail\tphone\tpassword\trole';

for ($i = 1; $i < count($lines); $i++) {
    $row = $lines[$i];
    if ($row === '' || $row === null) continue;
    $parts = explode("\t", $row);
    $name = isset($map['name']) && isset($parts[$map['name']]) ? trim($parts[$map['name']]) : '';
    if ($name === '' || strtolower($name) === 'name') continue;
    $slug = slugify($name);
    $emailBase = $slug !== '' ? $slug : ('user' . $i);
    $email = $emailBase . '@example.in';
    $ctr = 1;
    while (isset($emails[$email])) { $email = $emailBase . $ctr . '@example.in'; $ctr++; }
    $emails[$email] = true;
    $phone = genPhone($i);
    while (isset($phones[$phone])) { $i2 = $i + $ctr; $phone = genPhone($i2); $ctr++; }
    $phones[$phone] = true;
    $password = genPassword($name, $i);
    $role = 'customer';
    $enriched[] = $name . "\t" . $email . "\t" . $phone . "\t" . $password . "\t" . $role;
}

file_put_contents($path, implode("\n", $enriched) . "\n");

$doImport = (!$isCli && isset($_GET['import']) && $_GET['import'] == '1') || ($isCli && in_array('--import', $argv));
if ($doImport) {
    // Import into DB
    $pdo = DB::conn();
    $sel = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $ins = $pdo->prepare('INSERT INTO users(name,email,phone,password_hash,role) VALUES(:name,:email,:phone,:hash,:role)');
    $upd = $pdo->prepare('UPDATE users SET name = :name, phone = :phone, password_hash = :hash, role = :role WHERE email = :email');
    $inserted = 0; $updated = 0;
    for ($i = 1; $i < count($enriched); $i++) {
        $parts = explode("\t", $enriched[$i]);
        if (count($parts) < 5) continue;
        list($name,$email,$phone,$password,$role) = $parts;
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sel->execute([':email' => strtolower($email)]);
        $exists = $sel->fetchColumn();
        if ($exists) {
            $upd->execute([':name'=>$name, ':phone'=>$phone, ':hash'=>$hash, ':role'=>$role, ':email'=>strtolower($email)]);
            $updated++;
        } else {
            $ins->execute([':name'=>$name, ':email'=>strtolower($email), ':phone'=>$phone, ':hash'=>$hash, ':role'=>$role]);
            $inserted++;
        }
    }
    if ($isCli) {
        echo "Enriched and imported. Inserted=$inserted Updated=$updated\n";
    } else {
        header('Location: /index.php?page=admin&imported=1&ins=' . $inserted . '&upd=' . $updated);
    }
    exit;
}

if ($isCli) {
    echo "Enriched TSV written to pages/usersdg.tsv\n";
} else {
    header('Location: /index.php?page=admin&enriched=1');
}
?>