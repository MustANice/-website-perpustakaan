<?php
session_start();
include 'koneksi.php';
include 'functions.php';
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

$msg      = '';
$msg_type = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'returned') {
    $msg      = "✅ Buku berhasil dikembalikan!";
    $msg_type = "success";
}

define('BATAS_HARI', 30);
define('DENDA_PER_HARI', 2000);

$data        = mysqli_query($conn, "
    SELECT p.*, b.judul, b.penulis
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id
    WHERE p.tanggal_kembali IS NULL
    ORDER BY p.tanggal_pinjam ASC
");
$total_belum = mysqli_num_rows($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengembalian — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h2>Pengembalian Buku</h2>
        <p>Proses pengembalian buku dipinjam</p>
      </div>
      <div class="topbar-right">
        <div class="topbar-user">
          <div class="avatar"><?= strtoupper(substr($_SESSION['username']??'A',0,1)) ?></div>
          <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
        </div>
      </div>
    </div>

    <div class="page-content">
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span class="sep">/</span>
        <span>Pengembalian</span>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg_type) ?>">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <h3>🔄 Buku Belum Dikembalikan
            <span class="badge badge-amber" style="margin-left:8px;"><?= $total_belum ?></span>
          </h3>
          <a href="pinjam.php" class="btn btn-outline btn-sm">+ Catat Pinjam</a>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Judul Buku</th>
                <th>Penulis</th>
                <th>Tgl Pinjam</th>
                <th>Batas Kembali</th>
                <th>Status</th>
                <th>Est. Denda</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($total_belum === 0): ?>
              <tr>
                <td colspan="8">
                  <div class="empty-state" style="padding:48px;">
                    <div class="empty-icon">✅</div>
                    <p>Semua buku sudah dikembalikan!</p>
                  </div>
                </td>
              </tr>
              <?php else: $no = 1; while ($d = mysqli_fetch_assoc($data)):
                $tgl_pinjam  = strtotime($d['tanggal_pinjam']);
                $tgl_batas   = strtotime('+' . BATAS_HARI . ' days', $tgl_pinjam);
                $hari_pinjam = (int)floor((time() - $tgl_pinjam) / 86400);
                $sisa_hari   = (int)ceil(($tgl_batas - time()) / 86400);
                $terlambat   = max(0, $hari_pinjam - BATAS_HARI);
                $denda       = $terlambat * DENDA_PER_HARI;
              ?>
              <tr>
                <td class="text-muted"><?= $no++ ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($d['judul']) ?></td>
                <td class="text-muted"><?= htmlspecialchars($d['penulis']) ?></td>
                <td class="text-muted"><?= date('d M Y', $tgl_pinjam) ?></td>
                <td class="text-muted"><?= date('d M Y', $tgl_batas) ?></td>
                <td>
                  <?php if ($terlambat > 0): ?>
                    <span class="badge badge-red">Terlambat <?= $terlambat ?> hari</span>
                  <?php elseif ($sisa_hari <= 3): ?>
                    <span class="badge badge-amber">Segera kembali (<?= $sisa_hari ?> hari)</span>
                  <?php else: ?>
                    <span class="badge badge-green">Tepat waktu (sisa <?= $sisa_hari ?> hari)</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($denda > 0): ?>
                    <span style="color:#dc2626; font-weight:700;">Rp <?= number_format($denda, 0, ',', '.') ?></span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="proses_kembali.php?id=<?= (int)$d['id'] ?>&buku=<?= (int)$d['id_buku'] ?>"
                     class="btn btn-success btn-sm"
                     onclick="return confirm('Proses pengembalian buku ini?')">
                    ✅ Kembalikan
                  </a>
                </td>
              </tr>
              <?php endwhile; endif; ?>
            </tbody>
          </table>
        </div>

        <?php if ($total_belum > 0): ?>
        <div style="padding:14px 18px; border-top:1px solid #f1f5f9; font-size:12.5px; color:var(--text-muted);">
          💡 Batas peminjaman: <strong><?= BATAS_HARI ?> hari</strong> · Denda keterlambatan: <strong>Rp <?= number_format(DENDA_PER_HARI,0,',','.') ?>/hari</strong>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>
</body>
</html>