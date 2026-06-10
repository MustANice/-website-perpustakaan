<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Plus Jakarta Sans',sans-serif;background:#fff;min-height:100vh;display:flex;flex-direction:column;}

    /* LOADING */
    .loading-overlay{position:fixed;inset:0;z-index:9999;background:linear-gradient(135deg,#080b18 0%,#0f1629 50%,#080b18 100%);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:20px;opacity:0;pointer-events:none;transition:opacity .3s ease;}
    .loading-overlay.show{opacity:1;pointer-events:all;}
    .spinner{width:64px;height:64px;position:relative;}
    .spinner-ring{position:absolute;inset:0;border-radius:50%;border:3px solid transparent;animation:spinRing 1.2s cubic-bezier(.5,0,.5,1) infinite;}
    .spinner-ring:nth-child(1){border-top-color:#6366f1;animation-delay:-.45s;}
    .spinner-ring:nth-child(2){border-top-color:#06b6d4;animation-delay:-.3s;inset:6px;}
    .spinner-ring:nth-child(3){border-top-color:#a78bfa;animation-delay:-.15s;inset:12px;}
    @keyframes spinRing{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
    .loading-icon{font-size:28px;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);animation:iconPulse 1.2s ease-in-out infinite;}
    @keyframes iconPulse{0%,100%{transform:translate(-50%,-50%) scale(1);}50%{transform:translate(-50%,-50%) scale(1.15);}}
    .loading-text{color:#94a3b8;font-size:14px;font-weight:600;letter-spacing:.05em;animation:textFade 1.5s ease-in-out infinite;}
    @keyframes textFade{0%,100%{opacity:.5;}50%{opacity:1;}}
    .loading-dots span{display:inline-block;width:6px;height:6px;border-radius:50%;margin:0 3px;animation:dotBounce .8s ease-in-out infinite;}
    .loading-dots span:nth-child(1){background:#6366f1;animation-delay:0s;}
    .loading-dots span:nth-child(2){background:#06b6d4;animation-delay:.15s;}
    .loading-dots span:nth-child(3){background:#a78bfa;animation-delay:.3s;}
    @keyframes dotBounce{0%,100%{transform:translateY(0);opacity:.4;}50%{transform:translateY(-8px);opacity:1;}}

    /* LAYOUT */
    .login-wrap{flex:1;display:grid;grid-template-columns:1fr 1fr;min-height:100vh;}

    /* LEFT */
    .left-panel{background:linear-gradient(160deg,#0a1628 0%,#0f2244 40%,#1a3a6e 100%);display:flex;flex-direction:column;justify-content:space-between;padding:40px;position:relative;overflow:hidden;}
    .left-panel::before{content:'';position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(29,78,216,.2) 0%,transparent 70%);top:-100px;right:-100px;pointer-events:none;}
    .left-panel::after{content:'';position:absolute;width:350px;height:350px;background:radial-gradient(circle,rgba(96,165,250,.1) 0%,transparent 70%);bottom:-80px;left:-80px;pointer-events:none;}
    .left-brand{display:flex;align-items:center;gap:10px;position:relative;z-index:1;}
    .left-brand .brand-ico{width:36px;height:36px;background:rgba(255,255,255,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;border:1px solid rgba(255,255,255,.15);}
    .left-brand h2{font-size:15px;font-weight:700;color:#fff;}
    .left-brand span{font-size:11px;color:rgba(255,255,255,.45);display:block;}
    .left-image{flex:1;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;margin:20px 0;}
    .library-visual{width:100%;max-width:340px;}
    .left-bottom{position:relative;z-index:1;background:rgba(0,0,0,.3);border-radius:14px;padding:16px 20px;border:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:14px;}
    .left-bottom .lb-icon{font-size:28px;flex-shrink:0;}
    .left-bottom p{font-size:13.5px;font-weight:600;color:#fff;margin-bottom:2px;}
    .left-bottom span{font-size:12px;color:rgba(255,255,255,.45);}

    /* RIGHT */
    .right-panel{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:48px 56px;background:#fff;}
    .right-inner{width:100%;max-width:400px;}
    .right-header{margin-bottom:32px;}
    .right-header h1{font-size:30px;font-weight:800;color:#0f172a;margin-bottom:6px;}
    .right-header p{font-size:14px;color:#64748b;}

    /* FORM */
    .form-group{margin-bottom:20px;}
    .form-label{display:block;font-size:13px;font-weight:600;color:#0f172a;margin-bottom:8px;}
    .input-wrap{position:relative;}
    .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:16px;color:#94a3b8;}
    .input-eye{position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:16px;color:#94a3b8;cursor:pointer;user-select:none;}
    .form-control{width:100%;padding:12px 16px 12px 42px;border:1.5px solid #e2e8f0;border-radius:12px;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;color:#0f172a;background:#f8fafc;outline:none;transition:.2s;}
    .form-control:focus{border-color:#1d4ed8;background:#fff;box-shadow:0 0 0 3px rgba(29,78,216,.08);}
    .form-control::placeholder{color:#cbd5e1;}
    .forgot-wrap{display:flex;justify-content:flex-end;margin-top:-12px;margin-bottom:20px;}
    .forgot-link{font-size:12.5px;color:#1d4ed8;font-weight:600;text-decoration:none;cursor:pointer;}
    .forgot-link:hover{text-decoration:underline;}

    /* Modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:.2s;}
    .modal-overlay.show{opacity:1;pointer-events:all;}
    .modal-box{background:#fff;border-radius:20px;padding:32px;max-width:380px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,.2);transform:scale(.95);transition:.2s;}
    .modal-overlay.show .modal-box{transform:scale(1);}
    .modal-icon{font-size:48px;text-align:center;margin-bottom:16px;}
    .modal-box h3{font-size:18px;font-weight:800;color:#0f172a;text-align:center;margin-bottom:8px;}
    .modal-box p{font-size:13px;color:#64748b;text-align:center;line-height:1.7;margin-bottom:20px;}
    .modal-steps{background:#f8fafc;border-radius:12px;padding:16px;margin-bottom:20px;}
    .modal-step{display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;}
    .modal-step:last-child{margin-bottom:0;}
    .step-num{width:22px;height:22px;background:#1d4ed8;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;margin-top:1px;}
    .step-text{font-size:12.5px;color:#475569;line-height:1.5;}
    .modal-close{width:100%;padding:11px;background:#1d4ed8;color:#fff;border:none;border-radius:12px;font-family:'Plus Jakarta Sans',sans-serif;font-size:14px;font-weight:700;cursor:pointer;transition:.2s;}
    .modal-close:hover{background:#1e40af;}

    /* Button */
    .btn-login{width:100%;padding:13px;background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;border:none;border-radius:12px;font-family:'Plus Jakarta Sans',sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:.2s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 15px rgba(29,78,216,.3);}
    .btn-login:hover{background:linear-gradient(135deg,#1e40af,#1d4ed8);box-shadow:0 6px 20px rgba(29,78,216,.4);transform:translateY(-1px);}
    .btn-login.loading{pointer-events:none;opacity:.8;}
    .register-link{text-align:center;margin-top:24px;font-size:13px;color:#64748b;}
    .register-link a{color:#1d4ed8;font-weight:700;text-decoration:none;}
    .register-link a:hover{text-decoration:underline;}

    /* Alert */
    .alert{padding:12px 16px;border-radius:10px;font-size:13px;font-weight:500;margin-bottom:20px;display:flex;align-items:center;gap:8px;}
    .alert-danger{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;}
    .alert-success{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;}

    /* Footer */
    .login-footer-bar{text-align:center;padding:16px;font-size:12px;color:#94a3b8;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:center;gap:6px;}

    @media(max-width:768px){
      .login-wrap{grid-template-columns:1fr;}
      .left-panel{display:none;}
      .right-panel{padding:40px 28px;}
    }
  </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
  <div class="spinner">
    <div class="spinner-ring"></div>
    <div class="spinner-ring"></div>
    <div class="spinner-ring"></div>
    <span class="loading-icon">📚</span>
  </div>
  <div class="loading-text">Memverifikasi akun...</div>
  <div class="loading-dots"><span></span><span></span><span></span></div>
</div>

<!-- Modal Lupa Password -->
<div class="modal-overlay" id="modalLupa">
  <div class="modal-box">
    <div class="modal-icon">🔑</div>
    <h3>Lupa Password?</h3>
    <p>Karena sistem ini bersifat internal kampus, reset password dilakukan dengan cara berikut:</p>
    <div class="modal-steps">
      <div class="modal-step">
        <div class="step-num">1</div>
        <div class="step-text">Login menggunakan password lama kamu jika masih ingat</div>
      </div>
      <div class="modal-step">
        <div class="step-num">2</div>
        <div class="step-text">Masuk ke menu <strong>Profil</strong> di dashboard</div>
      </div>
      <div class="modal-step">
        <div class="step-num">3</div>
        <div class="step-text">Klik <strong>"Ganti Password"</strong> dan isi password baru</div>
      </div>
      <div class="modal-step">
        <div class="step-num">4</div>
        <div class="step-text">Jika benar-benar lupa, hubungi <strong>Admin Perpustakaan</strong> untuk reset manual</div>
      </div>
    </div>
    <button class="modal-close" onclick="document.getElementById('modalLupa').classList.remove('show')">
      Mengerti
    </button>
  </div>
</div>

<div class="login-wrap" id="loginWrap">

  <!-- ===== LEFT PANEL ===== -->
  <div class="left-panel">
    <div class="left-brand">
      <div class="brand-ico">📚</div>
      <div>
        <h2>Perpustakaan Digital</h2>
        <span>Baca hari ini, raih masa depan</span>
      </div>
    </div>

    <!-- KARAKTER ANIMASI SVG -->
    <div class="left-image">
      <div class="library-visual">
        <svg width="100%" viewBox="0 0 280 300" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <style>
              .head-nod{animation:nod 4s ease-in-out infinite;}
              @keyframes nod{0%,100%{transform:rotate(-12deg) translateX(0);}50%{transform:rotate(-20deg) translateX(-2px);}}
              .arm-l{animation:armL 4s ease-in-out infinite;transform-origin:130px 155px;}
              @keyframes armL{0%,100%{transform:rotate(0deg);}50%{transform:rotate(4deg);}}
              .arm-r{animation:armR 4s ease-in-out infinite;transform-origin:160px 155px;}
              @keyframes armR{0%,100%{transform:rotate(0deg);}50%{transform:rotate(-4deg);}}
              .book-float{animation:bookFloat 4s ease-in-out infinite;}
              @keyframes bookFloat{0%,100%{transform:translateY(0);}50%{transform:translateY(-3px);}}
              .breathe{animation:breathe 4s ease-in-out infinite;}
              @keyframes breathe{0%,100%{transform:scaleY(1);}50%{transform:scaleY(1.015);}}
              .eye-blink{animation:eblink 5s ease-in-out infinite;}
              @keyframes eblink{0%,44%,56%,100%{transform:scaleY(1);}50%{transform:scaleY(0.08);}}
              .lamp-ray{animation:rayPulse 2s ease-in-out infinite alternate;}
              @keyframes rayPulse{0%{opacity:.15;}100%{opacity:.3;}}
              .glow-lamp{animation:lampGlow 2s ease-in-out infinite alternate;}
              @keyframes lampGlow{0%{opacity:.7;}100%{opacity:1;}}
              .particle{animation:particleRise 3s ease-in-out infinite;}
              @keyframes particleRise{0%{opacity:0;transform:translateY(0);}40%{opacity:.6;}100%{opacity:0;transform:translateY(-30px);}}
              .p2{animation-delay:.8s;}
              .p3{animation-delay:1.6s;}
            </style>
          </defs>

          <!-- Background malam -->
          <rect x="0" y="0" width="280" height="300" fill="#070e1a" rx="16"/>

          <!-- Bintang -->
          <circle cx="30" cy="25" r="1.2" fill="#e2e8f0" opacity=".5"/>
          <circle cx="70" cy="15" r=".8" fill="#e2e8f0" opacity=".4"/>
          <circle cx="110" cy="30" r="1" fill="#e2e8f0" opacity=".3"/>
          <circle cx="200" cy="20" r="1.2" fill="#e2e8f0" opacity=".5"/>
          <circle cx="240" cy="35" r=".8" fill="#e2e8f0" opacity=".35"/>
          <circle cx="258" cy="12" r="1" fill="#e2e8f0" opacity=".4"/>
          <circle cx="170" cy="28" r=".7" fill="#e2e8f0" opacity=".3"/>
          <circle cx="50" cy="50" r=".6" fill="#bfdbfe" opacity=".3"/>
          <circle cx="220" cy="55" r=".8" fill="#bfdbfe" opacity=".25"/>

          <!-- Bulan -->
          <circle cx="240" cy="50" r="18" fill="#1e3a5f"/>
          <circle cx="248" cy="44" r="16" fill="#070e1a"/>

          <!-- Sinar lampu -->
          <polygon points="195,80 165,200 225,200" fill="#fbbf24" opacity=".06" class="lamp-ray"/>
          <polygon points="195,82 170,180 220,180" fill="#fef3c7" opacity=".08" class="lamp-ray"/>

          <!-- Lantai -->
          <rect x="20" y="240" width="240" height="8" rx="4" fill="#0f1f3d"/>
          <ellipse cx="140" cy="244" rx="80" ry="6" fill="#0c1a36"/>

          <!-- Rak buku kiri -->
          <rect x="20" y="80" width="12" height="160" rx="3" fill="#0d1e3a"/>
          <rect x="20" y="80" width="50" height="6" rx="2" fill="#0d1e3a"/>
          <rect x="20" y="130" width="50" height="6" rx="2" fill="#0d1e3a"/>
          <rect x="20" y="180" width="50" height="6" rx="2" fill="#0d1e3a"/>
          <rect x="24" y="88" width="9" height="40" rx="2" fill="#1d4ed8"/>
          <rect x="34" y="93" width="7" height="35" rx="2" fill="#0891b2"/>
          <rect x="42" y="86" width="8" height="42" rx="2" fill="#7c3aed"/>
          <rect x="51" y="91" width="6" height="37" rx="2" fill="#15803d"/>
          <rect x="24" y="138" width="10" height="38" rx="2" fill="#b45309"/>
          <rect x="35" y="142" width="7" height="34" rx="2" fill="#1d4ed8"/>
          <rect x="43" y="136" width="9" height="40" rx="2" fill="#dc2626"/>

          <!-- Meja -->
          <rect x="75" y="200" width="190" height="10" rx="4" fill="#0f2544"/>
          <rect x="80" y="210" width="8" height="30" rx="3" fill="#0d1e3a"/>
          <rect x="257" y="210" width="8" height="30" rx="3" fill="#0d1e3a"/>

          <!-- Lampu meja -->
          <rect x="225" y="148" width="6" height="54" rx="3" fill="#1e3a5f"/>
          <rect x="218" y="200" width="20" height="5" rx="2" fill="#1e3a5f"/>
          <path d="M228 148 Q218 120 196 90" stroke="#1e3a5f" stroke-width="5" fill="none" stroke-linecap="round"/>
          <ellipse cx="196" cy="87" rx="18" ry="8" fill="#1e3a5f"/>
          <path d="M178 87 L182 100 L210 100 L214 87" fill="#162d4f" stroke="#1e3a5f" stroke-width=".5"/>
          <circle cx="196" cy="92" r="5" fill="#fbbf24" opacity=".7" class="glow-lamp"/>
          <circle cx="196" cy="92" r="9" fill="#fef3c7" opacity=".12" class="glow-lamp"/>

          <!-- Kursi -->
          <rect x="105" y="145" width="60" height="58" rx="6" fill="#0d1e3a"/>
          <rect x="100" y="195" width="70" height="12" rx="5" fill="#0f2544"/>
          <rect x="105" y="207" width="8" height="30" rx="4" fill="#0d1e3a"/>
          <rect x="157" y="207" width="8" height="30" rx="4" fill="#0d1e3a"/>

          <!-- Tubuh -->
          <g class="breathe" style="transform-origin:140px 180px;">
            <rect x="122" y="160" width="40" height="45" rx="10" fill="#1d4ed8"/>
            <path d="M135 160 L142 170 L149 160" stroke="#93c5fd" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <line x1="142" y1="172" x2="142" y2="200" stroke="#1e40af" stroke-width="1.5" stroke-dasharray="3,3"/>
          </g>

          <!-- Tangan kiri -->
          <g class="arm-l">
            <path d="M124 165 Q105 185 108 205" stroke="#1d4ed8" stroke-width="12" fill="none" stroke-linecap="round"/>
            <circle cx="109" cy="207" r="7" fill="#fcd9a0"/>
          </g>

          <!-- Tangan kanan -->
          <g class="arm-r">
            <path d="M160 165 Q175 185 168 205" stroke="#1d4ed8" stroke-width="12" fill="none" stroke-linecap="round"/>
            <circle cx="167" cy="207" r="7" fill="#fcd9a0"/>
          </g>

          <!-- Buku yang dipegang -->
          <g class="book-float">
            <rect x="112" y="198" width="50" height="36" rx="3" fill="#f1f5f9"/>
            <rect x="112" y="198" width="7" height="36" rx="3" fill="#2563eb"/>
            <line x1="124" y1="208" x2="158" y2="208" stroke="#94a3b8" stroke-width="1.5"/>
            <line x1="124" y1="215" x2="155" y2="215" stroke="#94a3b8" stroke-width="1.5"/>
            <line x1="124" y1="222" x2="158" y2="222" stroke="#94a3b8" stroke-width="1.5"/>
            <line x1="124" y1="229" x2="150" y2="229" stroke="#94a3b8" stroke-width="1.5"/>
          </g>

          <!-- Leher -->
          <rect x="135" y="145" width="14" height="18" rx="5" fill="#f5c484"/>

          <!-- Kepala -->
          <g class="head-nod" style="transform-origin:142px 135px;">
            <ellipse cx="142" cy="128" rx="22" ry="20" fill="#1e293b"/>
            <ellipse cx="142" cy="130" rx="19" ry="20" fill="#fcd9a0"/>
            <path d="M122 122 Q130 105 142 108 Q154 105 162 122 Q155 112 142 114 Q129 112 122 122" fill="#1e293b"/>
            <ellipse cx="123" cy="132" rx="4" ry="5" fill="#f5c484"/>
            <ellipse cx="161" cy="132" rx="4" ry="5" fill="#f5c484"/>
            <g class="eye-blink" style="transform-origin:135px 136px;">
              <ellipse cx="135" cy="136" rx="4" ry="3" fill="#1e293b"/>
              <circle cx="136" cy="135" r="1" fill="white" opacity=".7"/>
            </g>
            <g class="eye-blink" style="transform-origin:149px 136px;">
              <ellipse cx="149" cy="136" rx="4" ry="3" fill="#1e293b"/>
              <circle cx="150" cy="135" r="1" fill="white" opacity=".7"/>
            </g>
            <path d="M130 128 Q135 125 139 127" stroke="#1e293b" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <path d="M145 127 Q149 125 154 128" stroke="#1e293b" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <path d="M141 138 Q140 143 143 143" stroke="#d4956a" stroke-width="1" fill="none" stroke-linecap="round"/>
            <path d="M136 148 Q142 152 148 148" stroke="#c47a50" stroke-width="1.5" fill="none" stroke-linecap="round"/>
          </g>

          <!-- Partikel melayang -->
          <circle cx="95" cy="170" r="3" fill="#3b82f6" opacity=".5" class="particle"/>
          <circle cx="88" cy="155" r="2" fill="#60a5fa" opacity=".4" class="particle p2"/>
          <circle cx="100" cy="145" r="2.5" fill="#93c5fd" opacity=".35" class="particle p3"/>

          <!-- Cangkir kopi -->
          <rect x="210" y="190" width="18" height="14" rx="3" fill="#0f2544"/>
          <path d="M228 194 Q234 194 234 200 Q234 206 228 206" stroke="#0f2544" stroke-width="3" fill="none"/>
          <path d="M214 188 Q216 183 214 178" stroke="#334155" stroke-width="1.5" fill="none" stroke-linecap="round" opacity=".6" class="particle"/>
          <path d="M219 187 Q221 181 219 176" stroke="#334155" stroke-width="1.5" fill="none" stroke-linecap="round" opacity=".5" class="particle p2"/>
        </svg>
      </div>
    </div>

    <div class="left-bottom">
      <div class="lb-icon">📖</div>
      <div>
        <p>Ribuan buku, pengetahuan tanpa batas.</p>
        <span>Akses kapan saja, di mana saja.</span>
      </div>
    </div>
  </div>

  <!-- ===== RIGHT PANEL ===== -->
  <div class="right-panel">
    <div class="right-inner">

      <div class="right-header">
        <h1>Login</h1>
        <p>Silakan masuk untuk melanjutkan</p>
      </div>

      <?php if(isset($_GET['error'])): ?>
      <div class="alert alert-danger">⚠️ Username atau password salah.</div>
      <?php endif; ?>
      <?php if(isset($_GET['logout'])): ?>
      <div class="alert alert-success">✅ Anda berhasil logout.</div>
      <?php endif; ?>
      <?php if(isset($_GET['registered'])): ?>
      <div class="alert alert-success">✅ Akun berhasil dibuat! Silakan login.</div>
      <?php endif; ?>

      <form method="POST" action="login.php" id="loginForm">
        <div class="form-group">
          <label class="form-label">Username</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input type="text" name="username" class="form-control"
              placeholder="Masukkan username" required autofocus>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" name="password" id="pwdInput" class="form-control"
              placeholder="Masukkan password" required>
            <span class="input-eye" id="eyeToggle">👁️</span>
          </div>
        </div>

        <div class="forgot-wrap">
          <a class="forgot-link" onclick="document.getElementById('modalLupa').classList.add('show')">
            Lupa password?
          </a>
        </div>

        <button type="submit" class="btn-login" id="loginBtn">
          <div class="btn-spinner" style="width:16px;height:16px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;display:none;animation:spinRing .7s linear infinite;flex-shrink:0;"></div>
          <span class="btn-text">Masuk</span>
        </button>
      </form>

      <div class="register-link">
        Belum punya akun? <a href="register.php">Daftar di sini</a>
      </div>

    </div>
  </div>
</div>

<div class="login-footer-bar">
  🛡️ Aman &amp; Terpercaya &nbsp;·&nbsp; Data Anda kami jaga kerahasiaannya.
</div>

<script>
document.getElementById('eyeToggle').addEventListener('click', function(){
  const pwd = document.getElementById('pwdInput');
  pwd.type = pwd.type === 'password' ? 'text' : 'password';
  this.textContent = pwd.type === 'password' ? '👁️' : '🙈';
});

const form    = document.getElementById('loginForm');
const btn     = document.getElementById('loginBtn');
const overlay = document.getElementById('loadingOverlay');
const texts   = ['Memverifikasi akun...','Memeriksa kredensial...','Menyiapkan dashboard...'];
let txtIdx = 0;

form.addEventListener('submit', function(){
  btn.classList.add('loading');
  btn.querySelector('.btn-spinner').style.display = 'block';
  btn.querySelector('.btn-text').textContent = 'Memproses...';
  setTimeout(() => {
    overlay.classList.add('show');
    setInterval(() => {
      txtIdx = (txtIdx + 1) % texts.length;
      const el = overlay.querySelector('.loading-text');
      el.style.opacity = '0';
      setTimeout(() => { el.textContent = texts[txtIdx]; el.style.opacity = ''; }, 300);
    }, 1200);
  }, 300);
});
</script>
</body>
</html>