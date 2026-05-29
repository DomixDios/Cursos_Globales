<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/constants.php';

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array
{
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, nombre_completo, email, rol, avatar, bio FROM usuarios WHERE id = ? AND activo = 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function currentUserRole(): ?string
{
    $u = currentUser();
    return $u['rol'] ?? null;
}

function login(string $email, string $password): array
{
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? AND activo = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Credenciales incorrectas'];
    }

    $_SESSION['user_id'] = $user['id'];
    return ['success' => true, 'user' => $user];
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function requireRole(string ...$roles): void
{
    $role = currentUserRole();
    if ($role === null || !in_array($role, $roles, true)) {
        redirect(BASE_URL . '/index.php?error=forbidden');
    }
}
