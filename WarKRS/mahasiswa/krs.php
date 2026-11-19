<?php
require_once __DIR__.'/../includes/functions.php'; require_once __DIR__.'/../config/database.php'; require_role('mahasiswa');
$mid=current_user()['profile_id'];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $ids=array_values(array_unique(array_map('intval',$_POST['jadwal_ids']??[])));
 if(!$ids){flash('error','Pilih minimal satu jadwal mata kuliah.');redirect('krs.php');}
 $marks=implode(',',array_fill(0,count($ids),'?'));
 $st=$pdo->prepare("SELECT j.*,mk.nama,mk.sks FROM jadwal j JOIN mata_kuliah mk ON mk.id=j.mata_kuliah_id WHERE j.id IN ($marks)");
 $st->execute($ids);$chosen=$st->fetchAll();
 if(count($chosen)!==count($ids)){flash('error','Terdapat jadwal yang tidak valid.');redirect('krs.php');}
 $total=array_sum(array_column($chosen,'sks'));$conflict=false;
 for($i=0;$i<count($chosen);$i++) for($j=$i+1;$j<count($chosen);$j++){
  $a=$chosen[$i];$b=$chosen[$j];
  if($a['hari']===$b['hari'] && $a['jam_mulai']<$b['jam_selesai'] && $b['jam_mulai']<$a['jam_selesai']) $conflict=true;
 }
 if($conflict){flash('error','Pilihan memiliki jadwal yang bertabrakan. Periksa kembali jadwal Anda.');redirect('krs.php');}
 if($total>24){flash('error','Total pilihan melebihi batas simulasi 24 SKS.');redirect('krs.php');}
 $pdo->beginTransaction();
 try{
  $pdo->prepare("INSERT INTO krs(mahasiswa_id,total_sks,status) VALUES(?,?,'Menunggu')")->execute([$mid,$total]);$kid=(int)$pdo->lastInsertId();
  $ins=$pdo->prepare("INSERT INTO detail_krs(krs_id,jadwal_id) VALUES(?,?)");foreach($ids as $id)$ins->execute([$kid,$id]);
  $pdo->commit();flash('success','Pengajuan KRS berhasil dikirim dan menunggu persetujuan dosen wali.');
 }catch(Throwable $e){$pdo->rollBack();flash('error','Pengajuan gagal disimpan.');}
 redirect('krs.php');
}
$catalog=$pdo->query("SELECT j.id,j.kelas,j.hari,j.jam_mulai,j.jam_selesai,j.ruang,j.gedung,mk.kode,mk.nama,mk.sks FROM jadwal j JOIN mata_kuliah mk ON mk.id=j.mata_kuliah_id ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat'),j.jam_mulai")->fetchAll();
$st=$pdo->prepare("SELECT * FROM krs WHERE mahasiswa_id=? ORDER BY created_at DESC");$st->execute([$mid]);$history=$st->fetchAll();
$pageTitle='KRS Saya';$active='krs';require __DIR__.'/../includes/header.php';
?>
<div class="page-heading"><div><div class="eyebrow">RENCANA STUDI</div><h1>Pengisian KRS</h1><p>Pilih jadwal mata kuliah, periksa total SKS, lalu kirim pengajuan.</p></div><div class="date-chip">Batas simulasi: 24 SKS</div></div>
<form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><div class="panel table-panel"><div class="table-toolbar"><div><h3>Pilih mata kuliah</h3><p>Centang jadwal yang ingin diambil.</p></div><span class="badge-soft">Tahun Akademik 2026/2027</span></div><div class="table-scroll"><table><thead><tr><th>Pilih</th><th>Kode / Mata Kuliah</th><th>SKS</th><th>Kelas</th><th>Hari</th><th>Waktu</th><th>Ruang</th><th>Gedung</th></tr></thead><tbody>
<?php foreach($catalog as $r): ?><tr><td><input class="check" type="checkbox" name="jadwal_ids[]" value="<?= (int)$r['id'] ?>" data-sks="<?= (int)$r['sks'] ?>" onchange="updateSks()"></td><td><span class="code-pill"><?= e($r['kode']) ?></span><br><b><?= e($r['nama']) ?></b></td><td><?= (int)$r['sks'] ?></td><td><?= e($r['kelas']) ?></td><td><?= e($r['hari']) ?></td><td><?= e(substr($r['jam_mulai'],0,5)) ?>–<?= e(substr($r['jam_selesai'],0,5)) ?></td><td><?= e($r['ruang']) ?></td><td><?= e($r['gedung']) ?></td></tr><?php endforeach; ?>
<?php if(!$catalog): ?><tr><td colspan="8" class="empty">Belum ada jadwal mata kuliah. Hubungi admin simulasi.</td></tr><?php endif; ?>
</tbody></table></div><div class="krs-submit-row"><div><small>Total pilihan</small><strong><span id="totalSks">0</span> <small>SKS</small></strong></div><button class="btn btn-primary" <?= !$catalog?'disabled':'' ?>>Kirim Pengajuan KRS →</button></div></div></form>
<div class="panel table-panel history-panel"><div class="table-toolbar"><div><h3>Riwayat pengajuan</h3><p>Status pengajuan KRS Anda.</p></div></div><div class="table-scroll"><table><thead><tr><th>Tanggal</th><th>Total SKS</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($history as $h): ?><tr><td><?= e(date('d M Y H:i',strtotime($h['created_at']))) ?></td><td><?= (int)$h['total_sks'] ?> SKS</td><td><span class="status <?= $h['status']==='Disetujui'?'approved':($h['status']==='Ditolak'?'rejected':'pending') ?>"><?= e($h['status']) ?></span></td><td><a class="text-link" href="cetak_krs.php?id=<?= (int)$h['id'] ?>">Lihat / cetak →</a></td></tr><?php endforeach; ?>
<?php if(!$history): ?><tr><td colspan="4" class="empty">Belum ada pengajuan KRS.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
