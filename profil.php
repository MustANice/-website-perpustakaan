<?php
session_start();
include 'koneksi.php';
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

$id_user = $_SESSION['id_user'];
$msg = ''; $msg_type = '';

// Ambil data user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Update profil
if(isset($_POST['update_profil'])){
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);

    if(empty($nama)||empty($username)){
        $msg="⚠️ Nama dan username wajib diisi."; $msg_type="warning";
    } else {
        // Cek username sudah dipakai user lain
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $cek->bind_param("si", $username, $id_user);
        $cek->execute();
        if($cek->get_result()->num_rows > 0){
            $msg="⚠️ Username sudah digunakan orang lain."; $msg_type="danger";
        } else {
            $upd = $conn->prepare("UPDATE users SET nama=?, username=? WHERE id=?");
            $upd->bind_param("ssi", $nama, $username, $id_user);
            if($upd->execute()){
                $_SESSION['username'] = $username;
                $user['nama']     = $nama;
                $user['username'] = $username;
                $msg="✅ Profil berhasil diupdate!"; $msg_type="success";
            }
        }
    }
}

// Ganti password
if(isset($_POST['ganti_password'])){
    $lama   = $_POST['password_lama'];
    $baru   = $_POST['password_baru'];
    $konfirm = $_POST['password_konfirm'];

    if(empty($lama)||empty($baru)||empty($konfirm)){
        $msg="⚠️ Semua kolom password wajib diisi."; $msg_type="warning";
    } elseif(strlen($baru) < 6){
        $msg="⚠️ Password baru minimal 6 karakter."; $msg_type="warning";
    } elseif($baru !== $konfirm){
        $msg="⚠️ Konfirmasi password tidak cocok."; $msg_type="danger";
    } else {
        // Verifikasi password lama
        $valid = ($lama === $user['password']) || password_verify($lama, $user['password']);
        if(!$valid){
            $msg="❌ Password lama salah."; $msg_type="danger";
        } else {
            $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
            $upd->bind_param("si", $baru, $id_user);
            if($upd->execute()){
                $msg="✅ Password berhasil diubah!"; $msg_type="success";
                $user['password'] = $baru;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Profil Saya — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-left"><h2>Profil Saya</h2><p>Kelola informasi akun kamu</p></div>
      <div class="topbar-right">
        <div class="topbar-user"><div class="avatar">A</div><?= htmlspecialchars($_SESSION['username']??'Admin') ?></div>
      </div>
    </div>
    <div class="page-content">
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a><span class="sep">/</span><span>Profil</span>
      </div>

      <?php if($msg): ?>
      <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

        <!-- KARTU PROFIL -->
        <div class="card">
          <div class="card-header"><h3>👤 Informasi Akun</h3></div>
          <div class="card-body">

            <!-- Avatar -->
            <div style="text-align:center;margin-bottom:24px;">
              <div style="width:80px;height:80px;background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:20px;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:#fff;margin:0 auto 12px;">
                <?= strtoupper(substr($user['nama'],0,1)) ?>
              </div>
              <p style="font-weight:700;font-size:16px;color:#0f172a;"><?= htmlspecialchars($user['nama']) ?></p>
              <span class="badge <?= $user['role']==='admin'?'badge-blue':'badge-green' ?>" style="margin-top:4px;">
                <?= ucfirst($user['role']) ?>
              </span>
            </div>

            <form method="POST">
              <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control"
                  value="<?= htmlspecialchars($user['nama']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control"
                  value="<?= htmlspecialchars($user['username']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Role</label>
                <input type="text" class="form-control"
                  value="<?= ucfirst($user['role']) ?>" disabled
                  style="background:#f1f5f9;color:#94a3b8;cursor:not-allowed;">
                <p class="form-hint">Role tidak bisa diubah sendiri</p>
              </div>
              <button name="update_profil" type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                💾 Simpan Perubahan
              </button>
            </form>
          </div>
        </div>

        <!-- GANTI PASSWORD -->
        <div class="card">
          <div class="card-header"><h3>🔑 Ganti Password</h3></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label class="form-label">Password Lama</label>
                <input type="password" name="password_lama" class="form-control"
                  placeholder="Masukkan password lama" required>
              </div>
              <div class="form-group">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password_baru" id="pwdBaru" class="form-control"
                  placeholder="Minimal 6 karakter" required>
                <div style="margin-top:6px;">
                  <div style="height:4px;border-radius:4px;background:#e2e8f0;overflow:hidden;margin-bottom:4px;">
                    <div id="strengthBar" style="height:100%;border-radius:4px;transition:.3s;width:0;"></div>
                  </div>
                  <span id="strengthTxt" style="font-size:11px;"></span>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_konfirm" id="pwdKonfirm" class="form-control"
                  placeholder="Ulangi password baru" required>
                <p id="matchTxt" style="font-size:11.5px;margin-top:5px;"></p>
              </div>
              <button name="ganti_password" type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                🔑 Ganti Password
              </button>
            </form>
          </div>
        </div>

      </div>

      <!-- INFO AKUN -->
      <div class="card" style="margin-top:20px;">
        <div class="card-header"><h3>📋 Ringkasan Akun</h3></div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
            <div style="background:#f8fafc;border-radius:12px;padding:16px;text-align:center;">
              <div style="font-size:28px;margin-bottom:8px;">👤</div>
              <p style="font-weight:700;font-size:15px;"><?= htmlspecialchars($user['username']) ?></p>
              <span style="font-size:12px;color:#64748b;">Username</span>
            </div>
            <div style="background:#f8fafc;border-radius:12px;padding:16px;text-align:center;">
              <div style="font-size:28px;margin-bottom:8px;">✍️</div>
              <p style="font-weight:700;font-size:15px;"><?= htmlspecialchars($user['nama']) ?></p>
              <span style="font-size:12px;color:#64748b;">Nama Lengkap</span>
            </div>
            <div style="background:#f8fafc;border-radius:12px;padding:16px;text-align:center;">
              <div style="font-size:28px;margin-bottom:8px;">🎭</div>
              <p style="font-weight:700;font-size:15px;"><?= ucfirst($user['role']) ?></p>
              <span style="font-size:12px;color:#64748b;">Role Akun</span>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
document.getElementById('pwdBaru').addEventListener('input', function(){
  const val = this.value;
  const bar = document.getElementById('strengthBar');
  const txt = document.getElementById('strengthTxt');
  let s = 0;
  if(val.length>=6) s++;
  if(val.length>=10) s++;
  if(/[A-Z]/.test(val)) s++;
  if(/[0-9]/.test(val)) s++;
  if(/[^A-Za-z0-9]/.test(val)) s++;
  const lvl=[{w:'0%',c:'',t:''},{w:'25%',c:'#ef4444',t:'Sangat lemah'},{w:'50%',c:'#f97316',t:'Lemah'},{w:'75%',c:'#f59e0b',t:'Cukup'},{w:'90%',c:'#10b981',t:'Kuat'},{w:'100%',c:'#4f46e5',t:'Sangat kuat'}];
  bar.style.width=lvl[s].w; bar.style.background=lvl[s].c;
  txt.textContent=lvl[s].t; txt.style.color=lvl[s].c;
});
document.getElementById('pwdKonfirm').addEventListener('input', function(){
  const txt = document.getElementById('matchTxt');
  const match = this.value === document.getElementById('pwdBaru').value;
  if(!this.value){ txt.textContent=''; return; }
  txt.textContent = match ? '✅ Password cocok' : '❌ Tidak cocok';
  txt.style.color = match ? '#10b981' : '#ef4444';
});
</script>
</body>
</html>