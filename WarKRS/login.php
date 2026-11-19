<?php
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/config/database.php';
if (current_user()) { $r=current_user()['role']; redirect($r==='mahasiswa'?'mahasiswa/index.php':($r==='dosen'?'dosen/index.php':'index.php')); }
$error='';
$flashMessage = get_flash();
if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf();
  $username=trim($_POST['username']??''); $password=$_POST['password']??'';
  $stmt=$pdo->prepare("
    SELECT
        u.*,
        COALESCE(m.nama, d.nama, u.username) AS display_name,
        COALESCE(m.id, d.id, 0) AS profile_id
    FROM users u
    LEFT JOIN mahasiswa m ON m.user_id = u.id
    LEFT JOIN dosen d ON d.user_id = u.id
    WHERE u.username = ?
    LIMIT 1
");
  $stmt->execute([$username]); $u=$stmt->fetch();
  if($u && password_verify($password,$u['password'])) {
    session_regenerate_id(true);
   $_SESSION['user'] = [
    'id' => $u['id'],
    'username' => $u['username'],
    'name' => $u['display_name'],
    'role' => $u['role'],
    'profile_id' => (int)$u['profile_id']
];
    if ($u['role'] === 'mahasiswa') redirect('mahasiswa/index.php');
    if ($u['role'] === 'dosen') redirect('dosen/index.php');
    redirect('index.php');
  } else $error='Username atau password tidak sesuai.';
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Masuk · WarKRS</title><link rel="stylesheet" href="/WarKRS/assets/css/style.css"><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet"></head>
<body class="login-body"><div class="login-wrap"><section class="login-visual"><a class="brand brand-white" href="#"><span class="brand-mark">W</span><span>War<span class="brand-light">KRS</span><small>Academic workspace</small></span></a><div class="login-hero"><div class="eyebrow light">UNIVERSITAS NEGERI MEDAN</div><h1>Rencanakan<br>kuliahmu dengan<br><em>lebih cerdas.</em></h1><p>Satu ruang untuk mengelola rencana studi, memantau pengajuan, dan berkolaborasi dengan dosen wali.</p></div><div class="login-bottom">Sistem Informasi Pengelolaan Kartu Rencana Studi · Prototipe</div><div class="login-shape"></div></section>
<section class="login-form-side"><div class="login-form"><div class="mobile-brand"><span class="brand-mark">W</span><b>WarKRS</b></div><div class="eyebrow">SELAMAT DATANG KEMBALI</div><h2>Masuk ke akun Anda</h2><p class="muted">Masuk menggunakan username dan password akun WarKRS Anda.</p>
<?php if($flashMessage): ?><div class="alert <?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Username / NIM / NIDN</label><input class="field" name="username" placeholder="Masukkan username, NIM, atau NIDN" required autofocus><label>Password</label><input class="field" type="password" name="password" placeholder="Masukkan password" required><button class="btn btn-primary btn-full" type="submit">Masuk ke WarKRS <span>→</span></button></form>
<p style="margin-top:18px;text-align:center;font-size:11px;color:#7e8a9b">Belum memiliki akun? <a href="/WarKRS/register.php" style="color:#2864dc;font-weight:700">Daftar sebagai mahasiswa</a><br><a href="/WarKRS/register_dosen.php" style="color:#2864dc;font-weight:700">Daftar sebagai dosen</a></p><small class="login-disclaimer">Data simulasi untuk keperluan pembelajaran. Bukan portal akademik resmi.</small></div></section></div></body></html>
