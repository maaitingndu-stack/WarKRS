<?php
require __DIR__ . '/_crud.php';

// Ambil daftar mata kuliah agar admin memilih nama, bukan mengetik ID manual.
$mataKuliahOptions = ['' => 'Pilih mata kuliah'];
$daftarMataKuliah = $pdo->query("SELECT id, kode, nama FROM mata_kuliah ORDER BY semester, kode")->fetchAll();
foreach ($daftarMataKuliah as $mk) {
    $mataKuliahOptions[$mk['id']] = $mk['kode'] . ' — ' . $mk['nama'];
}

$slotWaktu = [
    '08:00:00' => '08.00 – 10.30',
    '10:30:00' => '10.30 – 13.00',
    '13:00:00' => '13.00 – 15.30',
];

render_crud(
    'Jadwal Kuliah',
    'jadwal',
    'jadwal',
    [
        'mata_kuliah_id' => [
            'label' => 'Mata Kuliah',
            'type' => 'select',
            'options' => $mataKuliahOptions,
            'required' => true
        ],
        'kelas' => [
            'label' => 'Kelas',
            'type' => 'select',
            'options' => ['' => 'Pilih kelas', 'A' => 'Kelas A', 'B' => 'Kelas B', 'C' => 'Kelas C', 'D' => 'Kelas D'],
            'required' => true
        ],
        'hari' => [
            'label' => 'Hari',
            'type' => 'select',
            'options' => ['' => 'Pilih hari', 'Senin' => 'Senin', 'Selasa' => 'Selasa', 'Rabu' => 'Rabu', 'Kamis' => 'Kamis', 'Jumat' => 'Jumat'],
            'required' => true
        ],
        'jam_mulai' => [
            'label' => 'Sesi Waktu',
            'type' => 'select',
            'options' => ['' => 'Pilih sesi'] + $slotWaktu,
            'required' => true
        ],
        'ruang' => [
            'label' => 'Ruang',
            'required' => true
        ],
        'gedung' => [
            'label' => 'Gedung',
            'required' => true
        ]
    ],
    [
        'Mata Kuliah' => 'mata_kuliah_nama',
        'Kelas' => 'kelas',
        'Hari' => 'hari',
        'Mulai' => 'jam_mulai',
        'Selesai' => 'jam_selesai',
        'Ruang' => 'ruang',
        'Gedung' => 'gedung'
    ]
);
