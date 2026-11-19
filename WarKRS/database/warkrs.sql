CREATE DATABASE IF NOT EXISTS warkrs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE warkrs;

CREATE TABLE IF NOT EXISTS users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(60) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('admin','mahasiswa','dosen') NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dosen (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL UNIQUE,
 nidn VARCHAR(30) NOT NULL UNIQUE,
 nama VARCHAR(120) NOT NULL,
 CONSTRAINT fk_dosen_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mahasiswa (
 id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NULL UNIQUE,
 nim VARCHAR(30) NOT NULL UNIQUE,
 nama VARCHAR(120) NOT NULL,
 prodi VARCHAR(160) NOT NULL,
 angkatan INT NOT NULL,
 dosen_id INT NOT NULL,
 CONSTRAINT fk_mhs_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 CONSTRAINT fk_mhs_dosen FOREIGN KEY(dosen_id) REFERENCES dosen(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mata_kuliah (
 id INT AUTO_INCREMENT PRIMARY KEY,
 kode VARCHAR(30) NOT NULL UNIQUE,
 nama VARCHAR(160) NOT NULL,
 sks TINYINT UNSIGNED NOT NULL DEFAULT 2,
 semester TINYINT UNSIGNED NOT NULL DEFAULT 4
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS jadwal (
 id INT AUTO_INCREMENT PRIMARY KEY,
 mata_kuliah_id INT NOT NULL,
 kelas ENUM('A','B','C','D') NOT NULL DEFAULT 'A',
 hari ENUM('Senin','Selasa','Rabu','Kamis','Jumat') NOT NULL,
 jam_mulai TIME NOT NULL,
 jam_selesai TIME NOT NULL,
 ruang VARCHAR(40) NOT NULL,
 gedung VARCHAR(100) NOT NULL DEFAULT 'Gedung Teknik',
 CONSTRAINT fk_jadwal_mk FOREIGN KEY(mata_kuliah_id) REFERENCES mata_kuliah(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS krs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 mahasiswa_id INT NOT NULL,
 total_sks TINYINT UNSIGNED NOT NULL,
 status ENUM('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
 approved_by INT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NULL DEFAULT NULL,
 CONSTRAINT fk_krs_mhs FOREIGN KEY(mahasiswa_id) REFERENCES mahasiswa(id),
 CONSTRAINT fk_krs_dosen FOREIGN KEY(approved_by) REFERENCES dosen(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detail_krs (
 id INT AUTO_INCREMENT PRIMARY KEY,
 krs_id INT NOT NULL,
 jadwal_id INT NOT NULL,
 UNIQUE KEY unique_krs_jadwal(krs_id,jadwal_id),
 CONSTRAINT fk_detail_krs FOREIGN KEY(krs_id) REFERENCES krs(id) ON DELETE CASCADE,
 CONSTRAINT fk_detail_jadwal FOREIGN KEY(jadwal_id) REFERENCES jadwal(id)
) ENGINE=InnoDB;

-- 19 mata kuliah dari daftar yang diberikan pengguna.
-- SKS dan semester pada 10 mata kuliah gambar kedua belum tercantum;
-- sementara diisi 2 SKS dan semester 4 agar dapat diedit admin kemudian.
INSERT INTO mata_kuliah(kode,nama,sks,semester) VALUES
('1MKU40009','ISBD',2,4),
('3TIK40031','MICRO TEACHING',2,4),
('3TIK40032','PEMODELAN 3 DIMENSI',2,4),
('3TIK40033','MANAJEMEN PROYEK TI',2,4),
('3TIK40034','MULTIMEDIA',3,4),
('3TIK40035','SISTEM PENDUKUNG KEPUTUSAN',2,4),
('3TIK40036','CLOUD COMPUTING',2,4),
('3TIK40037','INTERNET OF THINGS (IOT)',3,4),
('3TIK40038','KECERDASAN BUATAN (AI)',3,4),
('1MKU40003','PENDIDIKAN AGAMA KRISTEN PROTESTAN',2,4),
('1MKU40014','PENDIDIKAN KEWARGANEGARAAN',2,4),
('3TIK40015','TEKNIK ANIMASI',2,4),
('3TIK40024','PI P-1',2,4),
('3TIK40025','PEMROGRAMAN BERORIENTASI OBJEK',2,4),
('3TIK40026','EVALUASI HASIL BELAJAR',2,4),
('3TIK40027','METODOLOGI PENELITIAN',2,4),
('3TIK40028','PEMROGRAMAN WEB',2,4),
('3TIK40029','KOMUNIKASI DATA',2,4),
('3TIK40030','TELAAH KURIKULUM',2,4)
ON DUPLICATE KEY UPDATE nama=VALUES(nama);

-- Jadwal 9 mata kuliah yang memiliki informasi jadwal pada gambar.
-- Semua waktu disesuaikan ke sesi tetap 2 jam 30 menit (08.00-10.30,
-- 10.30-13.00, 13.00-15.30). Kelas D dipertahankan dari daftar sumber.
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT mk.id,'D','Selasa','10:30:00','13:00:00','12.02.11B.2','Gedung Teknik' FROM mata_kuliah mk WHERE mk.kode='1MKU40009'
AND NOT EXISTS (SELECT 1 FROM jadwal j WHERE j.mata_kuliah_id=mk.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT mk.id,'D','Selasa','13:00:00','15:30:00','89.02.04A','Gedung Teknik' FROM mata_kuliah mk WHERE mk.kode='3TIK40031'
AND NOT EXISTS (SELECT 1 FROM jadwal j WHERE j.mata_kuliah_id=mk.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT mk.id,'D','Rabu','13:00:00','15:30:00','Lab_Komputer_B','Gedung Teknik' FROM mata_kuliah mk WHERE mk.kode='3TIK40032'
AND NOT EXISTS (SELECT 1 FROM jadwal j WHERE j.mata_kuliah_id=mk.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT mk.id,'D','Rabu','08:00:00','10:30:00','89.03.02.C.1','Gedung Teknik' FROM mata_kuliah mk WHERE mk.kode='3TIK40033'
AND NOT EXISTS (SELECT 1 FROM jadwal j WHERE j.mata_kuliah_id=mk.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT mk.id,'D','Senin','13:00:00','15:30:00','12.02.11B.1','Gedung Teknik' FROM mata_kuliah mk WHERE mk.kode='3TIK40034'
AND NOT EXISTS (SELECT 1 FROM jadwal j WHERE j.mata_kuliah_id=mk.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT mk.id,'D','Rabu','10:30:00','13:00:00','89.03.01.B.1','Gedung Teknik' FROM mata_kuliah mk WHERE mk.kode='3TIK40035'
AND NOT EXISTS (SELECT 1 FROM jadwal j WHERE j.mata_kuliah_id=mk.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT mk.id,'D','Jumat','13:00:00','15:30:00','Lab_Komputer_A','Gedung Teknik' FROM mata_kuliah mk WHERE mk.kode='3TIK40036'
AND NOT EXISTS (SELECT 1 FROM jadwal j WHERE j.mata_kuliah_id=mk.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT mk.id,'D','Selasa','08:00:00','10:30:00','Lab_Komputer_A','Gedung Teknik' FROM mata_kuliah mk WHERE mk.kode='3TIK40037'
AND NOT EXISTS (SELECT 1 FROM jadwal j WHERE j.mata_kuliah_id=mk.id);
INSERT INTO jadwal(mata_kuliah_id,kelas,hari,jam_mulai,jam_selesai,ruang,gedung)
SELECT mk.id,'D','Senin','10:30:00','13:00:00','89.03.01.A.1','Gedung Teknik' FROM mata_kuliah mk WHERE mk.kode='3TIK40038'
AND NOT EXISTS (SELECT 1 FROM jadwal j WHERE j.mata_kuliah_id=mk.id);
