-- Jalankan SATU KALI pada database warkrs yang sudah digunakan.
-- Menambahkan pilihan kelas A-D dan menyesuaikan jadwal lama ke sesi 2 jam 30 menit.
USE warkrs;

ALTER TABLE jadwal
    ADD COLUMN kelas ENUM('A','B','C','D') NOT NULL DEFAULT 'A' AFTER mata_kuliah_id;

-- Memetakan jadwal lama ke salah satu sesi yang tersedia.
UPDATE jadwal
SET jam_mulai = CASE
    WHEN jam_mulai < '10:30:00' THEN '08:00:00'
    WHEN jam_mulai < '13:00:00' THEN '10:30:00'
    ELSE '13:00:00'
END;

UPDATE jadwal
SET jam_selesai = ADDTIME(jam_mulai, '02:30:00');
