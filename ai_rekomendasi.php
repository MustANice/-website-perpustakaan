<?php
session_start();
include 'koneksi.php';
include 'functions.php';
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

$id_user = $_SESSION['id_user'];
$notif_list  = get_notifikasi($conn, 8);
$notif_count = count_notifikasi_unread($conn);

// Ambil riwayat peminjaman user ini
$riwayat = mysqli_query($conn,
    "SELECT b.judul, b.penulis, b.kategori
     FROM peminjaman p
     JOIN buku b ON p.id_buku = b.id
     WHERE p.id_user = $id_user
     ORDER BY p.id DESC
     LIMIT 10"
);

$buku_dipinjam = [];
while($r = mysqli_fetch_assoc($riwayat)){
    $buku_dipinjam[] = $r;
}

// Ambil semua buku yang tersedia
$semua_buku = mysqli_query($conn, "SELECT * FROM buku WHERE stok > 0 ORDER BY judul ASC");
$list_buku = [];
while($b = mysqli_fetch_assoc($semua_buku)){
    $list_buku[] = $b;
}

$rekomendasi = null;
$error = null;

// Proses rekomendasi AI
if(isset($_POST['minta_rekomendasi'])){
    if(empty($buku_dipinjam)){
        $error = "Kamu belum pernah meminjam buku. Pinjam beberapa buku dulu agar AI bisa memberikan rekomendasi!";
    } else {
        // Susun prompt untuk AI
        $riwayat_text = "";
        foreach($buku_dipinjam as $b){
            $riwayat_text .= "- {$b['judul']} karya {$b['penulis']} (kategori: {$b['kategori']})\n";
        }

        $koleksi_text = "";
        foreach($list_buku as $b){
            $koleksi_text .= "- ID:{$b['id']} | {$b['judul']} karya {$b['penulis']} (kategori: {$b['kategori']}, stok: {$b['stok']})\n";
        }

        $prompt = "Kamu adalah asisten perpustakaan kampus yang cerdas.

Berikut adalah riwayat peminjaman buku oleh pengguna:
$riwayat_text

Berikut adalah koleksi buku yang tersedia di perpustakaan:
$koleksi_text

Berdasarkan riwayat peminjaman di atas, rekomendasikan 3 buku dari koleksi yang tersedia yang paling relevan untuk pengguna ini. 

Berikan respons dalam format JSON seperti ini (tanpa markdown, langsung JSON):
{
  \"rekomendasi\": [
    {
      \"id\": \"ID buku\",
      \"judul\": \"judul buku\",
      \"penulis\": \"nama penulis\",
      \"kategori\": \"kategori\",
      \"alasan\": \"alasan rekomendasi dalam 1-2 kalimat\"
    }
  ],
  \"kesimpulan\": \"kesimpulan singkat tentang minat baca pengguna\"
}";

        // Panggil Anthropic API
        $api_key = ""; // ← ganti dengan API key kamu
        $response = file_get_contents('https://api.anthropic.com/v1/messages', false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => implode("\r\n", [
                    'Content-Type: application/json',
                    'x-api-key: ' . $api_key,
                    'anthropic-version: 2023-06-01'
                ]),
                'content' => json_encode([
                    'model'      => 'claude-sonnet-4-20250514',
                    'max_tokens' => 1000,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ]),
                'ignore_errors' => true
            ]
        ]));

        if($response === false){
            $error = "Gagal menghubungi AI. Periksa koneksi internet.";
        } else {
            $data = json_decode($response, true);
            if(isset($data['content'][0]['text'])){
                $text = $data['content'][0]['text'];
                $parsed = json_decode($text, true);
                if($parsed && isset($parsed['rekomendasi'])){
                    $rekomendasi = $parsed;
                    // Log aktivitas
                    log_aktivitas($conn, $id_user, $_SESSION['username'],
                        'AI Rekomendasi', 'Meminta rekomendasi buku dari AI');
                } else {
                    $error = "AI memberikan respons tidak terduga. Coba lagi.";
                }
            } else {
                $error = "Error API: " . ($data['error']['message'] ?? 'Unknown error');
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
  <title>Rekomendasi Buku AI — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* AI Card */
    .ai-banner{
      background:linear-gradient(135deg,#1d4ed8,#4f46e5,#7c3aed);
      border-radius:16px;padding:28px 32px;
      margin-bottom:24px;color:#fff;
      display:flex;align-items:center;
      justify-content:space-between;
      position:relative;overflow:hidden;
    }
    .ai-banner::before{
      content:'';position:absolute;
      width:300px;height:300px;
      background:radial-gradient(circle,rgba(255,255,255,.1) 0%,transparent 70%);
      top:-80px;right:-80px;
    }
    .ai-banner h2{font-size:22px;font-weight:800;margin-bottom:6px;}
    .ai-banner p{font-size:13px;opacity:.8;line-height:1.6;}
    .ai-icon{font-size:60px;opacity:.3;position:relative;z-index:1;}

    /* Riwayat chips */
    .chip-wrap{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;}
    .chip{
      padding:5px 12px;background:rgba(255,255,255,.1);
      border:1px solid rgba(255,255,255,.2);
      border-radius:20px;font-size:12px;color:#fff;
      backdrop-filter:blur(4px);
    }

    /* Tombol AI */
    .btn-ai{
      width:100%;padding:14px;
      background:linear-gradient(135deg,#1d4ed8,#4f46e5);
      color:#fff;border:none;border-radius:12px;
      font-family:'Plus Jakarta Sans',sans-serif;
      font-size:15px;font-weight:700;cursor:pointer;
      transition:.2s;display:flex;align-items:center;
      justify-content:center;gap:10px;
      box-shadow:0 4px 15px rgba(29,78,216,.3);
      position:relative;overflow:hidden;
    }
    .btn-ai:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(29,78,216,.4);}
    .btn-ai:disabled{opacity:.7;pointer-events:none;}

    /* Loading AI */
    .ai-loading{
      display:none;flex-direction:column;
      align-items:center;justify-content:center;
      gap:16px;padding:40px;text-align:center;
    }
    .ai-loading.show{display:flex;}
    .ai-spinner{
      width:48px;height:48px;border-radius:50%;
      border:3px solid #e2e8f0;
      border-top-color:#1d4ed8;
      animation:spin 1s linear infinite;
    }
    @keyframes spin{to{transform:rotate(360deg);}}
    .ai-loading p{font-size:14px;color:#64748b;font-weight:500;}
    .ai-loading-dots span{
      display:inline-block;width:6px;height:6px;
      background:#1d4ed8;border-radius:50%;margin:0 3px;
      animation:dotBounce .8s ease-in-out infinite;
    }
    .ai-loading-dots span:nth-child(2){animation-delay:.15s;}
    .ai-loading-dots span:nth-child(3){animation-delay:.3s;}
    @keyframes dotBounce{0%,100%{transform:translateY(0);}50%{transform:translateY(-8px);}}

    /* Rekomendasi card */
    .rekomen-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:8px;}
    .rekomen-card{
      background:#fff;border:1px solid #e2e8f0;
      border-radius:14px;padding:20px;
      transition:.2s;position:relative;overflow:hidden;
    }
    .rekomen-card:hover{transform:translateY(-3px);box-shadow:0 12px 24px rgba(0,0,0,.1);}
    .rekomen-card::before{
      content:'';position:absolute;top:0;left:0;right:0;
      height:4px;background:linear-gradient(90deg,#1d4ed8,#4f46e5);
    }
    .rekomen-num{
      width:28px;height:28px;background:#eff6ff;
      border-radius:8px;display:flex;align-items:center;
      justify-content:center;font-size:13px;font-weight:700;
      color:#1d4ed8;margin-bottom:12px;
    }
    .rekomen-judul{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:4px;line-height:1.4;}
    .rekomen-penulis{font-size:12px;color:#64748b;margin-bottom:8px;}
    .rekomen-alasan{
      font-size:12.5px;color:#475569;line-height:1.6;
      background:#f8fafc;border-radius:8px;padding:10px;
      border-left:3px solid #1d4ed8;margin-top:10px;
    }
    .rekomen-badge{
      display:inline-flex;align-items:center;gap:4px;
      padding:3px 10px;background:#eff6ff;color:#1d4ed8;
      border-radius:20px;font-size:11px;font-weight:600;margin-bottom:8px;
    }

    /* Kesimpulan */
    .kesimpulan-box{
      background:linear-gradient(135deg,#eff6ff,#eef2ff);
      border:1px solid #bfdbfe;border-radius:14px;
      padding:18px 22px;margin-top:20px;
      display:flex;gap:12px;align-items:flex-start;
    }
    .kesimpulan-box .ks-icon{font-size:24px;flex-shrink:0;}
    .kesimpulan-box h4{font-size:13px;font-weight:700;color:#1d4ed8;margin-bottom:4px;}
    .kesimpulan-box p{font-size:13px;color:#475569;line-height:1.6;}

    /* Pinjam button di card */
    .btn-pinjam-card{
      width:100%;margin-top:12px;padding:8px;
      background:#1d4ed8;color:#fff;border:none;
      border-radius:8px;font-family:'Plus Jakarta Sans',sans-serif;
      font-size:12.5px;font-weight:600;cursor:pointer;
      text-decoration:none;display:block;text-align:center;
      transition:.2s;
    }
    .btn-pinjam-card:hover{background:#1e40af;}

    body.dark-mode .rekomen-card{background:#1e293b;border-color:#334155;}
    body.dark-mode .rekomen-judul{color:#e2e8f0;}
    body.dark-mode .rekomen-alasan{background:#0f172a;}
    body.dark-mode .kesimpulan-box{background:#1e293b;border-color:#334155;}
  </style>
</head>
<body>

<div class="app">
  <?php include 'sidebar.php'; ?>

  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h2>🤖 Rekomendasi AI</h2>
        <p>Rekomendasi buku personal berdasarkan riwayat kamu</p>
      </div>
      <div class="topbar-right">
        <div class="dark-toggle" onclick="toggleDarkMode()" title="Toggle dark mode">
          <span id="darkIcon"><?= ($_SESSION['dark_mode']??0) ? '☀️' : '🌙' ?></span>
        </div>
        <div class="notif-wrap">
          <div class="notif-btn" onclick="toggleNotif(event)">
            🔔
            <?php if($notif_count > 0): ?>
            <div class="notif-badge"><?= $notif_count > 9 ? '9+' : $notif_count ?></div>
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
                  <span><?= htmlspecialchars($n['pesan']) ?></span>
                </div>
              </div>
              <?php endforeach; endif; ?>
            </div>
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
        <a href="dashboard.php">Dashboard</a>
        <span class="sep">/</span>
        <span>Rekomendasi AI</span>
      </div>

      <!-- AI Banner -->
      <div class="ai-banner">
        <div style="position:relative;z-index:1;">
          <h2>✨ Rekomendasi Buku Cerdas</h2>
          <p>AI menganalisis riwayat peminjaman kamu untuk<br>menemukan buku yang paling sesuai minat kamu.</p>
          <?php if(!empty($buku_dipinjam)): ?>
          <div class="chip-wrap">
            <?php foreach(array_slice($buku_dipinjam,0,4) as $b): ?>
            <div class="chip">📖 <?= htmlspecialchars($b['judul']) ?></div>
            <?php endforeach; ?>
            <?php if(count($buku_dipinjam)>4): ?>
            <div class="chip">+<?= count($buku_dipinjam)-4 ?> lainnya</div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="ai-icon">🤖</div>
      </div>

      <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

        <!-- Panel kiri: Form + Riwayat -->
        <div style="display:flex;flex-direction:column;gap:16px;">

          <!-- Tombol minta rekomendasi -->
          <div class="card">
            <div class="card-header"><h3>🎯 Minta Rekomendasi</h3></div>
            <div class="card-body">
              <?php if(empty($buku_dipinjam)): ?>
              <div class="alert alert-warning">
                ⚠️ Kamu belum punya riwayat peminjaman. Pinjam buku dulu!
              </div>
              <a href="pinjam.php" class="btn btn-primary w-full" style="justify-content:center;">
                📖 Pinjam Buku Sekarang
              </a>
              <?php else: ?>
              <p style="font-size:13px;color:#64748b;margin-bottom:16px;line-height:1.6;">
                AI akan menganalisis <strong><?= count($buku_dipinjam) ?> buku</strong> yang pernah kamu pinjam dan merekomendasikan buku terbaik untukmu.
              </p>
              <form method="POST" id="aiForm">
                <button type="submit" name="minta_rekomendasi"
                  class="btn-ai" id="btnAI">
                  ✨ Dapatkan Rekomendasi AI
                </button>
              </form>
              <?php endif; ?>
            </div>
          </div>

          <!-- Riwayat peminjaman -->
          <div class="card">
            <div class="card-header"><h3>📚 Riwayat Pinjam</h3></div>
            <div style="padding:0;">
              <?php if(empty($buku_dipinjam)): ?>
              <div class="empty-state" style="padding:24px;">
                <p>Belum ada riwayat</p>
              </div>
              <?php else: ?>
              <?php foreach($buku_dipinjam as $b): ?>
              <div style="padding:10px 16px;border-bottom:1px solid #f1f5f9;display:flex;gap:10px;align-items:center;">
                <div style="width:32px;height:32px;background:#eff6ff;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">📖</div>
                <div>
                  <p style="font-size:12.5px;font-weight:600;color:#0f172a;"><?= htmlspecialchars($b['judul']) ?></p>
                  <p style="font-size:11px;color:#64748b;"><?= htmlspecialchars($b['penulis']) ?></p>
                </div>
              </div>
              <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <!-- Panel kanan: Hasil rekomendasi -->
        <div>

          <!-- Loading state -->
          <div class="card">
            <div class="ai-loading" id="aiLoading">
              <div class="ai-spinner"></div>
              <p>AI sedang menganalisis minat baca kamu...</p>
              <div class="ai-loading-dots">
                <span></span><span></span><span></span>
              </div>
            </div>

            <?php if($error): ?>
            <div class="card-body">
              <div class="alert alert-danger">❌ <?= htmlspecialchars($error) ?></div>
            </div>

            <?php elseif($rekomendasi): ?>
            <div class="card-header">
              <h3>✨ Hasil Rekomendasi AI</h3>
              <span class="badge badge-blue">3 Buku Terpilih</span>
            </div>
            <div class="card-body">
              <div class="rekomen-grid">
                <?php foreach($rekomendasi['rekomendasi'] as $i => $r): ?>
                <div class="rekomen-card">
                  <div class="rekomen-num"><?= $i+1 ?></div>
                  <div class="rekomen-badge">🏷️ <?= htmlspecialchars($r['kategori']) ?></div>
                  <div class="rekomen-judul"><?= htmlspecialchars($r['judul']) ?></div>
                  <div class="rekomen-penulis">✍️ <?= htmlspecialchars($r['penulis']) ?></div>
                  <div class="rekomen-alasan">
                    💡 <?= htmlspecialchars($r['alasan']) ?>
                  </div>
                  <a href="pinjam.php" class="btn-pinjam-card">📋 Pinjam Buku Ini</a>
                </div>
                <?php endforeach; ?>
              </div>

              <?php if(isset($rekomendasi['kesimpulan'])): ?>
              <div class="kesimpulan-box">
                <div class="ks-icon">🧠</div>
                <div>
                  <h4>Analisis Minat Baca Kamu</h4>
                  <p><?= htmlspecialchars($rekomendasi['kesimpulan']) ?></p>
                </div>
              </div>
              <?php endif; ?>

              <!-- Minta lagi -->
              <form method="POST" style="margin-top:16px;">
                <button type="submit" name="minta_rekomendasi" class="btn btn-outline w-full" style="justify-content:center;">
                  🔄 Minta Rekomendasi Ulang
                </button>
              </form>
            </div>

            <?php else: ?>
            <div class="card-body">
              <div class="empty-state" style="padding:60px 24px;">
                <div class="empty-icon">🤖</div>
                <p style="font-weight:600;font-size:15px;margin-bottom:8px;">Belum ada rekomendasi</p>
                <p>Klik tombol <strong>"Dapatkan Rekomendasi AI"</strong> di sebelah kiri untuk memulai.</p>
              </div>
            </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('aiForm')?.addEventListener('submit', function(){
  document.getElementById('btnAI').disabled = true;
  document.getElementById('btnAI').innerHTML = '⏳ Memproses...';
  document.getElementById('aiLoading').classList.add('show');
});

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
(function(){
  if(<?= $_SESSION['dark_mode']??0 ?>) document.body.classList.add('dark-mode');
})();
</script>
</body>
</html>