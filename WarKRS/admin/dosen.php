
<?php

require __DIR__ . '/_crud.php';

render_crud(
    'Data Dosen',
    'dosen',
    'dosen',
    [
        'nidn' => [
            'label' => 'NIDN',
            'required' => true
        ],

        'nama' => [
            'label' => 'Nama Dosen',
            'required' => true
        ]
    ],
    [
        'NIDN' => 'nidn',
        'Nama Dosen' => 'nama'
    ]
);
