<?php
// Simpan sebagai topbar_right.php
// Lalu di setiap halaman, ganti bagian topbar-right dengan:
// <?php include 'topbar_right.php'; ?>
?>
<div class="topbar-right">
  <form action="buku.php" method="GET" class="search-wrap">
    <span class="search-icon">🔍</span>
    <input type="text" name="cari" class="search-input" placeholder="Cari buku...">
  </form>

  <!-- Dark mode toggle -->
  <div class="dark-toggle" onclick="toggleDarkMode()" title="Toggle dark mode">
    <span id="darkIcon"><?= ($dark_mode ?? 0) ? '☀️' : '🌙' ?></span>
  </div>

  <!-- Notifikasi -->
  <div class="notif-wrap">
    <div class="notif-btn" onclick="toggleNotif(event)" title="Notifikasi">
      🔔
      <?php if(($notif_count ?? 0) > 0): ?>
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
        <?php if(empty($notif_list ?? [])): ?>
        <div class="notif-empty">✅ Tidak ada notifikasi</div>
        <?php else: ?>
        <?php foreach(($notif_list ?? []) as $n): ?>
        <div class="notif-item <?= !$n['sudah_dibaca'] ? 'unread' : '' ?>">
          <div class="notif-dot-wrap">
            <div class="notif-dot <?= htmlspecialchars($n['tipe']) ?>"></div>
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
        <a href="notifikasi.php">Lihat semua notifikasi →</a>
      </div>
    </div>
  </div>

  <!-- User info -->
  <div class="topbar-user">
    <div class="avatar"><?= strtoupper(substr($_SESSION['username']??'A',0,1)) ?></div>
    <?= htmlspecialchars($_SESSION['nama'] ?? $_SESSION['username'] ?? 'Admin') ?>
  </div>
</div>
