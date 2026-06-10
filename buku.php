<?php
session_start();
include 'koneksi.php';
include 'functions.php';
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

// CEK ROLE
$role     = $_SESSION['role'] ?? 'user';
$is_admin = in_array($role, ['admin', 'superadmin']);

$msg = '';
$msg_type = '';

// TAMBAH BUKU — hanya admin
if(isset($_POST['tambah'])){
    if(!$is_admin){
        header("Location: buku.php?error=forbidden"); exit;
    }
    $judul   = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $stok    = (int)$_POST['stok'];

    if($judul && $penulis && $stok >= 0){
        $stmt = $conn->prepare("INSERT INTO buku (judul, penulis, stok) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $judul, $penulis, $stok);
        if ($stmt->execute()) {
    $msg = "✅ Buku berhasil ditambahkan!";
    $msg_type = "success";
} else {
    $msg = "❌ Gagal menambahkan buku.";
    $msg_type = "danger";
}
    } else {
        $msg = "⚠️ Semua kolom wajib diisi.";
        $msg_type = "warning";
    }
}

// PESAN DARI REDIRECT
if(isset($_GET['msg'])){
    $map = [
        'updated'        => ['✅ Buku berhasil diupdate!', 'success'],
        'deleted'        => ['✅ Buku berhasil dihapus!', 'success'],
        'cannot_delete'  => ['❌ Buku masih dipinjam, tidak bisa dihapus!', 'danger'],
    ];
    if(isset($map[$_GET['msg']])){
        [$msg, $msg_type] = $map[$_GET['msg']];
    }
}

// PESAN FORBIDDEN
if(isset($_GET['error']) && $_GET['error'] === 'forbidden'){
    $msg = "🚫 Akses ditolak! Hanya Admin yang bisa melakukan tindakan ini.";
    $msg_type = "danger";
}

// AMBIL DATA BUKU
$cari = trim($_GET['cari'] ?? '');
if($cari !== ''){
    $stmt   = $conn->prepare("SELECT * FROM buku WHERE judul LIKE ? OR penulis LIKE ? ORDER BY id DESC");
    $search = "%$cari%";
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $data = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM buku ORDER BY id DESC");
    $stmt->execute();
    $data = $stmt->get_result();
}

$total       = $data->num_rows;
$notif_list  = get_notifikasi($conn, 8);
$notif_count = count_notifikasi_unread($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Data Buku — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>

  <div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-left">
        <h2>Data Buku</h2>
        <p>Kelola koleksi buku perpustakaan</p>
      </div>
      <div class="topbar-right">

        <!-- Dark mode -->
        <div class="dark-toggle" onclick="toggleDarkMode()" title="Toggle dark mode">
          <span id="darkIcon"><?= ($_SESSION['dark_mode']??0) ? '☀️' : '🌙' ?></span>
        </div>

        <!-- Notifikasi -->
        <div class="notif-wrap">
          <div class="notif-btn" onclick="toggleNotif(event)">
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
              <button class="notif-read-all" onclick="tandaiDibaca()">Tandai dibaca</button>
            </div>
            <div class="notif-list">
              <?php if(empty($notif_list)): ?>
              <div class="notif-empty">✅ Tidak ada notifikasi</div>
              <?php else: foreach($notif_list as $n): ?>
              <div class="notif-item <?= !$n['sudah_dibaca']?'unread':'' ?>">
                <div class="notif-dot-wrap">
                  <div class="notif-dot <?= $n['tipe'] ?>"></div>
                </div>
                <div class="notif-content">
                  <p><?= htmlspecialchars($n['judul']) ?></p>
                  <span><?= htmlspecialchars($n['pesan']) ?></span><br>
                  <span style="font-size:10px;color:#94a3b8;">
                    <?= date('d M Y H:i', strtotime($n['created_at'])) ?>
                  </span>
                </div>
              </div>
              <?php endforeach; endif; ?>
            </div>
            <div class="notif-footer">
              <a href="notifikasi.php">Lihat semua →</a>
            </div>
          </div>
        </div>

        <!-- User -->
        <div class="topbar-user">
          <div class="avatar">
            <?= strtoupper(substr($_SESSION['username']??'A',0,1)) ?>
          </div>
          <?= htmlspecialchars($_SESSION['nama']??'Admin') ?>
        </div>

      </div>
    </div>

    <div class="page-content">
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span class="sep">/</span>
        <span>Data Buku</span>
      </div>

      <?php if($msg): ?>
      <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
      <?php endif; ?>

      <!-- INFO ROLE untuk user biasa -->
      <?php if(!$is_admin): ?>
      <div class="alert alert-info">
        ℹ️ Kamu login sebagai <strong><?= ucfirst($role) ?></strong>.
        Hanya dapat melihat daftar buku. Untuk mengelola buku hubungi Admin.
      </div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:<?= $is_admin ? '340px 1fr' : '1fr' ?>;gap:20px;align-items:start;">

        <!-- FORM TAMBAH — hanya tampil untuk admin -->
        <?php if($is_admin): ?>
        <div class="card">
          <div class="card-header"><h3>➕ Tambah Buku Baru</h3></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label class="form-label">Judul Buku</label>
                <input type="text" name="judul" class="form-control"
                  placeholder="Masukkan judul" maxlength="255" required>
              </div>
              <div class="form-group">
                <label class="form-label">Penulis</label>
                <input type="text" name="penulis" class="form-control"
                  placeholder="Nama penulis" maxlength="255" required>
              </div>
              <div class="form-group">
                <label class="form-label">Stok</label>
                <input type="number" name="stok" class="form-control"
                  placeholder="Jumlah stok" min="0" required>
              </div>
              <button name="tambah" type="submit"
                class="btn btn-primary w-full" style="justify-content:center;">
                ➕ Tambah Buku
              </button>
            </form>
          </div>
        </div>
        <?php endif; ?>

        <!-- DAFTAR BUKU -->
        <div class="card">
          <div class="card-header">
            <h3>
              📚 Daftar Buku
              <span class="badge badge-blue" style="margin-left:8px;">
                <?= $total ?> buku
              </span>
            </h3>
            <form method="GET" style="display:flex;gap:8px;">
              <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" name="cari" class="search-input"
                  placeholder="Cari judul / penulis..."
                  value="<?= htmlspecialchars($cari) ?>">
              </div>
              <button type="submit" class="btn btn-primary btn-sm">Cari</button>
              <?php if($cari): ?>
              <a href="buku.php" class="btn btn-outline btn-sm">✕ Reset</a>
              <?php endif; ?>
            </form>
          </div>

          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Judul Buku</th>
                  <th>Penulis</th>
                  <th>Stok</th>
                  <!-- Kolom Aksi hanya tampil untuk admin -->
                  <?php if($is_admin): ?>
                  <th>Aksi</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
                <?php if($total === 0): ?>
                <tr>
                  <td colspan="<?= $is_admin ? 5 : 4 ?>">
                    <div class="empty-state">
                      <div class="empty-icon">📭</div>
                      <p><?= $cari ? "Buku tidak ditemukan." : "Belum ada buku." ?></p>
                    </div>
                  </td>
                </tr>
                <?php else: ?>
                <?php $no = 1; while($d = $data->fetch_assoc()): ?>
                <tr>
                  <td class="text-muted"><?= $no++ ?></td>
                  <td style="font-weight:600;"><?= htmlspecialchars($d['judul']) ?></td>
                  <td class="text-muted"><?= htmlspecialchars($d['penulis']) ?></td>
                  <td>
                    <?php $s = (int)$d['stok']; ?>
                    <?php if($s === 0): ?>
                      <span class="badge badge-red">Habis</span>
                    <?php elseif($s <= 2): ?>
                      <span class="badge badge-amber"><?= $s ?> tersisa</span>
                    <?php else: ?>
                      <span class="badge badge-green"><?= $s ?> stok</span>
                    <?php endif; ?>
                  </td>
                  <!-- Tombol aksi hanya untuk admin -->
                  <?php if($is_admin): ?>
                  <td>
                    <div style="display:flex;gap:6px;">
                      <a href="edit.php?id=<?= (int)$d['id'] ?>"
                        class="btn btn-outline btn-sm">✏️ Edit</a>
                      <a href="hapus.php?id=<?= (int)$d['id'] ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Yakin ingin menghapus buku ini?')">
                        🗑️
                      </a>
                    </div>
                  </td>
                  <?php endif; ?>
                </tr>
                <?php endwhile; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function(){
  if(<?= $_SESSION['dark_mode']??0 ?>) document.body.classList.add('dark-mode');
})();

function toggleDarkMode(){
  const isDark = document.body.classList.toggle('dark-mode');
  fetch('ajax_notif.php?action=dark_mode&mode='+(isDark?1:0));
  document.getElementById('darkIcon').textContent = isDark?'☀️':'🌙';
}
function toggleNotif(e){
  e.stopPropagation();
  document.getElementById('notifDropdown').classList.toggle('show');
}
document.addEventListener('click',function(){
  document.getElementById('notifDropdown')?.classList.remove('show');
});
function tandaiDibaca(){
  fetch('ajax_notif.php?action=read_all');
  document.querySelectorAll('.notif-item').forEach(el=>el.classList.remove('unread'));
  document.getElementById('notifBadge')?.remove();
}
</script>
</body>
</html>