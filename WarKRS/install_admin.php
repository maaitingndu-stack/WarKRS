<?php
require_once __DIR__ . '/config/database.php';
// Jalankan satu kali setelah database warkrs diimpor, lalu hapus file ini.
$adminUsername = 'UNDEAD KING';
$adminPassword = 'THE KILLER MAN';
try {
    $pdo->beginTransaction();
    $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username,password,role) VALUES (?,?,'admin') ON DUPLICATE KEY UPDATE password=VALUES(password), role='admin'");
    $stmt->execute([$adminUsername, $hash]);
    // Hapus akun demo yang pernah disediakan pada versi lama. Profil akademik tidak dihapus.
    $del = $pdo->prepare("DELETE FROM users WHERE username IN ('admin','mahasiswa','dosen')");
    $del->execute();
    // Pastikan hanya administrator khusus ini yang memiliki role admin.
    $other = $pdo->prepare("DELETE FROM users WHERE role='admin' AND username <> ?");
    $other->execute([$adminUsername]);
    $pdo->commit();
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>WarKRS Admin Setup</title><body style="font:16px Arial;padding:40px"><h2>Administrator WarKRS berhasil dibuat.</h2><p>Akun demo lama telah dihapus.</p><p><b>Segera hapus file install_admin.php dari folder WarKRS.</b></p><p><a href="/WarKRS/login.php">Buka halaman login</a></p></body></html>';
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo 'Pemasangan admin gagal. Periksa database warkrs dan konfigurasi koneksi.';
}
