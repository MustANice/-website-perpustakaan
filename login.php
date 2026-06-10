<?php
session_start();
include 'koneksi.php';
include 'functions.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: index.php"); exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if(empty($username) || empty($password)){
    header("Location: index.php?error=1"); exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 1){
    $user = $result->fetch_assoc();
    $valid = false;
    if(password_verify($password, $user['password'])) $valid = true;
    elseif($password === $user['password']) $valid = true;

    if($valid){
        session_regenerate_id(true);
        $_SESSION['login']    = true;
        $_SESSION['username'] = htmlspecialchars($user['username']);
        $_SESSION['nama']     = htmlspecialchars($user['nama']);
        $_SESSION['role']     = $user['role'];
        $_SESSION['id_user']  = $user['id'];
        $_SESSION['dark_mode'] = get_dark_mode($conn, $user['id']);

        // Log aktivitas login
        log_aktivitas($conn, $user['id'], $user['username'], 'Login', 'Login berhasil');

        // Cek notifikasi otomatis
        cek_denda_otomatis($conn);
        cek_stok_rendah($conn);

        // Halaman transisi animasi
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Memuat — Perpustakaan Kampus</title>
          <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
          <style>
            *{margin:0;padding:0;box-sizing:border-box;}
            body{font-family:'Plus Jakarta Sans',sans-serif;background:#070e1a;min-height:100vh;display:flex;align-items:center;justify-content:center;overflow:hidden;}
            .stars{position:fixed;inset:0;pointer-events:none;}
            .star{position:absolute;border-radius:50%;background:#e2e8f0;animation:twinkle 3s ease-in-out infinite;}
            @keyframes twinkle{0%,100%{opacity:.2;}50%{opacity:.8;}}
            .load-container{display:flex;flex-direction:column;align-items:center;gap:28px;animation:containerIn .6s ease forwards;}
            @keyframes containerIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
            .brand{display:flex;align-items:center;gap:12px;margin-bottom:8px;}
            .brand-ico{width:44px;height:44px;background:#1d4ed8;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 0 20px rgba(29,78,216,.4);animation:iconPulse 2s ease-in-out infinite;}
            @keyframes iconPulse{0%,100%{box-shadow:0 0 20px rgba(29,78,216,.4);}50%{box-shadow:0 0 35px rgba(29,78,216,.7);}}
            .brand-text h2{font-size:16px;font-weight:700;color:#e2e8f0;}
            .brand-text p{font-size:12px;color:#475569;margin-top:2px;}
            .book-wrap{position:relative;width:80px;height:96px;}
            .book-cover{position:absolute;inset:0;background:linear-gradient(135deg,#1e40af,#1d4ed8);border-radius:4px 10px 10px 4px;transform-origin:left center;animation:coverOpen 2.5s ease-in-out infinite;box-shadow:0 8px 24px rgba(29,78,216,.3);}
            @keyframes coverOpen{0%,10%{transform:perspective(300px) rotateY(0deg);}45%,90%{transform:perspective(300px) rotateY(-42deg);}100%{transform:perspective(300px) rotateY(0deg);}}
            .book-spine{position:absolute;left:0;top:0;width:8px;height:100%;background:#1e3a8a;border-radius:4px 0 0 4px;z-index:2;}
            .book-pages{position:absolute;inset:3px 0 3px 6px;background:#f8fafc;border-radius:0 8px 8px 0;display:flex;flex-direction:column;justify-content:center;gap:6px;padding:10px 12px;animation:pagesReveal 2.5s ease-in-out infinite;}
            @keyframes pagesReveal{0%,15%{opacity:0;}40%,85%{opacity:1;}100%{opacity:0;}}
            .page-line{height:2.5px;background:#cbd5e1;border-radius:2px;}
            .page-line.short{width:65%;}
            .page-line.med{width:80%;}
            .book-shine{position:absolute;top:8px;right:10px;width:5px;height:24px;background:rgba(255,255,255,.2);border-radius:3px;transform:rotate(15deg);}
            .bar-section{width:240px;text-align:center;}
            .bar-label{font-size:13px;font-weight:600;color:#3b82f6;margin-bottom:10px;letter-spacing:.03em;min-height:20px;}
            .bar-outer{height:4px;background:#0f2544;border-radius:2px;overflow:hidden;margin-bottom:10px;}
            .bar-inner{height:100%;background:linear-gradient(90deg,#1d4ed8,#3b82f6,#60a5fa);border-radius:2px;width:0%;transition:width .1s ease;}
            .bar-pct{font-size:12px;color:#334155;font-weight:600;}
            .dots{display:flex;gap:8px;justify-content:center;}
            .dot{width:7px;height:7px;border-radius:50%;animation:dotBounce .9s ease-in-out infinite;}
            .dot:nth-child(1){background:#1d4ed8;animation-delay:0s;}
            .dot:nth-child(2){background:#3b82f6;animation-delay:.15s;}
            .dot:nth-child(3){background:#60a5fa;animation-delay:.3s;}
            @keyframes dotBounce{0%,100%{transform:translateY(0);opacity:.4;}50%{transform:translateY(-10px);opacity:1;}}
            .dash-wrap{width:280px;border-radius:14px;overflow:hidden;border:1px solid #0f2544;opacity:0;transform:translateY(20px);transition:opacity .5s ease,transform .5s ease;box-shadow:0 20px 40px rgba(0,0,0,.4);}
            .dash-wrap.show{opacity:1;transform:translateY(0);}
            .dash-topbar{height:36px;background:#050d1a;display:flex;align-items:center;gap:8px;padding:0 14px;border-bottom:1px solid #0f2544;}
            .dash-dot{width:8px;height:8px;border-radius:50%;}
            .dash-title{font-size:11px;font-weight:600;color:#334155;margin-left:4px;}
            .dash-body{display:grid;grid-template-columns:56px 1fr;background:#0a1628;min-height:120px;}
            .dash-sidebar{background:#050d1a;padding:12px 8px;display:flex;flex-direction:column;gap:7px;border-right:1px solid #0f2544;}
            .dash-nav{height:7px;background:#0f2544;border-radius:3px;}
            .dash-nav.active{background:#1d4ed8;}
            .dash-main{padding:12px;display:flex;flex-direction:column;gap:8px;}
            .dash-cards{display:grid;grid-template-columns:1fr 1fr;gap:6px;}
            .dash-card{height:26px;background:#0f2544;border-radius:5px;}
            .dash-card.blue{background:#1d3a6e;}
            .dash-row{height:6px;background:#0f2544;border-radius:3px;}
            .dash-row.w70{width:70%;}
            .dash-row.w50{width:50%;}
            body.exit{animation:exitAnim .5s ease forwards;}
            @keyframes exitAnim{from{opacity:1;transform:scale(1);}to{opacity:0;transform:scale(1.03);}}
          </style>
        </head>
        <body>
        <div class="stars" id="stars"></div>
        <div class="load-container">
          <div class="brand">
            <div class="brand-ico">📚</div>
            <div class="brand-text">
              <h2>Perpustakaan Kampus</h2>
              <p>Selamat datang, <?= htmlspecialchars($user['nama']) ?>!</p>
            </div>
          </div>
          <div class="book-wrap">
            <div class="book-spine"></div>
            <div class="book-cover"><div class="book-shine"></div></div>
            <div class="book-pages">
              <div class="page-line med"></div>
              <div class="page-line short"></div>
              <div class="page-line"></div>
              <div class="page-line short"></div>
              <div class="page-line med"></div>
            </div>
          </div>
          <div class="bar-section">
            <div class="bar-label" id="barLabel">Memverifikasi akun...</div>
            <div class="bar-outer"><div class="bar-inner" id="barInner"></div></div>
            <div class="bar-pct" id="barPct">0%</div>
          </div>
          <div class="dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
          <div class="dash-wrap" id="dashPreview">
            <div class="dash-topbar">
              <div class="dash-dot" style="background:#1d4ed8;"></div>
              <div class="dash-dot" style="background:#0f2544;"></div>
              <div class="dash-title">Dashboard — Perpustakaan Kampus</div>
            </div>
            <div class="dash-body">
              <div class="dash-sidebar">
                <div class="dash-nav active"></div>
                <div class="dash-nav"></div>
                <div class="dash-nav"></div>
                <div class="dash-nav"></div>
              </div>
              <div class="dash-main">
                <div class="dash-row w70"></div>
                <div class="dash-cards">
                  <div class="dash-card blue"></div>
                  <div class="dash-card"></div>
                  <div class="dash-card"></div>
                  <div class="dash-card blue"></div>
                </div>
                <div class="dash-row"></div>
                <div class="dash-row w50"></div>
              </div>
            </div>
          </div>
        </div>
        <script>
        const starsEl = document.getElementById('stars');
        for(let i=0;i<60;i++){
          const s=document.createElement('div');
          s.className='star';
          const size=Math.random()*2+.5;
          s.style.cssText=`width:${size}px;height:${size}px;top:${Math.random()*100}%;left:${Math.random()*100}%;animation-delay:${Math.random()*3}s;animation-duration:${2+Math.random()*2}s;`;
          starsEl.appendChild(s);
        }
        const bar=document.getElementById('barInner');
        const pct=document.getElementById('barPct');
        const label=document.getElementById('barLabel');
        const dash=document.getElementById('dashPreview');
        const steps=[
          {target:20,msg:'Memverifikasi akun...'},
          {target:45,msg:'Memuat data perpustakaan...'},
          {target:70,msg:'Menyiapkan dashboard...'},
          {target:90,msg:'Hampir selesai...'},
          {target:100,msg:'Selamat datang! 🎉'},
        ];
        let current=0,progress=0;
        function runStep(){
          if(current>=steps.length)return;
          const step=steps[current];
          label.textContent=step.msg;
          const interval=setInterval(()=>{
            progress++;
            bar.style.width=progress+'%';
            pct.textContent=progress+'%';
            if(progress>=step.target){
              clearInterval(interval);
              current++;
              if(progress>=90) dash.classList.add('show');
              if(progress>=100){
                setTimeout(()=>{
                  document.body.classList.add('exit');
                  setTimeout(()=>{ window.location.href='dashboard.php'; },500);
                },600);
                return;
              }
              setTimeout(runStep,300);
            }
          },18);
        }
        setTimeout(runStep,700);
        </script>
        </body>
        </html>
        <?php
        exit;
    }
}

// Log gagal login
log_aktivitas($conn, 0, $username, 'Login Gagal', 'Username/password salah');
header("Location: index.php?error=1");
exit;
?>