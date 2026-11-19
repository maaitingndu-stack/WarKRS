<?php
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/config/database.php';
if (!current_user()) redirect('login.php');
$role = current_user()['role'];
if ($role === 'mahasiswa') redirect('mahasiswa/index.php');
if ($role === 'dosen') redirect('dosen/index.php');
require_role('admin');
if ($role === 'admin') {
    $stats = [
      'Mahasiswa' => (int)$pdo->query("SELECT COUNT(*) FROM mahasiswa")->fetchColumn(),
      'Dosen' => (int)$pdo->query("SELECT COUNT(*) FROM dosen")->fetchColumn(),
      'Mata Kuliah' => (int)$pdo->query("SELECT COUNT(*) FROM mata_kuliah")->fetchColumn(),
      'Pengajuan KRS' => (int)$pdo->query("SELECT COUNT(*) FROM krs")->fetchColumn()
    ];
} elseif ($role === 'mahasiswa') {
    $q=$pdo->prepare("SELECT COUNT(*) FROM krs WHERE mahasiswa_id=?");
    $q->execute([current_user()['profile_id']]); $count=(int)$q->fetchColumn();
    $stats=['Pengajuan KRS'=>$count,'Status Akademik'=>'Aktif','Periode'=>'2026/2027 Ganjil'];
} else {
    $stats=['Menunggu Persetujuan'=>(int)$pdo->query("SELECT COUNT(*) FROM krs WHERE status='Menunggu'")->fetchColumn(),'Telah Diproses'=>(int)$pdo->query("SELECT COUNT(*) FROM krs WHERE status IN ('Disetujui','Ditolak')")->fetchColumn()];
}
$pageTitle='Dashboard'; $active='dashboard';
require __DIR__.'/includes/header.php';
?>
<div class="welcome-row"><div><div class="eyebrow">OVERVIEW</div><h1>Selamat datang, <?= e(explode(' ', current_user()['name'])[0]) ?>! <span class="wave">✦</span></h1><p>Kelola aktivitas akademik Anda dengan lebih mudah melalui WarKRS.</p></div><div class="date-chip">● Tahun Akademik 2026/2027</div></div>
<div class="stats-grid">
<?php foreach($stats as $label=>$value): ?><div class="stat-card"><div class="stat-top"><span><?= e($label) ?></span><span class="stat-dot"></span></div><div class="stat-value"><?= e((string)$value) ?></div><div class="stat-note">Informasi sistem <span>↗</span></div></div><?php endforeach; ?>
</div>
<div class="dashboard-grid">
  <div class="panel welcome-panel"><div class="panel-kicker">WAR KRS · ACADEMIC PORTAL</div><h2>Semua urusan KRS,<br><em>lebih terarah.</em></h2><p>WarKRS membantu proses pengelolaan mata kuliah, pengajuan KRS, dan persetujuan dosen wali dalam satu tempat.</p>
  <?php if(current_user()['role']==='mahasiswa'): ?><a class="btn btn-primary" href="mahasiswa/katalog.php">Jelajahi mata kuliah <span>→</span></a>
  <?php elseif(current_user()['role']==='dosen'): ?><a class="btn btn-primary" href="dosen/persetujuan.php">Periksa pengajuan <span>→</span></a>
  <?php else: ?><a class="btn btn-primary" href="admin/matakuliah.php">Kelola data akademik <span>→</span></a><?php endif; ?>
  <div class="decor-orb orb-one"></div><div class="decor-orb orb-two"></div></div>
  <div class="panel quick-panel"><div class="panel-heading"><div><h3>Akses cepat</h3><p>Menu yang sering digunakan</p></div><span class="heading-icon">✧</span></div>
  <?php if(current_user()['role']==='admin'): ?>
    <a class="quick-link" href="admin/mahasiswa.php"><span class="quick-icon blue">♙</span><span><b>Data mahasiswa</b><small>Kelola informasi mahasiswa</small></span><strong>→</strong></a>
    <a class="quick-link" href="admin/matakuliah.php"><span class="quick-icon purple">▤</span><span><b>Mata kuliah</b><small>Atur katalog akademik</small></span><strong>→</strong></a>
    <a class="quick-link" href="admin/jadwal.php"><span class="quick-icon orange">◷</span><span><b>Jadwal kuliah</b><small>Kelola waktu perkuliahan</small></span><strong>→</strong></a>
  <?php elseif(current_user()['role']==='mahasiswa'): ?>
    <a class="quick-link" href="mahasiswa/katalog.php"><span class="quick-icon blue">▤</span><span><b>Katalog mata kuliah</b><small>Temukan mata kuliah tersedia</small></span><strong>→</strong></a>
    <a class="quick-link" href="mahasiswa/krs.php"><span class="quick-icon purple">▣</span><span><b>KRS saya</b><small>Lihat pengajuan dan status</small></span><strong>→</strong></a>
  <?php else: ?>
    <a class="quick-link" href="dosen/persetujuan.php"><span class="quick-icon blue">✓</span><span><b>Persetujuan KRS</b><small>Periksa pengajuan mahasiswa</small></span><strong>→</strong></a>
  <?php endif; ?>
  </div>
</div>
<div class="notice"><span>ⓘ</span><div><b>Informasi prototipe</b><p>WarKRS menggunakan data simulasi untuk keperluan proyek perkuliahan dan belum terhubung dengan sistem akademik resmi UNIMED.</p></div></div>
<?php require __DIR__.'/includes/footer.php'; ?>
