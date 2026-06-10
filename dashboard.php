<?php
// ✅ BAGIAN INI — pastikan ada include functions.php
session_start();
include 'koneksi.php';
include 'functions.php'; // ← TAMBAH INI
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

$total_buku    = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM buku"));
$total_pinjam  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM peminjaman WHERE tanggal_kembali IS NULL"));
$total_kembali = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM peminjaman WHERE tanggal_kembali IS NOT NULL"));
$total_user    = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM users"));
$pinjam_terbaru = mysqli_query($conn,"SELECT p.*,b.judul,b.penulis FROM peminjaman p JOIN buku b ON p.id_buku=b.id ORDER BY p.id DESC LIMIT 8");
$stok_rendah    = mysqli_query($conn,"SELECT * FROM buku WHERE stok <= 2 ORDER BY stok ASC LIMIT 5");

// ✅ BAGIAN INI — ambil data notifikasi (wajib ada sebelum include sidebar)
$notif_list  = get_notifikasi($conn, 8);
$notif_count = count_notifikasi_unread($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app">
  <?php include 'sidebar.php'; ?>

  <div class="main">

    <!-- ✅ TOPBAR — ini bagian yang diupdate -->
    <div class="topbar">
      <div class="topbar-left">
        <h2>Dashboard</h2>
        <p><?= date('l, d F Y') ?></p>
      </div>

      <!-- ✅ GANTI topbar-right dengan ini -->
      <div class="topbar-right">
        <form action="buku.php" method="GET" class="search-wrap">
          <span class="search-icon">🔍</span>
          <input type="text" name="cari" class="search-input" placeholder="Cari buku...">
        </form>

        <!-- Dark mode toggle -->
        <div class="dark-toggle" onclick="toggleDarkMode()" title="Toggle dark mode">
          <span id="darkIcon"><?= ($_SESSION['dark_mode']??0) ? '☀️' : '🌙' ?></span>
        </div>

        <!-- Notifikasi -->
        <div class="notif-wrap">
          <div class="notif-btn" onclick="toggleNotif(event)" title="Notifikasi">
            🔔
            <?php if($notif_count > 0): ?>
            <div class="notif-badge" id="notifBadge">
              <?= $notif_count > 9 ? '9+' : $notif_count ?>
            </div>
            <?php endif; ?>
          </div>
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header">
              <h4>🔔 Notifikasi</h4>
              <button class="notif-read-all" onclick="tandaiDibaca()">Tandai semua dibaca</button>
            </div>
            <div class="notif-list">
              <?php if(empty($notif_list)): ?>
              <div class="notif-empty">✅ Tidak ada notifikasi</div>
              <?php else: ?>
              <?php foreach($notif_list as $n): ?>
              <div class="notif-item <?= !$n['sudah_dibaca']?'unread':'' ?>">
                <div class="notif-dot-wrap">
                  <div class="notif-dot <?= $n['tipe'] ?>"></div>
                </div>
                <div class="notif-content">
                  <p><?= htmlspecialchars($n['judul']) ?></p>
                  <span><?= htmlspecialchars($n['pesan']) ?></span><br>
                  <span style="font-size:10px;color:#94a3b8;margin-top:2px;display:block;">
                    <?= date('d M Y H:i', strtotime($n['created_at'])) ?>
                  </span>
                </div>
              </div>
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
            <div class="notif-footer">
              <a href="notifikasi.php">Lihat semua →</a>
            </div>
          </div>
        </div>

        <!-- User info -->
        <div class="topbar-user">
          <div class="avatar"><?= strtoupper(substr($_SESSION['username']??'A',0,1)) ?></div>
          <?= htmlspecialchars($_SESSION['nama']??'Admin') ?>
        </div>
      </div>
      <!-- ✅ SELESAI topbar-right -->

    </div>
    <!-- SELESAI TOPBAR -->

    <div class="page-content">
      <!-- Banner -->
      <div style="background:linear-gradient(135deg,#1d4ed8,#2563eb);border-radius:16px;padding:24px 28px;margin-bottom:24px;color:#fff;display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h2 style="font-size:20px;font-weight:700;margin-bottom:4px;">Halo, <?= htmlspecialchars($_SESSION['nama']??'Admin') ?>! 👋</h2>
          <p style="font-size:13px;opacity:.85;">Selamat datang di Sistem Perpustakaan Kampus</p>
        </div>
        <div style="font-size:64px;opacity:.2;">📚</div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">📚</div>
          <div class="stat-info"><h3><?= $total_buku ?></h3><p>Total Buku</p></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon amber">📖</div>
          <div class="stat-info"><h3><?= $total_pinjam ?></h3><p>Sedang Dipinjam</p></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">✅</div>
          <div class="stat-info"><h3><?= $total_kembali ?></h3><p>Sudah Kembali</p></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pink">👥</div>
          <div class="stat-info"><h3><?= $total_user ?></h3><p>Total User</p></div>
        </div>
      </div>

      <!-- Tabel + Stok Rendah -->
      <div style="display:grid;grid-template-columns:1fr 340px;gap:16px;">
        <div class="card">
          <div class="card-header">
            <h3>📋 Peminjaman Terbaru</h3>
            <a href="pinjam.php" class="btn btn-outline btn-sm">Lihat Semua</a>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Buku</th><th>Penulis</th><th>Tgl Pinjam</th><th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if(mysqli_num_rows($pinjam_terbaru)===0): ?>
                <tr><td colspan="4"><div class="empty-state"><p>Belum ada peminjaman</p></div></td></tr>
                <?php else: while($p=mysqli_fetch_assoc($pinjam_terbaru)): ?>
                <tr>
                  <td style="font-weight:600;"><?= htmlspecialchars($p['judul']) ?></td>
                  <td class="text-muted"><?= htmlspecialchars($p['penulis']) ?></td>
                  <td class="text-muted"><?= format_tgl($p['tanggal_pinjam']) ?></td>
                  <td>
                    <?php if(is_null($p['tanggal_kembali'])): ?>
                    <span class="badge badge-amber">Dipinjam</span>
                    <?php else: ?>
                    <span class="badge badge-green">Dikembalikan</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endwhile; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3>⚠️ Stok Rendah</h3>
            <a href="buku.php" class="btn btn-outline btn-sm">Kelola</a>
          </div>
          <div style="padding:0;">
            <?php
            $rows=[];
            while($b=mysqli_fetch_assoc($stok_rendah)) $rows[]=$b;
            if(empty($rows)):
            ?>
            <div class="empty-state" style="padding:32px;">
              <div class="empty-icon">✅</div>
              <p>Semua stok aman</p>
            </div>
            <?php else: foreach($rows as $b): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid #f1f5f9;">
              <div>
                <p style="font-weight:600;font-size:13px;"><?= htmlspecialchars($b['judul']) ?></p>
                <p class="text-muted text-sm"><?= htmlspecialchars($b['penulis']) ?></p>
              </div>
              <span class="badge <?= $b['stok']==0?'badge-red':'badge-amber' ?>"><?= $b['stok'] ?> stok</span>
            </div>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

</body>
</html>