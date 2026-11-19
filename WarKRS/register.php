<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

if (current_user()) {
    $role = current_user()['role'];
    redirect($role === 'mahasiswa' ? 'mahasiswa/index.php' : ($role === 'dosen' ? 'dosen/index.php' : 'index.php'));
}

$error = '';
$daftarDosen = $pdo->query("SELECT id, nidn, nama FROM dosen ORDER BY nama ASC")->fetchAll();
$tahunPilihan = range(2022, 2027);
$prodiPilihan = [
    'Pendidikan Teknologi Informatika Dan Komputer',
    'Teknik Elektro',
    'Pendidikan Teknik Elektro'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $prodi = trim($_POST['prodi'] ?? '');
    $angkatan = (int)($_POST['angkatan'] ?? 0);
    $dosenId = (int)($_POST['dosen_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if ($nim === '' || $nama === '' || !in_array($prodi, $prodiPilihan, true) || !in_array($angkatan, $tahunPilihan, true) || $dosenId < 1 || $password === '' || $konfirmasi === '') {
        $error = 'Semua kolom wajib diisi dengan pilihan yang tersedia.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Password tidak sama. Pastikan password dan konfirmasi password sama.';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter.';
    } else {
        try {
            $pdo->beginTransaction();
            $cekDosen = $pdo->prepare("SELECT id FROM dosen WHERE id = ? LIMIT 1");
            $cekDosen->execute([$dosenId]);
            if (!$cekDosen->fetch()) throw new Exception('Dosen wali yang dipilih tidak tersedia.');

            $cekNim = $pdo->prepare("SELECT id FROM mahasiswa WHERE nim = ? LIMIT 1");
            $cekNim->execute([$nim]);
            if ($cekNim->fetch()) throw new Exception('NIM tersebut sudah terdaftar.');

            $buatUser = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'mahasiswa')");
            $buatUser->execute([$nim, password_hash($password, PASSWORD_DEFAULT)]);
            $userId = (int)$pdo->lastInsertId();

            $buatMahasiswa = $pdo->prepare("INSERT INTO mahasiswa (user_id, nim, nama, prodi, angkatan, dosen_id) VALUES (?, ?, ?, ?, ?, ?)");
            $buatMahasiswa->execute([$userId, $nim, $nama, $prodi, $angkatan, $dosenId]);
            $pdo->commit();
            flash('success', 'Registrasi mahasiswa berhasil. Silakan login menggunakan NIM dan password yang dibuat.');
            redirect('login.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e instanceof PDOException && $e->getCode() === '23000' ? 'NIM sudah digunakan. Periksa kembali data Anda.' : $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Registrasi Mahasiswa · WarKRS</title>
<link rel="stylesheet" href="/WarKRS/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<style>
.register-card{width:min(700px,100%);background:#fff;border-radius:18px;padding:32px;box-shadow:0 18px 60px #1b355215}.register-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 18px}.register-grid .full{grid-column:1/-1}.register-card h1{font-size:25px;margin:0 0 8px;color:#1a2a40}.register-card .intro{font-size:12px;color:#8793a4;line-height:1.7;margin:0 0 20px}.register-card label{display:block;font-size:10px;font-weight:700;color:#526178;margin:13px 0 7px}.register-card .field{padding:12px;width:100%}.register-foot{text-align:center;font-size:11px;color:#8793a4;margin-top:18px}.register-foot a{color:#2864dc;font-weight:700}@media(max-width:600px){.register-card{padding:23px 18px;border-radius:0;min-height:100vh}.register-grid{grid-template-columns:1fr}.register-grid .full{grid-column:auto}}
</style></head>
<body class="login-body"><div class="register-card">
<div class="eyebrow">UNIVERSITAS NEGERI MEDAN · WARKRS</div><h1>Registrasi Mahasiswa</h1>
<p class="intro">Lengkapi data diri, pilih program studi, tahun angkatan, dan dosen wali. Data akan tersimpan pada database WarKRS.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if (!$daftarDosen): ?><div class="alert error">Belum ada dosen terdaftar. Silakan hubungi admin atau tunggu dosen melakukan registrasi.</div><?php else: ?>
<form method="post" autocomplete="on"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<div class="register-grid">
<div><label for="nim">NIM</label><input class="field" id="nim" name="nim" maxlength="30" value="<?= e($_POST['nim'] ?? '') ?>" required></div>
<div><label for="angkatan">Tahun Angkatan</label><select class="field" id="angkatan" name="angkatan" required><option value="">Pilih tahun</option><?php foreach ($tahunPilihan as $tahun): ?><option value="<?= $tahun ?>" <?= (string)($_POST['angkatan'] ?? '') === (string)$tahun ? 'selected' : '' ?>><?= $tahun ?></option><?php endforeach; ?></select></div>
<div class="full"><label for="nama">Nama Lengkap</label><input class="field" id="nama" name="nama" maxlength="120" value="<?= e($_POST['nama'] ?? '') ?>" required></div>
<div class="full"><label for="prodi">Program Studi</label><select class="field" id="prodi" name="prodi" required><option value="">Pilih program studi</option><?php foreach ($prodiPilihan as $p): ?><option value="<?= e($p) ?>" <?= ($_POST['prodi'] ?? '') === $p ? 'selected' : '' ?>><?= e($p) ?></option><?php endforeach; ?></select></div>
<div class="full"><label for="dosen_id">Dosen Wali</label><select class="field" id="dosen_id" name="dosen_id" required><option value="">Pilih dosen wali</option><?php foreach ($daftarDosen as $i => $dosen): ?><option value="<?= (int)$dosen['id'] ?>" <?= (string)($_POST['dosen_id'] ?? '') === (string)$dosen['id'] ? 'selected' : '' ?>><?= $i + 1 ?>. <?= e($dosen['nama']) ?> — NIDN <?= e($dosen['nidn']) ?></option><?php endforeach; ?></select></div>
<div><label for="password">Buat Password</label><input class="field" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required></div>
<div><label for="konfirmasi_password">Konfirmasi Password</label><input class="field" id="konfirmasi_password" name="konfirmasi_password" type="password" minlength="8" autocomplete="new-password" required></div>
</div><p id="passwordHint" style="font-size:11px;color:#8793a4;margin-top:10px">Password minimal 8 karakter dan kedua kolom wajib sama.</p>
<button class="btn btn-primary btn-full" type="submit" style="margin-top:18px;padding:13px">Daftar Akun →</button></form>
<script>
const pw=document.getElementById('password'), cpw=document.getElementById('konfirmasi_password'), hint=document.getElementById('passwordHint');
function checkPw(){if(cpw.value && pw.value!==cpw.value){hint.textContent='Password tidak sama.';hint.style.color='#d94b4b';cpw.setCustomValidity('Password tidak sama');}else{hint.textContent='Password minimal 8 karakter dan kedua kolom wajib sama.';hint.style.color='#8793a4';cpw.setCustomValidity('');}}
pw.addEventListener('input',checkPw);cpw.addEventListener('input',checkPw);
</script>
<?php endif; ?>
<div class="register-foot">Sudah memiliki akun? <a href="/WarKRS/login.php">Masuk</a> · <a href="/WarKRS/register_dosen.php">Daftar sebagai dosen</a></div>
</div></body></html>
