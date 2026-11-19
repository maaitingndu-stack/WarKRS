<?php
require_once __DIR__.'/../includes/functions.php'; require_once __DIR__.'/../config/database.php'; require_role('mahasiswa');
$rows=$pdo->query("SELECT mk.*, GROUP_CONCAT(CONCAT('Kelas ',j.kelas,' · ',j.hari,' ',TIME_FORMAT(j.jam_mulai,'%H:%i'),'-',TIME_FORMAT(j.jam_selesai,'%H:%i'),' · ',j.ruang,' · ',j.gedung) SEPARATOR ' | ') jadwal FROM mata_kuliah mk LEFT JOIN jadwal j ON j.mata_kuliah_id=mk.id GROUP BY mk.id ORDER BY mk.semester,mk.kode")->fetchAll();
$pageTitle='Katalog Mata Kuliah';$active='katalog';require __DIR__.'/../includes/header.php';
?>
<div class="page-heading"><div><div class="eyebrow">AKADEMIK MAHASISWA</div><h1>Katalog Mata Kuliah</h1><p>Temukan mata kuliah yang ingin Anda masukkan ke rencana studi.</p></div><a class="btn btn-primary" href="krs.php">Lihat KRS Saya →</a></div>
<div class="panel table-panel"><div class="table-toolbar"><div><h3>Mata kuliah tersedia</h3><p><?= count($rows) ?> mata kuliah dalam katalog simulasi</p></div><input class="search-input" placeholder="Cari mata kuliah..." oninput="filterTable(this,'catalogTable')"></div><div class="table-scroll"><table id="catalogTable"><thead><tr><th>Kode</th><th>Mata Kuliah</th><th>SKS</th><th>Semester</th><th>Jadwal</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><span class="code-pill"><?= e($r['kode']) ?></span></td><td><b><?= e($r['nama']) ?></b></td><td><?= (int)$r['sks'] ?> SKS</td><td><?= (int)$r['semester'] ?></td><td><?= e($r['jadwal']?:'Jadwal belum tersedia') ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
