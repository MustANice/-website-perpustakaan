<?php
session_start();
include 'koneksi.php';
include 'functions.php';

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit();
}

// Ambil notifikasi
$notif_list  = get_notifikasi($conn, 50);
$notif_count = count_notifikasi_unread($conn);

// Tandai semua dibaca jika ada request
if (isset($_GET['read_all'])) {
    tandai_semua_dibaca($conn);
    header("Location: notifikasi.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifikasi — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app">
  <?php include 'sidebar.php'; ?>

  <div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-left">
        <h2>Notifikasi</h2>
        <p><?= date('l, d F Y') ?></p>
      </div>
      <div class="topbar-right">
        <!-- Dark mode toggle -->
        <div class="dark-toggle" onclick="toggleDarkMode()" title="Toggle dark mode">
          <span id="darkIcon"><?= ($_SESSION['dark_mode'] ?? 0) ? '☀️' : '🌙' ?></span>
        </div>

        <!-- Notifikasi bell -->
        <div class="notif-wrap">
          <div class="notif-btn" onclick="toggleNotif(event)" title="Notifikasi">
            🔔
            <?php if ($notif_count > 0): ?>
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
              <?php if (empty($notif_list)): ?>
              <div class="notif-empty">✅ Tidak ada notifikasi</div>
              <?php else: ?>
              <?php foreach ($notif_list as $n): ?>
              <div class="notif-item <?= !$n['sudah_dibaca'] ? 'unread' : '' ?>">
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
          <div class="avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)) ?></div>
          <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?>
        </div>
      </div>
    </div>
    <!-- SELESAI TOPBAR -->

    <div class="page-content">

      <!-- Header halaman -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
          <h3 style="font-size:18px;font-weight:700;margin-bottom:2px;">🔔 Semua Notifikasi</h3>
          <p style="font-size:13px;color:var(--text-muted);">
            <?= $notif_count > 0 ? "$notif_count notifikasi belum dibaca" : "Semua notifikasi sudah dibaca" ?>
          </p>
        </div>
        <?php if ($notif_count > 0): ?>
        <a href="notifikasi.php?read_all=1" class="btn btn-outline btn-sm" style="font-size:12px;">
          ✅ Tandai Semua Dibaca
        </a>
        <?php endif; ?>
      </div>

      <!-- List notifikasi -->
      <div class="card" style="padding:0;overflow:hidden;">

        <?php if (empty($notif_list)): ?>
        <div class="empty-state" style="padding:60px 20px;text-align:center;">
          <div style="font-size:48px;margin-bottom:12px;">✅</div>
          <p style="font-weight:600;color:var(--text);margin-bottom:4px;">Tidak ada notifikasi</p>
          <span style="font-size:13px;color:var(--text-muted);">Semua berjalan lancar!</span>
        </div>

        <?php else: ?>
        <?php foreach ($notif_list as $n): ?>
        <?php
          $dot_color = match($n['tipe']) {
            'danger'  => '#ef4444',
            'warning' => '#f59e0b',
            'success' => '#10b981',
            default   => '#3b82f6'
          };
          $bg_unread = !$n['sudah_dibaca'] ? 'background:#fefce8;' : '';
        ?>
        <div style="display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-bottom:1px solid var(--border);transition:.15s;<?= $bg_unread ?>">

          <!-- Icon tipe -->
          <div style="flex-shrink:0;width:36px;height:36px;border-radius:10px;background:<?= $dot_color ?>22;display:flex;align-items:center;justify-content:center;margin-top:2px;">
            <div style="width:10px;height:10px;border-radius:50%;background:<?= $dot_color ?>;"></div>
          </div>

          <!-- Konten -->
          <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px;">
              <p style="font-size:13px;font-weight:600;color:var(--text);margin:0;">
                <?= htmlspecialchars($n['judul']) ?>
              </p>
              <?php if (!$n['sudah_dibaca']): ?>
              <span style="font-size:10px;background:#ef4444;color:#fff;padding:1px 7px;border-radius:20px;font-weight:600;">Baru</span>
              <?php endif; ?>
            </div>
            <p style="font-size:12px;color:var(--text-muted);margin:0 0 4px 0;">
              <?= htmlspecialchars($n['pesan']) ?>
            </p>
            <span style="font-size:11px;color:#94a3b8;">
              🕐 <?= date('d M Y, H:i', strtotime($n['created_at'])) ?>
              &nbsp;·&nbsp;
              <span style="text-transform:capitalize;"><?= $n['tipe'] ?></span>
            </span>
          </div>

        </div>
        <?php endforeach; ?>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const dm = <?= $_SESSION['dark_mode'] ?? 0 ?>;
  if(dm) document.body.classList.add('dark-mode');
})();

function toggleNotif(e){
  e.stopPropagation();
  document.getElementById('notifDropdown').classList.toggle('show');
}
document.addEventListener('click', function(){
  const dd = document.getElementById('notifDropdown');
  if(dd) dd.classList.remove('show');
});
function tandaiDibaca(){
  fetch('ajax_notif.php?action=read_all').then(()=>{
    document.querySelectorAll('.notif-item').forEach(el => el.classList.remove('unread'));
    const badge = document.getElementById('notifBadge');
    if(badge) badge.style.display = 'none';
  });
}
function toggleDarkMode(){
  const isDark = document.body.classList.toggle('dark-mode');
  fetch('ajax_notif.php?action=dark_mode&mode=' + (isDark ? 1 : 0));
  document.getElementById('darkIcon').textContent = isDark ? '☀️' : '🌙';
}
</script>

</body>
</html>