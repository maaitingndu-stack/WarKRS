-- Jalankan satu kali pada database warkrs yang sudah ada setelah backup.
USE warkrs;

-- Tambahkan kolom gedung hanya jika belum tersedia.
SET @has_gedung = (
  SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'jadwal' AND COLUMN_NAME = 'gedung'
);
SET @sql_gedung = IF(@has_gedung = 0,
  'ALTER TABLE jadwal ADD COLUMN gedung VARCHAR(100) NOT NULL DEFAULT ''Gedung Teknik'' AFTER ruang',
  'SELECT ''Kolom gedung sudah tersedia'' AS info');
PREPARE stmt_gedung FROM @sql_gedung;
EXECUTE stmt_gedung;
DEALLOCATE PREPARE stmt_gedung;

-- Hapus enam data contoh bawaan versi awal jika belum pernah dipakai dalam KRS.
DELETE FROM mata_kuliah
WHERE kode IN ('PTI101','PTI102','PTI201','PTI202','PTI203','PTI301')
  AND NOT EXISTS (
    SELECT 1 FROM jadwal j
    JOIN detail_krs d ON d.jadwal_id = j.id
    WHERE j.mata_kuliah_id = mata_kuliah.id
  );

INSERT INTO mata_kuliah(kode,nama,sks,semester) VALUES
('1MKU40009','ISBD',2,4),('3TIK40031','MICRO TEACHING',2,4),
('3TIK40032','PEMODELAN 3 DIMENSI',2,4),('3TIK40033','MANAJEMEN PROYEK TI',2,4),
('3TIK40034','MULTIMEDIA',3,4),('3TIK40035','SISTEM PENDUKUNG KEPUTUSAN',2,4),
('3TIK40036','CLOUD COMPUTING',2,4),('3TIK40037','INTERNET OF THINGS (IOT)',3,4),
('3TIK40038','KECERDASAN BUATAN (AI)',3,4),
('1MKU40003','PENDIDIKAN AGAMA KRISTEN PROTESTAN',2,4),
('1MKU40014','PENDIDIKAN KEWARGANEGARAAN',2,4),('3TIK40015','TEKNIK ANIMASI',2,4),
('3TIK40024','PI P-1',2,4),('3TIK40025','PEMROGRAMAN BERORIENTASI OBJEK',2,4),
('3TIK40026','EVALUASI HASIL BELAJAR',2,4),('3TIK40027','METODOLOGI PENELITIAN',2,4),
('3TIK40028','PEMROGRAMAN WEB',2,4),('3TIK40029','KOMUNIKASI DATA',2,4),
('3TIK40030','TELAAH KURIKULUM',2,4)
ON DUPLICATE KEY UPDATE nama=VALUES(nama);

INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT id,'D','Selasa','10:30:00','13:00:00','12.02.11B.2','Gedung Teknik' FROM mata_kuliah WHERE kode='1MKU40009'
AND NOT EXISTS (SELECT 1 FROM jadwal WHERE mata_kuliah_id=mata_kuliah.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT id,'D','Selasa','13:00:00','15:30:00','89.02.04A','Gedung Teknik' FROM mata_kuliah WHERE kode='3TIK40031'
AND NOT EXISTS (SELECT 1 FROM jadwal WHERE mata_kuliah_id=mata_kuliah.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT id,'D','Rabu','13:00:00','15:30:00','Lab_Komputer_B','Gedung Teknik' FROM mata_kuliah WHERE kode='3TIK40032'
AND NOT EXISTS (SELECT 1 FROM jadwal WHERE mata_kuliah_id=mata_kuliah.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT id,'D','Rabu','08:00:00','10:30:00','89.03.02.C.1','Gedung Teknik' FROM mata_kuliah WHERE kode='3TIK40033'
AND NOT EXISTS (SELECT 1 FROM jadwal WHERE mata_kuliah_id=mata_kuliah.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT id,'D','Senin','13:00:00','15:30:00','12.02.11B.1','Gedung Teknik' FROM mata_kuliah WHERE kode='3TIK40034'
AND NOT EXISTS (SELECT 1 FROM jadwal WHERE mata_kuliah_id=mata_kuliah.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT id,'D','Rabu','10:30:00','13:00:00','89.03.01.B.1','Gedung Teknik' FROM mata_kuliah WHERE kode='3TIK40035'
AND NOT EXISTS (SELECT 1 FROM jadwal WHERE mata_kuliah_id=mata_kuliah.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT id,'D','Jumat','13:00:00','15:30:00','Lab_Komputer_A','Gedung Teknik' FROM mata_kuliah WHERE kode='3TIK40036'
AND NOT EXISTS (SELECT 1 FROM jadwal WHERE mata_kuliah_id=mata_kuliah.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT id,'D','Selasa','08:00:00','10:30:00','Lab_Komputer_A','Gedung Teknik' FROM mata_kuliah WHERE kode='3TIK40037'
AND NOT EXISTS (SELECT 1 FROM jadwal WHERE mata_kuliah_id=mata_kuliah.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT id,'D','Senin','10:30:00','13:00:00','89.03.01.A.1','Gedung Teknik' FROM mata_kuliah WHERE kode='3TIK40038'
AND NOT EXISTS (SELECT 1 FROM jadwal WHERE mata_kuliah_id=mata_kuliah.id);
