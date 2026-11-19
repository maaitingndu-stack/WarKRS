
<?php

require __DIR__ . '/_crud.php';

$dosenOptions = [
    '' => 'Pilih dosen wali'
];

$daftarDosen = $pdo->query(
    "SELECT id, nidn, nama FROM dosen ORDER BY nama ASC"
)->fetchAll();

foreach ($daftarDosen as $dosen) {
    $dosenOptions[$dosen['id']] =
        $dosen['nama'] . ' — NIDN ' . $dosen['nidn'];
}

render_crud(
    'Data Mahasiswa',
    'mahasiswa',
    'mahasiswa',
    [
        'nim' => [
            'label' => 'NIM',
            'required' => true
        ],

        'nama' => [
            'label' => 'Nama Mahasiswa',
            'required' => true
        ],

        'prodi' => [
            'label' => 'Program Studi',
            'required' => true
        ],

        'angkatan' => [
            'label' => 'Angkatan',
            'type' => 'number',
            'required' => true
        ],

        'dosen_id' => [
            'label' => 'Dosen Wali',
            'type' => 'select',
            'options' => $dosenOptions,
            'required' => true
        ]
    ],
    [
        'NIM' => 'nim',
        'Nama' => 'nama',
        'Program Studi' => 'prodi',
        'Angkatan' => 'angkatan',
        'ID Dosen' => 'dosen_id'
    ]
);
