<?php
$user = current_user();
$pageTitle = $pageTitle ?? 'Dashboard';
$active = $active ?? '';
$flashMessage = get_flash();

$baseUrl = '/WarKRS/';
$role = $user['role'] ?? '';
$dashboardUrl = $role === 'mahasiswa'
    ? $baseUrl . 'mahasiswa/index.php'
    : ($role === 'dosen' ? $baseUrl . 'dosen/index.php' : $baseUrl . 'index.php');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · WarKRS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/WarKRS/assets/css/style.css">
</head>
<body>
<div class="app-shell">
<aside class="sidebar" id="sidebar">
  <a class="brand" href="<?= e($dashboardUrl) ?>"><span class="brand-mark">W</span><span>War<span class="brand-light">KRS</span><small>Academic workspace</small></span></a>
  
<div class="side-label">MENU UTAMA</div>

<nav class="side-nav">

    <a class="<?= $active === 'dashboard' ? 'active' : '' ?>"
       href="<?= e($dashboardUrl) ?>">
        <span>▦</span> Dashboard
    </a>

    <?php if ($user && $role === 'admin'): ?>

        <div class="side-label">MANAJEMEN DATA</div>

        <a class="<?= $active === 'mahasiswa' ? 'active' : '' ?>"
           href="<?= $baseUrl ?>admin/mahasiswa.php">
            <span>♙</span> Data Mahasiswa
        </a>

        <a class="<?= $active === 'dosen' ? 'active' : '' ?>"
           href="<?= $baseUrl ?>admin/dosen.php">
            <span>♧</span> Data Dosen
        </a>

        <a class="<?= $active === 'matakuliah' ? 'active' : '' ?>"
           href="<?= $baseUrl ?>admin/matakuliah.php">
            <span>▤</span> Mata Kuliah
        </a>

        <a class="<?= $active === 'jadwal' ? 'active' : '' ?>"
           href="<?= $baseUrl ?>admin/jadwal.php">
            <span>◷</span> Jadwal Kuliah
        </a>

    <?php elseif ($user && $role === 'mahasiswa'): ?>

        <div class="side-label">AKADEMIK</div>

        <a class="<?= $active === 'katalog' ? 'active' : '' ?>"
           href="<?= $baseUrl ?>mahasiswa/katalog.php">
            <span>▤</span> Katalog Mata Kuliah
        </a>

        <a class="<?= $active === 'krs' ? 'active' : '' ?>"
           href="<?= $baseUrl ?>mahasiswa/krs.php">
            <span>▣</span> KRS Saya
        </a>

    <?php elseif ($user && $role === 'dosen'): ?>

        <div class="side-label">PERWALIAN</div>

        <a class="<?= $active === 'persetujuan' ? 'active' : '' ?>"
           href="<?= $baseUrl ?>dosen/persetujuan.php">
            <span>✓</span> Persetujuan KRS
        </a>

    <?php endif; ?>

</nav>
  <div class="sidebar-bottom"><div class="help-card"><div class="help-icon">✦</div><b>Butuh bantuan?</b><p>WarKRS versi prototipe untuk simulasi akademik.</p></div><div class="side-foot">© 2026 WarKRS · UNIMED</div></div>
</aside>
<main class="main-area">
<header class="topbar">
  <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')">☰</button>
  <div class="crumb">Workspace <span>/</span> <b><?= e($pageTitle) ?></b></div>
  <div class="top-user"><div class="avatar"><?= e(strtoupper(substr($user['name'] ?? 'W',0,1))) ?></div><div><b><?= e($user['name'] ?? 'Pengunjung') ?></b><small><?= e(role_label($user['role'] ?? '')) ?></small></div><a class="logout-link" href="<?= $baseUrl ?>logout.php" title="Keluar">↗</a></div>
</header>
<section class="content">
<?php if ($flashMessage): ?>
<div class="alert <?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div>
<?php endif; ?>
