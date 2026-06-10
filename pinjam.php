<?php
session_start();
include 'koneksi.php';
include 'functions.php';
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

$role     = $_SESSION['role'] ?? 'petugas';
$is_admin = in_array($role, ['admin', 'superadmin']);

define('BATAS_HARI', 30);
define('DENDA_PER_HARI', 2000);

$tanggal_hari_ini = date('Y-m-d');
$tanggal_batas    = date('Y-m-d', strtotime('+' . BATAS_HARI . ' days'));
$msg      = '';
$msg_type = '';

// PROSES PINJAM — semua role bisa
if(isset($_POST['pinjam'])){
    $id_buku = (int)$_POST['id_buku'];
    $id_user = (int)($_SESSION['id'] ?? $_SESSION['id_user'] ?? 1);

    if(!$id_buku){
        $msg = "⚠️ Pilih buku terlebih dahulu.";
        $msg_type = "warning";
    } else {
        $cek = $conn->prepare("SELECT stok FROM buku WHERE id = ?");
        $cek->bind_param("i", $id_buku);
        $cek->execute();
        $buku = $cek->get_result()->fetch_assoc();

        if(!$buku || $buku['stok'] <= 0){
            $msg = "❌ Stok habis!";
            $msg_type = "danger";
        } else {
            $stmt = $conn->prepare("INSERT INTO peminjaman (id_buku, id_user, tanggal_pinjam) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $id_buku, $id_user, $tanggal_hari_ini);
            if($stmt->execute()){
                $upd = $conn->prepare("UPDATE buku SET stok=stok-1 WHERE id=?");
                $upd->bind_param("i", $id_buku);
                $upd->execute();
                log_aktivitas($conn, $id_user, $_SESSION['username'],
                    'Peminjaman', "Meminjam buku ID: $id_buku");
                $msg = "✅ Buku berhasil dipinjam! Batas kembali: " . date('d M Y', strtotime($tanggal_batas));
                $msg_type = "success";
            } else {
                $msg = "❌ Gagal menyimpan.";
                $msg_type = "danger";
            }
        }
    }
}

// Update denda otomatis
mysqli_query($conn, "
    UPDATE peminjaman
    SET denda = (DATEDIFF(CURDATE(), tanggal_pinjam) - " . BATAS_HARI . ") * " . DENDA_PER_HARI . "
    WHERE tanggal_kembali IS NULL
      AND DATEDIFF(CURDATE(), tanggal_pinjam) > " . BATAS_HARI . "
");

$buku_list   = mysqli_query($conn, "SELECT * FROM buku WHERE stok > 0 ORDER BY judul ASC");
$riwayat     = mysqli_query($conn, "
    SELECT p.*, b.judul, b.penulis
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id
    ORDER BY p.id DESC LIMIT 10
");
$notif_list  = get_notifikasi($conn, 8);
$notif_count = count_notifikasi_unread($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Peminjaman — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>
  <div class="main">

    <div class="topbar">
      <div class="topbar-left">
        <h2>Peminjaman Buku</h2>
        <p>Catat peminjaman buku oleh anggota</p>
      </div>
      <div class="topbar-right">
        <div class="dark-toggle" onclick="toggleDarkMode()" title="Toggle dark mode">
          <span id="darkIcon"><?= ($_SESSION['dark_mode']??0) ? '☀️' : '🌙' ?></span>
        </div>
        <div class="notif-wrap">
          <div class="notif-btn" onclick="toggleNotif(event)">
            🔔
            <?php if($notif_count > 0): ?>
            <div class="notif-badge" id="notifBadge"><?= $notif_count > 9 ? '9+' : $notif_count ?></div>
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
                <div class="notif-dot-wrap"><div class="notif-dot <?= $n['tipe'] ?>"></div></div>
                <div class="notif-content">
                  <p><?= htmlspecialchars($n['judul']) ?></p>
                  <span><?= htmlspecialchars($n['pesan']) ?></span><br>
                  <span style="font-size:10px;color:#94a3b8;"><?= date('d M Y H:i', strtotime($n['created_at'])) ?></span>
                </div>
              </div>
              <?php endforeach; endif; ?>
            </div>
            <div class="notif-footer"><a href="notifikasi.php">Lihat semua →</a></div>
          </div>
        </div>
        <div class="topbar-user">
          <div class="avatar"><?= strtoupper(substr($_SESSION['username']??'A',0,1)) ?></div>
          <?= htmlspecialchars($_SESSION['nama']??'Admin') ?>
        </div>
      </div>
    </div>

    <div class="page-content">
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a><span class="sep">/</span><span>Peminjaman</span>
      </div>

      <?php if($msg): ?>
      <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start;">

        <!-- FORM PEMINJAMAN — semua role bisa -->
        <div class="card">
          <div class="card-header"><h3>📖 Form Peminjaman</h3></div>
          <div class="card-body">
            <?php if(mysqli_num_rows($buku_list) === 0): ?>
            <div class="alert alert-warning">⚠️ Semua buku habis stok.</div>
            <?php else: ?>
            <form method="POST">
              <div class="form-group">
                <label class="form-label">Pilih Buku</label>
                <select name="id_buku" class="form-control" required>
                  <option value="">-- Pilih buku --</option>
                  <?php while($b = mysqli_fetch_assoc($buku_list)): ?>
                  <option value="<?= (int)$b['id'] ?>">
                    <?= htmlspecialchars($b['judul']) ?> (Stok: <?= (int)$b['stok'] ?>)
                  </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Tanggal Pinjam</label>
                <div class="form-control" style="background:#f8f8ff;color:#6c63ff;font-weight:600;cursor:default;">
                  📅 <?= date('d M Y') ?> <small style="color:#888;">(hari ini)</small>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Batas Kembali</label>
                <div class="form-control" style="background:#f8f8ff;color:#e67e22;font-weight:600;cursor:default;">
                  ⏰ <?= date('d M Y', strtotime($tanggal_batas)) ?>
                  <small style="color:#888;">(<?= BATAS_HARI ?> hari)</small>
                </div>
              </div>
              <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12.5px;color:#b45309;">
                ⚠️ Denda keterlambatan <strong>Rp <?= number_format(DENDA_PER_HARI,0,',','.') ?>/hari</strong> setelah batas kembali.
              </div>
              <button name="pinjam" type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                📋 Catat Peminjaman
              </button>
            </form>
            <?php endif; ?>
          </div>
        </div>

        <!-- RIWAYAT -->
        <div class="card">
          <div class="card-header">
            <h3>📋 Riwayat Peminjaman</h3>
            <a href="kembali.php" class="btn btn-outline btn-sm">Proses Pengembalian</a>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>#</th><th>Buku</th><th>Tgl Pinjam</th><th>Batas Kembali</th><th>Denda</th><th>Status</th></tr>
              </thead>
              <tbody>
                <?php $no=1; if(mysqli_num_rows($riwayat)===0): ?>
                <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">📭</div><p>Belum ada riwayat</p></div></td></tr>
                <?php else: while($p=mysqli_fetch_assoc($riwayat)):
                  $tgl_pinjam    = strtotime($p['tanggal_pinjam']);
                  $tgl_batas_r   = strtotime('+'.BATAS_HARI.' days', $tgl_pinjam);
                  $hari_pinjam   = (int)floor((time()-$tgl_pinjam)/86400);
                  $terlambat     = max(0, $hari_pinjam-BATAS_HARI);
                  $denda         = (int)$p['denda'];
                  $sudah_kembali = !is_null($p['tanggal_kembali']);
                ?>
                <tr>
                  <td class="text-muted"><?= $no++ ?></td>
                  <td style="font-weight:600;"><?= htmlspecialchars($p['judul']) ?></td>
                  <td class="text-muted"><?= date('d M Y', $tgl_pinjam) ?></td>
                  <td class="text-muted"><?= date('d M Y', $tgl_batas_r) ?></td>
                  <td>
                    <?php if($denda>0): ?>
                    <span class="badge badge-red">Rp <?= number_format($denda,0,',','.') ?></span>
                    <?php else: ?>
                    <span class="badge badge-green">Tidak ada</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if($sudah_kembali): ?>
                    <span class="badge badge-green">Dikembalikan</span>
                    <?php elseif($terlambat>0): ?>
                    <span class="badge badge-red">Terlambat <?= $terlambat ?> hari</span>
                    <?php elseif($hari_pinjam>=BATAS_HARI-3): ?>
                    <span class="badge badge-amber">Segera kembali</span>
                    <?php else: ?>
                    <span class="badge badge-amber">Dipinjam</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endwhile; endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<script>
(function(){ if(<?= $_SESSION['dark_mode']??0 ?>) document.body.classList.add('dark-mode'); })();
function toggleDarkMode(){
  const isDark = document.body.classList.toggle('dark-mode');
  fetch('ajax_notif.php?action=dark_mode&mode='+(isDark?1:0));
  document.getElementById('darkIcon').textContent = isDark?'☀️':'🌙';
}
function toggleNotif(e){ e.stopPropagation(); document.getElementById('notifDropdown').classList.toggle('show'); }
document.addEventListener('click',function(){ document.getElementById('notifDropdown')?.classList.remove('show'); });
function tandaiDibaca(){
  fetch('ajax_notif.php?action=read_all');
  document.querySelectorAll('.notif-item').forEach(el=>el.classList.remove('unread'));
  document.getElementById('notifBadge')?.remove();
}
</script>
</body>
</html>