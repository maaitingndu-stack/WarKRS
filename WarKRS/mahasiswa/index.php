<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
require_role('mahasiswa');

$mahasiswaId = (int)(current_user()['profile_id'] ?? 0);
if ($mahasiswaId < 1) {
    http_response_code(403);
    exit('Profil mahasiswa tidak ditemukan. Silakan hubungi administrator.');
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM krs WHERE mahasiswa_id = ?");
$stmt->execute([$mahasiswaId]);
$totalPengajuan = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT status, total_sks, created_at FROM krs WHERE mahasiswa_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$mahasiswaId]);
$krsTerakhir = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

$stmt = $pdo->prepare("SELECT m.nama, m.nim, m.prodi, m.angkatan, d.nama AS nama_dosen, d.nidn FROM mahasiswa m LEFT JOIN dosen d ON d.id = m.dosen_id WHERE m.id = ? LIMIT 1");
$stmt->execute([$mahasiswaId]);
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

$mataKuliahTersedia = $pdo->query("SELECT kode,nama,sks,semester FROM mata_kuliah ORDER BY semester,kode")->fetchAll();

$stats = [
    'Total Pengajuan KRS' => $totalPengajuan,
    'Status KRS Terakhir' => $krsTerakhir['status'] ?? 'Belum mengajukan',
    'Total SKS Terakhir' => $krsTerakhir ? (int)$krsTerakhir['total_sks'] . ' SKS' : '0 SKS'
];
$pageTitle = 'Dashboard Mahasiswa';
$active = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>
<div class="welcome-row">
  <div>
    <div class="eyebrow">STUDENT WORKSPACE</div>
    <h1>Selamat datang, <?= e(explode(' ', current_user()['name'])[0]) ?>! <span class="wave">✦</span></h1>
    <p>Pantau informasi akademik dan kelola rencana studimu melalui WarKRS.</p>
  </div>
  <div class="date-chip">● Tahun Akademik 2026/2027</div>
</div>
<div class="stats-grid">
<?php foreach ($stats as $label => $value): ?>
  <div class="stat-card">
    <div class="stat-top"><span><?= e($label) ?></span><span class="stat-dot"></span></div>
    <div class="stat-value" style="font-size:<?= is_numeric($value) ? '30px' : '19px' ?>"><?= e((string)$value) ?></div>
    <div class="stat-note">Informasi akademik <span>↗</span></div>
  </div>
<?php endforeach; ?>
</div>
<div class="dashboard-grid">
  <div class="panel welcome-panel">
    <div class="panel-kicker">WAR KRS · STUDENT PORTAL</div>
    <h2>Rencanakan studimu,<br><em>selangkah lebih mudah.</em></h2>
    <p>Pilih mata kuliah yang tersedia, ajukan KRS, dan pantau persetujuan dosen wali melalui satu tempat.</p>
    <a class="btn btn-primary" href="/WarKRS/mahasiswa/katalog.php">Jelajahi mata kuliah <span>→</span></a>
    <div class="decor-orb orb-one"></div><div class="decor-orb orb-two"></div>
  </div>
  <div class="panel quick-panel">
    <div class="panel-heading"><div><h3>Akses cepat</h3><p>Menu akademik mahasiswa</p></div><span class="heading-icon">✧</span></div>
    <a class="quick-link" href="/WarKRS/mahasiswa/katalog.php"><span class="quick-icon blue">▤</span><span><b>Katalog mata kuliah</b><small>Lihat mata kuliah yang tersedia</small></span><strong>→</strong></a>
    <a class="quick-link" href="/WarKRS/mahasiswa/krs.php"><span class="quick-icon purple">▣</span><span><b>KRS saya</b><small>Ajukan dan pantau status KRS</small></span><strong>→</strong></a>
  </div>
</div>
<div class="panel" style="padding:22px;margin-top:20px">
  <div class="panel-heading"><div><h3>Informasi mahasiswa</h3><p>Data profil yang terdaftar pada WarKRS</p></div></div>
  <?php if ($profil): ?>
  <div class="table-scroll"><table><tbody>
    <tr><th>Nama</th><td><?= e($profil['nama']) ?></td></tr>
    <tr><th>NIM</th><td><?= e($profil['nim']) ?></td></tr>
    <tr><th>Program Studi</th><td><?= e($profil['prodi']) ?></td></tr>
    <tr><th>Angkatan</th><td><?= e((string)$profil['angkatan']) ?></td></tr>
    <tr><th>Dosen Wali</th><td><?= e($profil['nama_dosen'] ?? 'Belum ditentukan') ?><?= !empty($profil['nidn']) ? ' — NIDN ' . e($profil['nidn']) : '' ?></td></tr>
  </tbody></table></div>
  <?php else: ?>
    <p>Data profil mahasiswa belum tersedia. Silakan hubungi administrator.</p>
  <?php endif; ?>
</div>

<div class="panel table-panel" style="margin-top:20px">
  <div class="table-toolbar"><div><h3>Mata Kuliah Tersedia</h3><p>Daftar mata kuliah yang telah dimasukkan admin ke katalog WarKRS.</p></div><span class="badge-soft"><?= count($mataKuliahTersedia) ?> mata kuliah</span></div>
  <div class="table-scroll"><table><thead><tr><th>Kode</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Semester</th></tr></thead><tbody>
  <?php foreach ($mataKuliahTersedia as $mk): ?><tr><td><?= e($mk['kode']) ?></td><td><b><?= e($mk['nama']) ?></b></td><td><?= (int)$mk['sks'] ?> SKS</td><td><?= (int)$mk['semester'] ?></td></tr><?php endforeach; ?>
  <?php if (!$mataKuliahTersedia): ?><tr><td colspan="4" class="empty">Belum ada mata kuliah yang didaftarkan admin.</td></tr><?php endif; ?>
  </tbody></table></div>
</div>

<div class="notice"><span>ⓘ</span><div><b>Informasi prototipe</b><p>WarKRS menggunakan data simulasi untuk keperluan proyek perkuliahan dan belum terhubung dengan sistem akademik resmi UNIMED.</p></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
