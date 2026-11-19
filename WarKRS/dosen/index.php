<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';
require_role('dosen');
$dosenId = (int)(current_user()['profile_id'] ?? 0);
if ($dosenId < 1) { http_response_code(403); exit('Profil dosen tidak ditemukan. Silakan hubungi admin.'); }
$q = $pdo->prepare("SELECT COUNT(*) FROM mahasiswa WHERE dosen_id=?"); $q->execute([$dosenId]); $totalMhs=(int)$q->fetchColumn();
$q = $pdo->prepare("SELECT COUNT(*) FROM krs k JOIN mahasiswa m ON m.id=k.mahasiswa_id WHERE m.dosen_id=? AND k.status='Menunggu'"); $q->execute([$dosenId]); $menunggu=(int)$q->fetchColumn();
$q = $pdo->prepare("SELECT nim,nama,prodi,angkatan FROM mahasiswa WHERE dosen_id=? ORDER BY nama"); $q->execute([$dosenId]); $mahasiswa=$q->fetchAll();
$pageTitle='Dashboard Dosen'; $active='dashboard'; require __DIR__ . '/../includes/header.php';
?>
<div class="welcome-row"><div><div class="eyebrow">DOSEN WALI WORKSPACE</div><h1>Selamat datang, <?= e(explode(' ',current_user()['name'])[0]) ?>!</h1><p>Pantau mahasiswa bimbingan dan proses pengajuan KRS mereka.</p></div><div class="date-chip">WarKRS · Dosen Wali</div></div>
<div class="stats-grid"><div class="stat-card"><div class="stat-top"><span>Mahasiswa Bimbingan</span><span class="stat-dot"></span></div><div class="stat-value"><?= $totalMhs ?></div><div class="stat-note">Mahasiswa terdaftar</div></div><div class="stat-card"><div class="stat-top"><span>Menunggu Persetujuan</span><span class="stat-dot"></span></div><div class="stat-value"><?= $menunggu ?></div><div class="stat-note">Pengajuan KRS</div></div></div>
<div class="panel table-panel"><div class="table-toolbar"><div><h3>Mahasiswa yang Memilih Anda</h3><p>Daftar mahasiswa yang memilih Anda sebagai dosen pembimbing saat registrasi.</p></div><a class="btn btn-primary" href="/WarKRS/dosen/persetujuan.php">Periksa Persetujuan KRS →</a></div><div class="table-scroll"><table><thead><tr><th>No.</th><th>NIM</th><th>Nama Mahasiswa</th><th>Program Studi</th><th>Angkatan</th></tr></thead><tbody><?php foreach($mahasiswa as $i=>$m): ?><tr><td><?= $i+1 ?></td><td><?= e($m['nim']) ?></td><td><b><?= e($m['nama']) ?></b></td><td><?= e($m['prodi']) ?></td><td><?= (int)$m['angkatan'] ?></td></tr><?php endforeach; ?><?php if(!$mahasiswa): ?><tr><td colspan="5" class="empty">Belum ada mahasiswa yang memilih Anda.</td></tr><?php endif; ?></tbody></table></div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
