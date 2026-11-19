
<?php
require_once __DIR__.'/../includes/functions.php';
require_once __DIR__.'/../config/database.php';

require_role('admin');

function render_crud(
    string $title,
    string $table,
    string $active,
    array $fields,
    array $columns,
    string $order = '1 DESC'
): void {
    global $pdo;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verify_csrf();

        $action = $_POST['action'] ?? '';

        // HAPUS DATA
        if ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);

            try {
                $st = $pdo->prepare("DELETE FROM `$table` WHERE id=?");
                $st->execute([$id]);
                flash('success', 'Data berhasil dihapus.');
            } catch (Throwable $e) {
                flash('error', 'Data tidak dapat dihapus karena masih digunakan oleh data lain.');
            }

            redirect(basename($_SERVER['PHP_SELF']));
        }

        // TAMBAH DAN EDIT DATA
        
// TAMBAH DAN EDIT DATA

if ($action === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $values = [];

    foreach ($fields as $key => $meta) {
        $values[$key] = trim($_POST[$key] ?? '');
    }

    foreach ($fields as $key => $meta) {
        if (($meta['required'] ?? false) && $values[$key] === '') {
            flash('error', 'Mohon lengkapi semua kolom wajib.');
            redirect(basename($_SERVER['PHP_SELF']));
        }
    }

    // Setiap jadwal kuliah menggunakan sesi tetap 2 jam 30 menit.
    if ($table === 'jadwal') {
        $slotDurations = [
            '08:00:00' => '10:30:00',
            '10:30:00' => '13:00:00',
            '13:00:00' => '15:30:00',
        ];
        if (!isset($slotDurations[$values['jam_mulai'] ?? ''])) {
            flash('error', 'Pilih sesi waktu yang tersedia antara pukul 08.00 sampai 16.00.');
            redirect(basename($_SERVER['PHP_SELF']));
        }
        $values['jam_selesai'] = $slotDurations[$values['jam_mulai']];
    }

    try {
        $pdo->beginTransaction();

        if ($table === 'jadwal') {
            // Cegah kelas yang sama atau ruang yang sama dipakai bersamaan.
            $conflict = $pdo->prepare("
                SELECT id FROM jadwal
                WHERE hari = ? AND jam_mulai = ?
                  AND (kelas = ? OR ruang = ?)
                  AND id <> ?
                LIMIT 1
            ");
            $conflict->execute([
                $values['hari'],
                $values['jam_mulai'],
                $values['kelas'],
                $values['ruang'],
                $id
            ]);
            if ($conflict->fetch()) {
                throw new Exception('Jadwal bertabrakan: kelas tersebut atau ruang tersebut sudah memiliki jadwal pada hari dan sesi yang sama.');
            }
        }

        if ($table === 'mahasiswa') {
            $userId = null;
            $oldNim = null;

            if ($id > 0) {
                $check = $pdo->prepare(
                    "SELECT user_id, nim FROM mahasiswa WHERE id = ? LIMIT 1"
                );
                $check->execute([$id]);
                $existing = $check->fetch();

                if (!$existing) {
                    throw new Exception('Data mahasiswa tidak ditemukan.');
                }

                $userId = $existing['user_id'];
                $oldNim = $existing['nim'];
            }

            // Periksa username agar tidak bentrok dengan akun lain
            $checkUsername = $pdo->prepare("
                SELECT id FROM users
                WHERE username = ?
                AND (? IS NULL OR id <> ?)
                LIMIT 1
            ");
            $checkUsername->execute([
                $values['nim'],
                $userId,
                $userId
            ]);

            if ($checkUsername->fetch()) {
                throw new Exception(
                    'NIM mahasiswa sudah digunakan sebagai username.'
                );
            }

            if ($id > 0) {
                // Perbarui data mahasiswa
                $sets = implode(',', array_map(
                    fn($k) => "`$k` = ?",
                    array_keys($values)
                ));

                $vals = array_values($values);
                $vals[] = $id;

                $st = $pdo->prepare(
                    "UPDATE mahasiswa SET $sets WHERE id = ?"
                );
                $st->execute($vals);

                if ($userId) {
                    // Perbarui akun login mahasiswa
                    if ($oldNim !== $values['nim']) {
                        $st = $pdo->prepare("
                            UPDATE users
                            SET username = ?, password = ?
                            WHERE id = ?
                        ");

                        $st->execute([
                            $values['nim'],
                            password_hash($values['nim'], PASSWORD_DEFAULT),
                            $userId
                        ]);
                    } else {
                        $st = $pdo->prepare("
                            UPDATE users SET username = ? WHERE id = ?
                        ");
                        $st->execute([$values['nim'], $userId]);
                    }
                } else {
                    // Buat akun untuk mahasiswa lama yang belum punya akun
                    $st = $pdo->prepare("
                        INSERT INTO users (username, password, role)
                        VALUES (?, ?, 'mahasiswa')
                    ");
                    $st->execute([
                        $values['nim'],
                        password_hash($values['nim'], PASSWORD_DEFAULT)
                    ]);

                    $newUserId = (int)$pdo->lastInsertId();

                    $st = $pdo->prepare(
                        "UPDATE mahasiswa SET user_id = ? WHERE id = ?"
                    );
                    $st->execute([$newUserId, $id]);
                }

            } else {
                // Buat akun login otomatis
                $st = $pdo->prepare("
                    INSERT INTO users (username, password, role)
                    VALUES (?, ?, 'mahasiswa')
                ");
                $st->execute([
                    $values['nim'],
                    password_hash($values['nim'], PASSWORD_DEFAULT)
                ]);

                $newUserId = (int)$pdo->lastInsertId();

                // Hubungkan akun dengan data mahasiswa
                $values['user_id'] = $newUserId;

                $keys = array_keys($values);
                $cols = implode(',', array_map(
                    fn($k) => "`$k`",
                    $keys
                ));
                $marks = implode(',', array_fill(0, count($keys), '?'));

                $st = $pdo->prepare(
                    "INSERT INTO mahasiswa ($cols) VALUES ($marks)"
                );
                $st->execute(array_values($values));
            }

        } else {
            // CRUD tabel lainnya
            if ($id > 0) {
                $sets = implode(',', array_map(
                    fn($k) => "`$k` = ?",
                    array_keys($values)
                ));

                $vals = array_values($values);
                $vals[] = $id;

                $st = $pdo->prepare(
                    "UPDATE `$table` SET $sets WHERE id = ?"
                );
                $st->execute($vals);

            } else {
                $keys = array_keys($values);
                $cols = implode(',', array_map(
                    fn($k) => "`$k`",
                    $keys
                ));
                $marks = implode(',', array_fill(0, count($keys), '?'));

                $st = $pdo->prepare(
                    "INSERT INTO `$table` ($cols) VALUES ($marks)"
                );
                $st->execute(array_values($values));
            }
        }

        $pdo->commit();
        flash('success', 'Data berhasil disimpan.');

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        flash('error', 'Gagal menyimpan: ' . $e->getMessage());
    }

    redirect(basename($_SERVER['PHP_SELF']));
}


    }

    // AMBIL DATA
    if ($table === 'mahasiswa') {
        $rows = $pdo->query(
            "SELECT m.*, d.nama AS dosen_nama, d.nidn AS dosen_nidn
             FROM mahasiswa m
             LEFT JOIN dosen d ON d.id = m.dosen_id
             ORDER BY m.id DESC"
        )->fetchAll();
    } elseif ($table === 'jadwal') {
        $rows = $pdo->query(
            "SELECT j.*, mk.nama AS mata_kuliah_nama, mk.kode AS mata_kuliah_kode
             FROM jadwal j
             JOIN mata_kuliah mk ON mk.id = j.mata_kuliah_id
             ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat'), j.jam_mulai, j.kelas"
        )->fetchAll();
    } else {
        $rows = $pdo->query(
            "SELECT * FROM `$table` ORDER BY $order"
        )->fetchAll();
    }

    $pageTitle = $title;

    require __DIR__.'/../includes/header.php';
?>

<div class="crud-page">

    <!-- HEADER HALAMAN -->
    <div class="page-heading crud-heading">
        <div>
            <div class="eyebrow">PENGELOLAAN DATA AKADEMIK</div>
            <h1><?= e($title) ?></h1>
            <p>
                Kelola informasi akademik dengan mudah melalui sistem WarKRS.
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary crud-add-button"
            onclick="openModal('addModal')"
        >
            <span class="crud-plus">＋</span>
            Tambah Data
        </button>
    </div>

    <!-- RINGKASAN DATA -->
    <div class="crud-summary">
        <div class="crud-summary-icon">
            <span>▤</span>
        </div>

        <div>
            <span class="crud-summary-label">Total Data</span>
            <strong><?= count($rows) ?></strong>
            <small>Data tersimpan dalam sistem</small>
        </div>
    </div>

    <!-- TABEL DATA -->
    <div class="panel table-panel crud-table-card">

        <div class="table-toolbar crud-toolbar">
            <div>
                <h3>Daftar <?= e($title) ?></h3>
                <p>Informasi data yang telah terdaftar di WarKRS.</p>
            </div>

            <div class="crud-search-wrapper">
                <span class="crud-search-icon">⌕</span>
                <input
                    type="text"
                    class="search-input crud-search"
                    placeholder="Cari data..."
                    oninput="filterTable(this,'dataTable')"
                >
            </div>
        </div>

        <div class="table-scroll">
            <table id="dataTable" class="crud-table">
                <thead>
                    <tr>
                        <?php foreach ($columns as $label => $key): ?>
                            <th><?= e($label) ?></th>
                        <?php endforeach; ?>
                        <th class="crud-action-heading">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($columns as $key): ?>
                                <td>
                                    <?php if ($table === 'jadwal' && $key === 'mata_kuliah_nama'): ?>
                                        <b><?= e($row['mata_kuliah_kode'] . ' — ' . $row['mata_kuliah_nama']) ?></b>
                                    <?php elseif ($table === 'jadwal' && in_array($key, ['jam_mulai', 'jam_selesai'], true)): ?>
                                        <?= e(substr((string)($row[$key] ?? ''), 0, 5)) ?>
                                    <?php else: ?>
                                        <?= e((string)($key === 'dosen_id' && $table === 'mahasiswa' ? (($row['dosen_nama'] ?? 'Belum dipilih') . (empty($row['dosen_nidn']) ? '' : ' — ' . $row['dosen_nidn'])) : ($row[$key] ?? ''))) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>

                            <td class="actions crud-actions">
                                <button
                                    type="button"
                                    class="btn-icon crud-edit-button"
                                    title="Edit data"
                                    onclick='editRow(
                                        <?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                                        <?= json_encode(array_keys($fields)) ?>
                                    )'
                                >
                                    ✎
                                </button>

                                <form
                                    method="post"
                                    class="crud-delete-form"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf"
                                        value="<?= e(csrf_token()) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$row['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="btn-icon danger crud-delete-button"
                                        title="Hapus data"
                                    >
                                        ⌫
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$rows): ?>
                        <tr>
                            <td
                                colspan="<?= count($columns) + 1 ?>"
                                class="empty crud-empty"
                            >
                                <div class="crud-empty-icon">▤</div>
                                <strong>Belum ada data</strong>
                                <p>Silakan tambahkan data baru untuk memulai.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="crud-table-footer">
            Menampilkan <?= count($rows) ?> data
        </div>
    </div>
</div>

<!-- MODAL TAMBAH DAN EDIT -->
<div class="modal-backdrop" id="addModal">
    <div class="modal crud-modal">

        <div class="modal-head crud-modal-head">
            <div>
                <div class="eyebrow">FORMULIR WAR KRS</div>
                <h3 id="modalTitle">Tambah <?= e($title) ?></h3>
                <p>Lengkapi informasi pada kolom berikut.</p>
            </div>

            <button
                type="button"
                class="btn-icon"
                onclick="closeModal('addModal')"
                title="Tutup"
            >
                ✕
            </button>
        </div>

        <form method="post" id="crudForm" class="crud-form">

            <input
                type="hidden"
                name="csrf"
                value="<?= e(csrf_token()) ?>"
            >

            <input type="hidden" name="action" value="save">

            <input
                type="hidden"
                name="id"
                id="rowId"
                value="0"
            >

            <div class="crud-form-grid">
                <?php foreach ($fields as $key => $meta): ?>
                    <div class="crud-field-group">

                        <label for="field_<?= e($key) ?>">
                            <?= e($meta['label']) ?>

                            <?php if ($meta['required'] ?? false): ?>
                                <span class="crud-required">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if (($meta['type'] ?? 'text') === 'select'): ?>

                            <select
                                class="field"
                                id="field_<?= e($key) ?>"
                                name="<?= e($key) ?>"
                                <?= ($meta['required'] ?? false) ? 'required' : '' ?>
                            >
                                <?php foreach ($meta['options'] as $v => $lab): ?>
                                    <option value="<?= e((string)$v) ?>">
                                        <?= e($lab) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php else: ?>

                            <input
                                class="field"
                                id="field_<?= e($key) ?>"
                                type="<?= e($meta['type'] ?? 'text') ?>"
                                name="<?= e($key) ?>"
                                placeholder="Masukkan <?= e(strtolower($meta['label'])) ?>"
                                <?= ($meta['required'] ?? false) ? 'required' : '' ?>
                            >

                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>

            <div class="modal-actions crud-modal-actions">
                <button
                    type="button"
                    class="btn btn-ghost"
                    onclick="closeModal('addModal')"
                >
                    Batal
                </button>

                <button type="submit" class="btn btn-primary">
                    Simpan Data
                </button>
            </div>

        </form>
    </div>
</div>

<?php
    require __DIR__.'/../includes/footer.php';
}
