<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';
if (current_user()) {
    $role = current_user()['role'];
    redirect($role === 'mahasiswa' ? 'mahasiswa/index.php' : ($role === 'dosen' ? 'dosen/index.php' : 'index.php'));
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nidn = trim($_POST['nidn'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';
    if ($nidn === '' || $nama === '' || $password === '' || $konfirmasi === '') $error = 'Semua kolom wajib diisi.';
    elseif ($password !== $konfirmasi) $error = 'Password tidak sama. Pastikan kedua kolom password sama.';
    elseif (strlen($password) < 8) $error = 'Password minimal 8 karakter.';
    else {
        try {
            $pdo->beginTransaction();
            $check = $pdo->prepare('SELECT id FROM dosen WHERE nidn = ? LIMIT 1');
            $check->execute([$nidn]);
            if ($check->fetch()) throw new Exception('NIDN sudah terdaftar.');
            $user = $pdo->prepare("INSERT INTO users (username,password,role) VALUES (?,?,'dosen')");
            $user->execute([$nidn, password_hash($password, PASSWORD_DEFAULT)]);
            $userId = (int)$pdo->lastInsertId();
            $dosen = $pdo->prepare('INSERT INTO dosen (user_id,nidn,nama) VALUES (?,?,?)');
            $dosen->execute([$userId,$nidn,$nama]);
            $pdo->commit();
            flash('success','Registrasi dosen berhasil. Silakan login menggunakan NIDN dan password yang dibuat.');
            redirect('login.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e instanceof PDOException && $e->getCode() === '23000' ? 'NIDN atau akun sudah digunakan.' : $e->getMessage();
        }
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registrasi Dosen · WarKRS</title><link rel="stylesheet" href="/WarKRS/assets/css/style.css"><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet"><style>.register-card{width:min(600px,100%);background:#fff;border-radius:18px;padding:32px;box-shadow:0 18px 60px #1b355215}.register-card h1{font-size:25px;margin:0 0 8px;color:#1a2a40}.register-card .intro{font-size:12px;color:#8793a4;line-height:1.7}.register-card label{display:block;font-size:10px;font-weight:700;color:#526178;margin:13px 0 7px}.register-card .field{padding:12px;width:100%}.register-foot{text-align:center;font-size:11px;color:#8793a4;margin-top:18px}.register-foot a{color:#2864dc;font-weight:700}@media(max-width:600px){.register-card{padding:23px 18px;border-radius:0;min-height:100vh}}</style></head>
<body class="login-body"><div class="register-card"><div class="eyebrow">UNIVERSITAS NEGERI MEDAN · WARKRS</div><h1>Registrasi Dosen</h1><p class="intro">Daftarkan akun dosen wali. Setelah berhasil, data dosen akan tampil di Data Dosen Admin dan pilihan dosen wali mahasiswa.</p>
<?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label for="nidn">NIDN (digunakan sebagai username login)</label><input class="field" id="nidn" name="nidn" maxlength="30" value="<?= e($_POST['nidn'] ?? '') ?>" required><label for="nama">Nama Lengkap</label><input class="field" id="nama" name="nama" maxlength="120" value="<?= e($_POST['nama'] ?? '') ?>" required><label for="password">Buat Password</label><input class="field" id="password" name="password" type="password" minlength="8" required><label for="konfirmasi_password">Konfirmasi Password</label><input class="field" id="konfirmasi_password" name="konfirmasi_password" type="password" minlength="8" required><p id="passwordHint" style="font-size:11px;color:#8793a4">Password minimal 8 karakter dan harus sama.</p><button class="btn btn-primary btn-full" type="submit" style="margin-top:16px;padding:13px">Daftar sebagai Dosen →</button></form>
<script>const p=document.getElementById('password'),c=document.getElementById('konfirmasi_password'),h=document.getElementById('passwordHint');function ck(){if(c.value&&p.value!==c.value){h.textContent='Password tidak sama.';h.style.color='#d94b4b';c.setCustomValidity('Password tidak sama');}else{h.textContent='Password minimal 8 karakter dan harus sama.';h.style.color='#8793a4';c.setCustomValidity('');}}p.addEventListener('input',ck);c.addEventListener('input',ck);</script>
<div class="register-foot">Sudah punya akun? <a href="/WarKRS/login.php">Masuk</a> · <a href="/WarKRS/register.php">Daftar sebagai mahasiswa</a></div></div></body></html>
