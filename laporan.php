<?php
session_start();
include 'koneksi.php';
include 'functions.php';
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }

// Hanya admin & superadmin yang bisa akses
$role     = $_SESSION['role'] ?? 'petugas';
$is_admin = in_array($role, ['admin', 'superadmin']);
if (!$is_admin) { header("Location: dashboard.php?error=forbidden"); exit; }

// Ambil data peminjaman
$result = mysqli_query($conn, "
    SELECT p.id, b.judul, b.penulis, u.nama, u.username,
           p.tanggal_pinjam, p.tanggal_kembali, p.denda
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id
    JOIN users u ON p.id_user = u.id
    ORDER BY p.id DESC
");
$data  = [];
while ($row = mysqli_fetch_assoc($result)) { $data[] = $row; }
$total = count($data);

// Ambil data buku
$buku_result = mysqli_query($conn, "SELECT * FROM buku ORDER BY id DESC");
$data_buku   = [];
while ($row = mysqli_fetch_assoc($buku_result)) { $data_buku[] = $row; }
$total_buku  = count($data_buku);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h2>Laporan</h2>
        <p>Export data peminjaman dan buku</p>
      </div>
      <div class="topbar-right">
        <div class="topbar-user">
          <div class="avatar"><?= strtoupper(substr($_SESSION['username']??'A',0,1)) ?></div>
          <?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? 'Admin') ?>
        </div>
      </div>
    </div>

    <div class="page-content">
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span class="sep">/</span>
        <span>Laporan</span>
      </div>

      <!-- TOMBOL EXPORT -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
        <div class="card">
          <div class="card-header"><h3>📋 Export Data Peminjaman</h3></div>
          <div class="card-body">
            <p class="text-muted" style="margin-bottom:16px;">Total <strong><?= $total ?></strong> data peminjaman tersedia.</p>
            <div style="display:flex; gap:10px;">
              <a href="export_peminjaman_excel.php" class="btn btn-primary">📊 Export Excel</a>
              <a href="export_peminjaman_pdf.php" class="btn btn-outline">📄 Export PDF</a>
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><h3>📚 Export Data Buku</h3></div>
          <div class="card-body">
            <p class="text-muted" style="margin-bottom:16px;">Total <strong><?= $total_buku ?></strong> data buku tersedia.</p>
            <div style="display:flex; gap:10px;">
              <a href="export_buku_excel.php" class="btn btn-primary">📊 Export Excel</a>
              <a href="export_buku_pdf.php" class="btn btn-outline">📄 Export PDF</a>
            </div>
          </div>
        </div>
      </div>

      <!-- PREVIEW DATA PEMINJAMAN -->
      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <h3>📋 Preview Data Peminjaman <span class="badge badge-blue" style="margin-left:8px;"><?= $total ?> data</span></h3>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Judul Buku</th><th>Penulis</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Tgl Kembali</th><th>Denda</th></tr>
            </thead>
            <tbody>
              <?php if ($total === 0): ?>
              <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📭</div><p>Belum ada data peminjaman.</p></div></td></tr>
              <?php else: $no=1; foreach ($data as $d): ?>
              <tr>
                <td class="text-muted"><?= $no++ ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($d['judul']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($d['penulis']) ?></td>
                <td><?= htmlspecialchars($d['nama']) ?></td>
                <td><?= htmlspecialchars($d['tanggal_pinjam']) ?></td>
                <td><?= $d['tanggal_kembali'] ? htmlspecialchars($d['tanggal_kembali']) : '<span class="text-muted">—</span>' ?></td>
                <td>
                  <?php if ((int)$d['denda'] > 0): ?>
                    <span class="badge badge-red">Rp <?= number_format($d['denda'],0,',','.') ?></span>
                  <?php else: ?>
                    <span class="badge badge-green">Tidak ada</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- PREVIEW DATA BUKU -->
      <div class="card">
        <div class="card-header">
          <h3>📚 Preview Data Buku <span class="badge badge-blue" style="margin-left:8px;"><?= $total_buku ?> buku</span></h3>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>#</th><th>Judul Buku</th><th>Penulis</th><th>Stok</th></tr>
            </thead>
            <tbody>
              <?php if ($total_buku === 0): ?>
              <tr><td colspan="4"><div class="empty-state"><div class="empty-icon">📭</div><p>Belum ada data buku.</p></div></td></tr>
              <?php else: $no=1; foreach ($data_buku as $d): ?>
              <tr>
                <td class="text-muted"><?= $no++ ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($d['judul']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($d['penulis']) ?></td>
                <td>
                  <?php $s = (int)$d['stok'];
                  if ($s === 0) echo '<span class="badge badge-red">Habis</span>';
                  elseif ($s <= 2) echo '<span class="badge badge-amber">'.$s.' tersisa</span>';
                  else echo '<span class="badge badge-green">'.$s.' stok</span>'; ?>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>