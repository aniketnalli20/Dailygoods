<?php
require_once __DIR__ . '/DB.php';

class Auth {
    public static function currentUser(): ?array {
        if (!isset($_SESSION['user_id'])) return null;
        $pdo = DB::conn();
        $stmt = $pdo->prepare('SELECT id,name,email,role FROM users WHERE id = :id');
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $u = $stmt->fetch();
        return $u ?: null;
    }

    public static function login(string $email, string $password): bool {
        $pdo = DB::conn();
        $stmt = $pdo->prepare('SELECT id,password_hash FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        if (!$row) return false;
        if (!password_verify($password, $row['password_hash'])) return false;
        $_SESSION['user_id'] = $row['id'];
        return true;
    }

    public static function register(string $name, string $email, string $phone, string $password): ?int {
        $pdo = DB::conn();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users(name,email,phone,password_hash,role) VALUES(:name,:email,:phone,:hash,:role)');
        try {
            $stmt->execute([':name' => $name, ':email' => $email, ':phone' => $phone, ':hash' => $hash, ':role' => 'customer']);
            $id = (int)$pdo->lastInsertId();
            return $id ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
?>