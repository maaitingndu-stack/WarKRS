<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never {
    if (preg_match('/^(https?:)?\/\//i', $path)) {
        header('Location: ' . $path);
    } else {
        header('Location: /WarKRS/' . ltrim($path, '/'));
    }
    exit;
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_login(): void {
    if (!current_user()) redirect('login.php');
}

function require_role(string|array $roles): void {
    require_login();
    $roles = (array)$roles;
    if (!in_array(current_user()['role'], $roles, true)) {
        http_response_code(403);
        exit('Anda tidak memiliki akses ke halaman ini.');
    }
}

function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf'] ?? '';
        if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
            http_response_code(419);
            exit('Sesi formulir tidak valid. Silakan kembali dan coba lagi.');
        }
    }
}

function role_label(string $role): string {
    return match ($role) {
        'admin' => 'Administrator',
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen Wali',
        default => ucfirst($role)
    };
}

function rupiah_dummy(): string { return 'Simulasi akademik'; }
