# WarKRS — Sistem Informasi Pengelolaan Kartu Rencana Studi

## Teknologi
PHP 8+, MySQL/MariaDB, HTML, CSS, JavaScript, XAMPP.

## Instalasi awal
1. Salin folder `WarKRS` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL melalui XAMPP.
3. Buka phpMyAdmin dan impor `database/warkrs.sql` ke database `warkrs`.
4. Buka `http://localhost/WarKRS/install_admin.php` satu kali untuk membuat akun administrator utama dan membersihkan akun demo lama jika masih ada.
5. Setelah muncul pesan berhasil, HAPUS file `install_admin.php` dari folder WarKRS. Jangan biarkan file pemasang dapat diakses publik.
6. Buka `http://localhost/WarKRS/login.php`.

## Akun administrator utama
Username: `UNDEAD KING`
Password: `THE KILLER MAN`

Username admin disimpan tanpa spasi di awal/akhir. Password disimpan menggunakan `password_hash()` dan tidak ditampilkan di halaman login. Jangan bagikan kredensial ini.

## Pengaturan jadwal kuliah
- Jam operasional jadwal adalah 08.00–16.00 dengan tiga sesi tetap: 08.00–10.30, 10.30–13.00, dan 13.00–15.30.
- Setiap sesi berlangsung 2 jam 30 menit; jam selesai dihitung otomatis oleh sistem.
- Pilihan kelas adalah A, B, C, dan D. Sistem mencegah kelas yang sama atau ruang yang sama dijadwalkan bersamaan pada hari dan sesi yang sama.
- Daftar 19 mata kuliah dari gambar pengguna sudah disediakan pada `database/warkrs.sql`. Sembilan mata kuliah yang memiliki jadwal pada gambar juga diberi jadwal awal yang disesuaikan ke sesi 2 jam 30 menit.
- Jika menggunakan database lama, backup terlebih dahulu lalu impor `database/update_katalog_jadwal.sql` satu kali. Jika database baru, cukup impor `database/warkrs.sql`.
- Pada 10 mata kuliah di gambar kedua, SKS tidak terlihat; sistem memberi nilai sementara 2 SKS dan semester 4 agar bisa tampil di katalog. Admin perlu memeriksa dan mengubah SKS/semester melalui menu Mata Kuliah sesuai data akademik yang benar.

## Registrasi
- Mahasiswa: `http://localhost/WarKRS/register.php` — login menggunakan NIM dan password yang dibuat saat pendaftaran. Pilihan angkatan 2022–2027, tiga program studi sesuai permintaan, dan dosen wali yang sudah terdaftar.
- Dosen: `http://localhost/WarKRS/register_dosen.php` — login menggunakan NIDN dan password yang dibuat saat pendaftaran.
- Tidak ada akun demo maupun informasi kredensial demo pada halaman login. Tidak ada tautan pendaftaran admin. Akun admin hanya dibuat melalui langkah pemasangan di atas.

## Alur sistem
- Registrasi mahasiswa membuat akun `users` dan profil `mahasiswa` dalam satu transaksi MySQL serta menyimpan `dosen_id`.
- Registrasi dosen membuat akun `users` dan profil `dosen` dalam satu transaksi. Dosen langsung muncul pada Data Dosen Admin dan pilihan dosen wali mahasiswa.
- Dashboard mahasiswa menampilkan dosen wali dan katalog mata kuliah yang didaftarkan admin.
- Dashboard dosen menampilkan mahasiswa yang memilihnya; halaman Persetujuan KRS menyediakan tombol Setujui/Tolak untuk mahasiswa bimbingannya.
- Dashboard admin berada di `index.php`.

## Catatan
Aplikasi ini prototipe akademik lokal, bukan sistem resmi UNIMED. Ganti kredensial database pada `config/database.php` bila konfigurasi MySQL berbeda.
