<?php
session_start();
include 'koneksi.php';
include 'functions.php';
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

// Cek akses hanya superadmin
if (($_SESSION['role'] ?? '') !== 'superadmin') {
    header('Location: dashboard.php');
    exit;
}

$current  = basename($_SERVER['PHP_SELF']);
$role     = $_SESSION['role']     ?? 'superadmin';
$nama     = $_SESSION['nama']     ?? 'Administrator';
$username = $_SESSION['username'] ?? 'admin';

// Filter & Pagination
$per_page = 20;
$page     = max(1, intval($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

$filter_user   = $_GET['user']   ?? '';
$filter_aksi   = $_GET['aksi']   ?? '';
$filter_tanggal = $_GET['tanggal'] ?? '';

// Build query dengan filter
$where = "WHERE 1=1";
$params = [];
$types  = '';

if ($filter_user) {
    $where .= " AND (l.username LIKE ?)";
    $like = "%$filter_user%";
    $params[] = $like;
    $types .= 's';
}
if ($filter_aksi) {
    $where .= " AND l.aksi = ?";
    $params[] = $filter_aksi;
    $types .= 's';
}
if ($filter_tanggal) {
    $where .= " AND DATE(l.created_at) = ?";
    $params[] = $filter_tanggal;
    $types .= 's';
}

// Cek apakah tabel log_aktivitas ada, kalau tidak buat otomatis
$cek_tabel = $conn->query("SHOW TABLES LIKE 'log_aktivitas'");
if ($cek_tabel->num_rows === 0) {
    $conn->query("CREATE TABLE log_aktivitas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_user INT DEFAULT 0,
        username VARCHAR(100),
        nama VARCHAR(150),
        aksi VARCHAR(100),
        detail TEXT,
        ip_address VARCHAR(50),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

// Hitung total
$sql_count = "SELECT COUNT(*) as total FROM log_aktivitas l $where";
$stmt = $conn->prepare($sql_count);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total_row = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$total_page = ceil($total_row / $per_page);

// Ambil data
$sql = "SELECT l.* FROM log_aktivitas l $where ORDER BY l.created_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= 'ii';
$stmt2 = $conn->prepare($sql);
if ($params) $stmt2->bind_param($types, ...$params);
$stmt2->execute();
$logs = $stmt2->get_result();

// Daftar aksi unik untuk filter dropdown
$aksi_list = $conn->query("SELECT DISTINCT aksi FROM log_aktivitas ORDER BY aksi ASC");

// Hapus log (opsional, superadmin only)
if (isset($_GET['hapus_semua']) && $_SESSION['role'] === 'superadmin') {
    $conn->query("TRUNCATE TABLE log_aktivitas");
    header('Location: log_aktivitas.php?msg=cleared');
    exit;
}

// Data untuk topbar
$notif_list  = get_notifikasi($conn, 8);
$notif_count = count_notifikasi_unread($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log Aktivitas - Perpustakaan</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .log-badge {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600;
    }
    .log-badge.login    { background: #d1fae5; color: #065f46; }
    .log-badge.logout   { background: #fee2e2; color: #991b1b; }
    .log-badge.tambah   { background: #dbeafe; color: #1e40af; }
    .log-badge.edit     { background: #fef3c7; color: #92400e; }
    .log-badge.hapus    { background: #fce7f3; color: #9d174d; }
    .log-badge.pinjam   { background: #ede9fe; color: #5b21b6; }
    .log-badge.kembali  { background: #ecfdf5; color: #065f46; }
    .log-badge.default  { background: #f1f5f9; color: #475569; }
    .filter-bar { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; margin-bottom:16px; }
    .filter-bar input, .filter-bar select {
      padding: 7px 12px; border: 1.5px solid var(--border);
      border-radius: 8px; font-size: 13px; background: var(--bg);
      color: var(--text);
    }
    .filter-bar button {
      padding: 7px 16px; border-radius: 8px; font-size: 13px;
      font-weight: 600; cursor: pointer; border: none;
    }
    .btn-filter { background: var(--primary); color: #fff; }
    .btn-reset  { background: #f1f5f9; color: #475569; }
    .ip-text    { font-size: 11px; color: var(--text-muted); font-family: monospace; }
    .empty-state { text-align:center; padding: 48px 20px; color: var(--text-muted); }
    .empty-state .icon { font-size: 48px; margin-bottom: 12px; }
  </style>
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>

  <div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-left">
        <h2>Log Aktivitas</h2>
        <p><?= date('l, d F Y') ?></p>
      </div>
      <div class="topbar-right">
        <!-- Dark mode toggle -->
        <div class="dark-toggle" onclick="toggleDarkMode()" title="Toggle dark mode">
          <span id="darkIcon"><?= ($_SESSION['dark_mode']??0) ? '☀️' : '🌙' ?></span>
        </div>

        <!-- Notifikasi -->
        <div class="notif-wrap">
          <div class="notif-btn" onclick="toggleNotif(event)" title="Notifikasi">
            🔔
            <?php if(($notif_count??0) > 0): ?>
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
              <?php else: foreach($notif_list as $n): ?>
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
              <?php endforeach; endif; ?>
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
    </div>
    <!-- SELESAI TOPBAR -->

    <div class="page-content">
      <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div>
          <h2 style="font-size:20px;font-weight:700;color:var(--text);margin:0;">📜 Log Aktivitas</h2>
          <p style="color:var(--text-muted);font-size:13px;margin:4px 0 0;">Riwayat semua aktivitas pengguna sistem</p>
        </div>
        <?php if ($total_row > 0): ?>
        <a href="log_aktivitas.php?hapus_semua=1"
           onclick="return confirm('Yakin ingin menghapus SEMUA log aktivitas?')"
           style="padding:8px 16px;background:#fee2e2;color:#dc2626;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
          🗑️ Hapus Semua Log
        </a>
        <?php endif; ?>
      </div>

      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cleared'): ?>
      <div style="background:#d1fae5;color:#065f46;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
        ✅ Semua log aktivitas berhasil dihapus.
      </div>
      <?php endif; ?>

      <!-- Filter -->
      <form method="GET" class="filter-bar">
        <input type="text" name="user" placeholder="🔍 Cari username/nama..."
               value="<?= htmlspecialchars($filter_user) ?>" style="width:200px;">

        <select name="aksi">
          <option value="">Semua Aksi</option>
          <?php while ($a = $aksi_list->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($a['aksi']) ?>"
            <?= $filter_aksi === $a['aksi'] ? 'selected' : '' ?>>
            <?= htmlspecialchars(ucfirst($a['aksi'])) ?>
          </option>
          <?php endwhile; ?>
        </select>

        <input type="date" name="tanggal" value="<?= htmlspecialchars($filter_tanggal) ?>">

        <button type="submit" class="btn-filter">Filter</button>
        <a href="log_aktivitas.php" style="padding:7px 16px;border-radius:8px;font-size:13px;font-weight:600;background:#f1f5f9;color:#475569;text-decoration:none;">Reset</a>
      </form>

      <!-- Tabel -->
      <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
          <span style="font-size:13px;color:var(--text-muted);">
            Total: <strong><?= number_format($total_row) ?></strong> aktivitas
          </span>
          <span style="font-size:12px;color:var(--text-muted);">
            Halaman <?= $page ?> / <?= max(1,$total_page) ?>
          </span>
        </div>

        <div style="overflow-x:auto;">
          <table style="width:100%;border-collapse:collapse;">
            <thead>
              <tr style="background:#f8fafc;">
                <th style="padding:11px 14px;text-align:left;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid var(--border);">No</th>
                <th style="padding:11px 14px;text-align:left;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid var(--border);">Waktu</th>
                <th style="padding:11px 14px;text-align:left;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid var(--border);">Pengguna</th>
                <th style="padding:11px 14px;text-align:left;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid var(--border);">Aksi</th>
                <th style="padding:11px 14px;text-align:left;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid var(--border);">Keterangan</th>
                <th style="padding:11px 14px;text-align:left;font-size:12px;color:#64748b;font-weight:600;border-bottom:1px solid var(--border);">IP Address</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($logs->num_rows === 0): ?>
              <tr>
                <td colspan="6">
                  <div class="empty-state">
                    <div class="icon">📭</div>
                    <p>Belum ada log aktivitas</p>
                  </div>
                </td>
              </tr>
              <?php else: $no = $offset + 1; while ($row = $logs->fetch_assoc()): ?>
              <tr style="border-bottom:1px solid #f1f5f9;transition:.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                <td style="padding:11px 14px;font-size:13px;color:var(--text-muted);"><?= $no++ ?></td>
                <td style="padding:11px 14px;font-size:12.5px;color:var(--text);white-space:nowrap;">
                  <?= date('d/m/Y', strtotime($row["created_at"])) ?><br>
                  <span style="color:var(--text-muted);font-size:11px;"><?= date('H:i:s', strtotime($row["created_at"])) ?></span>
                </td>
                <td style="padding:11px 14px;">
                  <div style="font-size:13px;font-weight:600;color:var(--text);">@<?= htmlspecialchars($row['username'] ?? '-') ?></div>
                </td>
                <td style="padding:11px 14px;">
                  <?php
                    $aksi = strtolower($row['aksi'] ?? 'default');
                    $badge_class = in_array($aksi, ['login','logout','tambah','edit','hapus','pinjam','kembali']) ? $aksi : 'default';
                    $aksi_icon = [
                      'login'=>'🔑','logout'=>'🚪','tambah'=>'➕','edit'=>'✏️',
                      'hapus'=>'🗑️','pinjam'=>'📋','kembali'=>'🔄'
                    ][$aksi] ?? '📌';
                  ?>
                  <span class="log-badge <?= $badge_class ?>">
                    <?= $aksi_icon ?> <?= htmlspecialchars(ucfirst($row['aksi'])) ?>
                  </span>
                </td>
                <td style="padding:11px 14px;font-size:12.5px;color:var(--text);max-width:280px;">
                  <?= htmlspecialchars($row["detail"] ?? '-') ?>
                </td>
                <td style="padding:11px 14px;">
                  <span class="ip-text"><?= htmlspecialchars($row['ip_address'] ?? '-') ?></span>
                </td>
              </tr>
              <?php endwhile; endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_page > 1): ?>
        <div style="padding:14px 18px;border-top:1px solid var(--border);display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
          <?php
          $q = http_build_query(array_filter(['user'=>$filter_user,'aksi'=>$filter_aksi,'tanggal'=>$filter_tanggal]));
          for ($i = 1; $i <= $total_page; $i++):
            $active = $i === $page;
          ?>
          <a href="?page=<?= $i ?>&<?= $q ?>"
             style="padding:5px 12px;border-radius:7px;font-size:13px;text-decoration:none;font-weight:600;
                    background:<?= $active?'var(--primary)':'#f1f5f9' ?>;
                    color:<?= $active?'#fff':'#475569' ?>;">
            <?= $i ?>
          </a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>

    </div><!-- end page-content -->
  </div><!-- end main -->
</div>

</body>
</html>