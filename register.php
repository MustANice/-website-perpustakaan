<?php
include 'koneksi.php';

$msg = '';
$msg_type = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama']);
    $password = $_POST['password'];
    $konfirm  = $_POST['konfirm'];
    $role     = 'user';

    if(empty($username) || empty($nama) || empty($password)){
        $msg = "⚠️ Semua kolom wajib diisi.";
        $msg_type = "danger";
    } elseif(strlen($username) < 4){
        $msg = "⚠️ Username minimal 4 karakter.";
        $msg_type = "danger";
    } elseif(strlen($password) < 6){
        $msg = "⚠️ Password minimal 6 karakter.";
        $msg_type = "danger";
    } elseif($password !== $konfirm){
        $msg = "⚠️ Konfirmasi password tidak cocok.";
        $msg_type = "danger";
    } else {
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        $cek_result = $cek->get_result();

        if($cek_result->num_rows > 0){
            $msg = "⚠️ Username sudah digunakan.";
            $msg_type = "danger";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, nama, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $nama, $password, $role);
            if($stmt->execute()){
                header("Location: index.php?registered=1");
                exit;
            } else {
                $msg = "❌ Gagal membuat akun.";
                $msg_type = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Plus Jakarta Sans',sans-serif;background:#fff;min-height:100vh;display:flex;flex-direction:column;}
    .login-wrap{flex:1;display:grid;grid-template-columns:1fr 1fr;min-height:100vh;}

    /* LEFT PANEL */
    .left-panel{background:linear-gradient(160deg,#1e1b4b 0%,#312e81 40%,#4338ca 100%);display:flex;flex-direction:column;justify-content:space-between;padding:40px;position:relative;overflow:hidden;}
    .left-panel::before{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(99,102,241,.25) 0%,transparent 70%);top:-100px;right:-100px;}
    .left-panel::after{content:'';position:absolute;width:350px;height:350px;background:radial-gradient(circle,rgba(167,139,250,.15) 0%,transparent 70%);bottom:-80px;left:-80px;}
    .left-brand{display:flex;align-items:center;gap:10px;position:relative;z-index:1;}
    .left-brand .brand-ico{width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;border:1px solid rgba(255,255,255,.2);}
    .left-brand h2{font-size:15px;font-weight:700;color:#fff;}
    .left-brand span{font-size:11px;color:rgba(255,255,255,.55);display:block;}
    .left-center{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;position:relative;z-index:1;gap:20px;}
    .left-big-icon{font-size:80px;animation:float 3s ease-in-out infinite;}
    @keyframes float{0%,100%{transform:translateY(0);}50%{transform:translateY(-12px);}}
    .left-center h2{font-size:26px;font-weight:800;color:#fff;text-align:center;line-height:1.3;}
    .left-center p{font-size:13.5px;color:rgba(255,255,255,.6);text-align:center;line-height:1.7;max-width:300px;}
    .feature-list{display:flex;flex-direction:column;gap:12px;width:100%;max-width:300px;}
    .feature-item{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:12px 16px;}
    .feature-item span:first-child{font-size:20px;}
    .feature-item p{font-size:13px;font-weight:600;color:#fff;margin:0;}
    .feature-item small{font-size:11px;color:rgba(255,255,255,.5);}
    .left-bottom{position:relative;z-index:1;background:rgba(0,0,0,.25);border-radius:14px;padding:16px 20px;border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:14px;}
    .left-bottom .lb-icon{font-size:28px;flex-shrink:0;}
    .left-bottom p{font-size:13.5px;font-weight:600;color:#fff;margin-bottom:2px;}
    .left-bottom span{font-size:12px;color:rgba(255,255,255,.55);}

    /* RIGHT PANEL */
    .right-panel{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 56px;background:#fff;overflow-y:auto;}
    .right-inner{width:100%;max-width:400px;}
    .right-header{margin-bottom:28px;}
    .right-header h1{font-size:28px;font-weight:800;color:#0f172a;margin-bottom:6px;}
    .right-header p{font-size:14px;color:#64748b;}

    /* FORM */
    .form-group{margin-bottom:18px;}
    .form-label{display:block;font-size:13px;font-weight:600;color:#0f172a;margin-bottom:8px;}
    .input-wrap{position:relative;}
    .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:16px;color:#94a3b8;}
    .input-eye{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:16px;color:#94a3b8;cursor:pointer;user-select:none;}
    .form-control{width:100%;padding:12px 16px 12px 42px;border:1.5px solid #e2e8f0;border-radius:12px;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;color:#0f172a;background:#f8fafc;outline:none;transition:.2s;}
    .form-control:focus{border-color:#4f46e5;background:#fff;box-shadow:0 0 0 3px rgba(79,70,229,.08);}
    .form-control::placeholder{color:#cbd5e1;}
    .form-hint{font-size:11.5px;color:#94a3b8;margin-top:5px;}

    /* PASSWORD STRENGTH */
    .strength-bar{height:4px;border-radius:4px;background:#e2e8f0;overflow:hidden;margin-top:6px;margin-bottom:4px;}
    .strength-fill{height:100%;border-radius:4px;transition:.3s;width:0;}
    .strength-text{font-size:11px;}

    /* BUTTON */
    .btn-register{width:100%;padding:13px;background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border:none;border-radius:12px;font-family:'Plus Jakarta Sans',sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 15px rgba(79,70,229,.3);}
    .btn-register:hover{background:linear-gradient(135deg,#4338ca,#4f46e5);transform:translateY(-1px);}

    /* ALERT */
    .alert{padding:12px 16px;border-radius:10px;font-size:13px;font-weight:500;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
    .alert-danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;}
    .alert-success{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;}

    .login-link{text-align:center;margin-top:20px;font-size:13px;color:#64748b;}
    .login-link a{color:#4f46e5;font-weight:700;text-decoration:none;}
    .login-link a:hover{text-decoration:underline;}
    .login-footer-bar{text-align:center;padding:16px;font-size:12px;color:#94a3b8;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;gap:6px;}

    @media(max-width:768px){
      .login-wrap{grid-template-columns:1fr;}
      .left-panel{display:none;}
      .right-panel{padding:40px 28px;}
    }
  </style>
</head>
<body>

<div class="login-wrap">

  <!-- LEFT PANEL -->
  <div class="left-panel">
    <div class="left-brand">
      <div class="brand-ico">📚</div>
      <div>
        <h2>Perpustakaan Digital</h2>
        <span>Baca hari ini, raih masa depan</span>
      </div>
    </div>

    <div class="left-center">
      <div class="left-big-icon">🎓</div>
      <h2>Bergabung Sekarang!</h2>
      <p>Daftarkan akun kamu dan nikmati akses ke sistem perpustakaan kampus.</p>
      <div class="feature-list">
        <div class="feature-item">
          <span>📖</span>
          <div><p>Pinjam Buku</p><small>Akses ribuan koleksi buku</small></div>
        </div>
        <div class="feature-item">
          <span>🔄</span>
          <div><p>Kelola Peminjaman</p><small>Mudah dan cepat</small></div>
        </div>
        <div class="feature-item">
          <span>📊</span>
          <div><p>Riwayat Lengkap</p><small>Pantau semua aktivitas kamu</small></div>
        </div>
      </div>
    </div>

    <div class="left-bottom">
      <div class="lb-icon">🛡️</div>
      <div>
        <p>Data kamu aman bersama kami.</p>
        <span>Sistem terenkripsi &amp; terpercaya.</span>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="right-panel">
    <div class="right-inner">

      <div class="right-header">
        <h1>Buat Akun</h1>
        <p>Isi form berikut untuk mendaftar</p>
      </div>

      <?php if($msg): ?>
      <div class="alert alert-<?= $msg_type ?>"><?= $msg ?></div>
      <?php endif; ?>

      <form method="POST" id="registerForm">

        <div class="form-group">
          <label class="form-label">Nama Lengkap</label>
          <div class="input-wrap">
            <span class="input-icon">✍️</span>
            <input type="text" name="nama" class="form-control"
              placeholder="Masukkan nama lengkap" required
              value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Username</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input type="text" name="username" class="form-control"
              placeholder="Buat username unik" required
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
          <p class="form-hint">Minimal 4 karakter, tanpa spasi</p>
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" name="password" id="pwdInput"
              class="form-control" placeholder="Buat password kuat" required>
            <span class="input-eye" id="eyeToggle1">👁️</span>
          </div>
          <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
          <span class="strength-text" id="strengthText"></span>
        </div>

        <div class="form-group">
          <label class="form-label">Konfirmasi Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" name="konfirm" id="konfirmInput"
              class="form-control" placeholder="Ulangi password" required>
            <span class="input-eye" id="eyeToggle2">👁️</span>
          </div>
          <p class="form-hint" id="matchText"></p>
        </div>

        <button type="submit" class="btn-register">
          🎓 Daftar Sekarang
        </button>

      </form>

      <div class="login-link">
        Sudah punya akun? <a href="index.php">Masuk di sini</a>
      </div>

    </div>
  </div>
</div>

<div class="login-footer-bar">
  🛡️ Aman &amp; Terpercaya &nbsp;·&nbsp; Data Anda kami jaga kerahasiaannya.
</div>

<script>
// Toggle show/hide password
document.getElementById('eyeToggle1').addEventListener('click', function(){
  const pwd = document.getElementById('pwdInput');
  pwd.type = pwd.type === 'password' ? 'text' : 'password';
  this.textContent = pwd.type === 'password' ? '👁️' : '🙈';
});

document.getElementById('eyeToggle2').addEventListener('click', function(){
  const pwd = document.getElementById('konfirmInput');
  pwd.type = pwd.type === 'password' ? 'text' : 'password';
  this.textContent = pwd.type === 'password' ? '👁️' : '🙈';
});

// Password strength checker
document.getElementById('pwdInput').addEventListener('input', function(){
  const val  = this.value;
  const fill = document.getElementById('strengthFill');
  const text = document.getElementById('strengthText');

  let strength = 0;
  if(val.length >= 6)            strength++;
  if(val.length >= 10)           strength++;
  if(/[A-Z]/.test(val))         strength++;
  if(/[0-9]/.test(val))         strength++;
  if(/[^A-Za-z0-9]/.test(val))  strength++;

  const levels = [
    {w:'0%',   c:'',        t:''},
    {w:'25%',  c:'#ef4444', t:'Sangat lemah'},
    {w:'50%',  c:'#f97316', t:'Lemah'},
    {w:'75%',  c:'#f59e0b', t:'Cukup kuat'},
    {w:'90%',  c:'#10b981', t:'Kuat'},
    {w:'100%', c:'#4f46e5', t:'Sangat kuat'},
  ];

  fill.style.width      = levels[strength].w;
  fill.style.background = levels[strength].c;
  text.textContent      = levels[strength].t;
  text.style.color      = levels[strength].c;
});

// Konfirmasi password match
document.getElementById('konfirmInput').addEventListener('input', function(){
  const pwd  = document.getElementById('pwdInput').value;
  const text = document.getElementById('matchText');

  if(this.value === ''){
    text.textContent = '';
  } else if(this.value === pwd){
    text.textContent = '✅ Password cocok';
    text.style.color = '#10b981';
  } else {
    text.textContent = '❌ Password tidak cocok';
    text.style.color = '#ef4444';
  }
});
</script>

</body>
</html>