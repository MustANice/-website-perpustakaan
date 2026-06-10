<?php
include_once 'functions.php';

$current  = basename($_SERVER['PHP_SELF']);
$role     = $_SESSION['role']     ?? 'admin';
$nama     = $_SESSION['nama']     ?? 'Administrator';
$username = $_SESSION['username'] ?? 'admin';
$id_user  = $_SESSION['id_user']  ?? 0;

$dark_mode = $_SESSION['dark_mode'] ?? 0;

cek_denda_otomatis($conn);
cek_stok_rendah($conn);
$notif_list  = get_notifikasi($conn, 8);
$notif_count = count_notifikasi_unread($conn);

$role_label = ['superadmin'=>'Super Admin','admin'=>'Admin','petugas'=>'Petugas'];
$role_text  = $role_label[$role] ?? 'Admin';
$avatar     = strtoupper(substr($username, 0, 1));

$is_admin = in_array($role, ['admin', 'superadmin']);
?>
<div class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-wrap">
      <div class="logo-icon">📚</div>
      <div>
        <h1>Perpustakaan</h1>
        <span>Kampus Digital</span>
      </div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Menu Utama</div>

    <a href="dashboard.php" class="nav-item <?= $current=='dashboard.php'?'active':'' ?>">
      <span class="nav-icon">🏠</span> Dashboard
    </a>

    <a href="buku.php" class="nav-item <?= $current=='buku.php'?'active':'' ?>">
      <span class="nav-icon">📖</span> Data Buku
    </a>

    <a href="pinjam.php" class="nav-item <?= $current=='pinjam.php'?'active':'' ?>">
      <span class="nav-icon">📋</span> Peminjaman
    </a>

    <a href="kembali.php" class="nav-item <?= ($current=='kembali.php'||$current=='proses_kembali.php')?'active':'' ?>">
      <span class="nav-icon">🔄</span> Pengembalian
    </a>

    <?php if($is_admin): ?>
    <a href="laporan.php" class="nav-item <?= $current=='laporan.php'?'active':'' ?>">
      <span class="nav-icon">📊</span> Laporan
    </a>
    <?php endif; ?>

    <a href="ai_rekomendasi.php" class="nav-item <?= $current=='ai_rekomendasi.php'?'active':'' ?>">
      <span class="nav-icon">🤖</span> Rekomendasi AI
    </a>

    <?php if($role === 'superadmin'): ?>
    <div class="nav-label">Manajemen</div>
    <a href="users.php" class="nav-item <?= ($current=='users.php'||$current=='edit_user.php')?'active':'' ?>">
      <span class="nav-icon">👥</span> Kelola User
    </a>
    <a href="setting.php" class="nav-item <?= $current=='setting.php'?'active':'' ?>">
      <span class="nav-icon">⚙️</span> Setting
    </a>
    <a href="log_aktivitas.php" class="nav-item <?= $current=='log_aktivitas.php'?'active':'' ?>">
      <span class="nav-icon">📜</span> Log Aktivitas
    </a>
    <?php endif; ?>

    <div class="nav-label">Akun</div>
    <a href="profil.php" class="nav-item <?= $current=='profil.php'?'active':'' ?>">
      <span class="nav-icon">👤</span> Profil Saya
    </a>
    <a href="logout.php" class="nav-item logout">
      <span class="nav-icon">🚪</span> Logout
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><?= htmlspecialchars($avatar) ?></div>
      <div class="user-info">
        <p><?= htmlspecialchars($nama) ?></p>
        <span><?= htmlspecialchars($role_text) ?></span>
      </div>
    </div>
  </div>
</div>

<style>
body.dark-mode{
  --bg:#0f172a;--card-bg:#1e293b;--text:#e2e8f0;
  --text-muted:#94a3b8;--border:#334155;--sidebar-bg:#0a1628;
  background:var(--bg) !important;
}
body.dark-mode .main{background:var(--bg);}
body.dark-mode .topbar{background:var(--card-bg);border-color:var(--border);}
body.dark-mode .card{background:var(--card-bg);border-color:var(--border);}
body.dark-mode .stat-card{background:var(--card-bg);border-color:var(--border);}
body.dark-mode table thead th{background:#1e293b;color:#94a3b8;}
body.dark-mode tbody td{color:#e2e8f0;border-color:#1e293b;}
body.dark-mode tbody tr:hover{background:#1e293b;}
body.dark-mode .form-control{background:#1e293b;border-color:#334155;color:#e2e8f0;}
body.dark-mode .topbar-left h2{color:#e2e8f0;}
body.dark-mode .topbar-left p{color:#64748b;}
body.dark-mode .search-input{background:#1e293b;border-color:#334155;color:#e2e8f0;}
body.dark-mode .topbar-user{background:#1e293b;border-color:#334155;color:#e2e8f0;}
body.dark-mode .btn-outline{border-color:#334155;color:#e2e8f0;}
body.dark-mode .breadcrumb{color:#64748b;}
body.dark-mode .card-header{border-color:#334155;}
body.dark-mode .card-header h3{color:#e2e8f0;}
body.dark-mode .stat-info h3{color:#e2e8f0;}
body.dark-mode .page-content{background:var(--bg);}

.notif-wrap{position:relative;}
.notif-btn{
  width:38px;height:38px;background:var(--bg);
  border:1.5px solid var(--border);border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;font-size:18px;position:relative;transition:.2s;
}
.notif-btn:hover{background:var(--primary-light);border-color:var(--primary);}
.notif-badge{
  position:absolute;top:-5px;right:-5px;
  width:18px;height:18px;background:#ef4444;
  border-radius:50%;font-size:10px;font-weight:700;
  color:#fff;display:flex;align-items:center;justify-content:center;
  border:2px solid #fff;
}
body.dark-mode .notif-badge{border-color:#1e293b;}
.notif-dropdown{
  position:absolute;top:46px;right:0;width:320px;
  background:#fff;border:1px solid var(--border);
  border-radius:14px;box-shadow:0 20px 40px rgba(0,0,0,.15);
  z-index:9999;display:none;overflow:hidden;
}
body.dark-mode .notif-dropdown{background:#1e293b;border-color:#334155;}
.notif-dropdown.show{display:block;animation:dropIn .2s ease;}
@keyframes dropIn{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}
.notif-header{padding:14px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;}
.notif-header h4{font-size:13px;font-weight:700;color:var(--text);}
.notif-read-all{font-size:11.5px;color:var(--primary);cursor:pointer;font-weight:600;border:none;background:none;padding:0;}
.notif-read-all:hover{text-decoration:underline;}
.notif-list{max-height:320px;overflow-y:auto;}
.notif-item{padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;gap:10px;align-items:flex-start;transition:.15s;}
body.dark-mode .notif-item{border-color:#334155;}
.notif-item:hover{background:#f8fafc;}
body.dark-mode .notif-item:hover{background:#0f172a;}
.notif-item.unread{background:#fefce8;}
body.dark-mode .notif-item.unread{background:#1a2035;}
.notif-dot-wrap{flex-shrink:0;margin-top:3px;}
.notif-dot{width:8px;height:8px;border-radius:50%;}
.notif-dot.danger{background:#ef4444;}
.notif-dot.warning{background:#f59e0b;}
.notif-dot.info{background:#3b82f6;}
.notif-dot.success{background:#10b981;}
.notif-content p{font-size:12.5px;font-weight:600;color:var(--text);margin-bottom:2px;}
.notif-content span{font-size:11px;color:var(--text-muted);}
.notif-empty{padding:32px;text-align:center;color:var(--text-muted);font-size:13px;}
.notif-footer{padding:10px 16px;border-top:1px solid var(--border);text-align:center;}
.notif-footer a{font-size:12px;color:var(--primary);text-decoration:none;font-weight:600;}
.dark-toggle{
  width:38px;height:38px;background:var(--bg);
  border:1.5px solid var(--border);border-radius:10px;
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;font-size:18px;transition:.2s;user-select:none;
}
.dark-toggle:hover{background:var(--primary-light);border-color:var(--primary);}
</style>

<script>
(function(){
  const dm = <?= $dark_mode ?>;
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
    document.querySelectorAll('.notif-item').forEach(el=>el.classList.remove('unread'));
    const badge = document.getElementById('notifBadge');
    if(badge) badge.style.display='none';
  });
}
function toggleDarkMode(){
  const isDark = document.body.classList.toggle('dark-mode');
  fetch('ajax_notif.php?action=dark_mode&mode='+(isDark?1:0));
  document.getElementById('darkIcon').textContent = isDark?'☀️':'🌙';
}
</script>