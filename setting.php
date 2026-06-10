<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }
if ($_SESSION['role'] !== 'superadmin') { header("Location: dashboard.php"); exit; }

$msg      = '';
$msg_type = '';

// UPDATE SETTING
if (isset($_POST['update_setting'])) {
    $nama        = trim($_POST['nama_perpustakaan']);
    $alamat      = trim($_POST['alamat']);
    $telepon     = trim($_POST['no_telepon']);
    $email       = trim($_POST['email']);
    $website     = trim($_POST['website']);
    $keterangan  = trim($_POST['keterangan']);

    if ($nama) {
        $stmt = $conn->prepare("UPDATE settings SET nama_perpustakaan=?, alamat=?, no_telepon=?, email=?, website=?, keterangan=? WHERE id=1");
        $stmt->bind_param("ssssss", $nama, $alamat, $telepon, $email, $website, $keterangan);
        if ($stmt->execute()) {
            $msg      = "✅ Setting berhasil disimpan!";
            $msg_type = "success";
        } else {
            $msg      = "❌ Gagal menyimpan setting.";
            $msg_type = "danger";
        }
    } else {
        $msg      = "⚠️ Nama perpustakaan wajib diisi.";
        $msg_type = "warning";
    }
}

// Ambil data setting
$stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
$stmt->execute();
$setting = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Setting — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h2>Setting</h2>
        <p>Pengaturan sistem perpustakaan</p>
      </div>
      <div class="topbar-right">
        <div class="topbar-user">
          <div class="avatar"><?= strtoupper(substr($_SESSION['username']??'A',0,1)) ?></div>
          <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
        </div>
      </div>
    </div>

    <div class="page-content">
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span class="sep">/</span>
        <span>Setting</span>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg_type) ?>">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start;">

        <!-- FORM SETTING -->
        <div class="card">
          <div class="card-header"><h3>⚙️ Informasi Perpustakaan</h3></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label class="form-label">Nama Perpustakaan <span style="color:red;">*</span></label>
                <input type="text" name="nama_perpustakaan" class="form-control" maxlength="100"
                       value="<?= htmlspecialchars($setting['nama_perpustakaan'] ?? '') ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="3"
                          placeholder="Alamat lengkap perpustakaan"><?= htmlspecialchars($setting['alamat'] ?? '') ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="no_telepon" class="form-control" maxlength="20"
                       placeholder="Contoh: 08123456789"
                       value="<?= htmlspecialchars($setting['no_telepon'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" maxlength="100"
                       placeholder="Contoh: perpustakaan@kampus.ac.id"
                       value="<?= htmlspecialchars($setting['email'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Website</label>
                <input type="text" name="website" class="form-control" maxlength="100"
                       placeholder="Contoh: www.kampus.ac.id"
                       value="<?= htmlspecialchars($setting['website'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3"
                          placeholder="Keterangan tambahan"><?= htmlspecialchars($setting['keterangan'] ?? '') ?></textarea>
              </div>
              <button name="update_setting" type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                💾 Simpan Setting
              </button>
            </form>
          </div>
        </div>

        <!-- PREVIEW INFO KONTAK -->
        <div class="card">
          <div class="card-header"><h3>📋 Preview Info Kontak</h3></div>
          <div class="card-body">
            <div style="background:#f8f8ff; border-radius:12px; padding:24px; border:1px solid #e0e0f0;">
              <div style="text-align:center; margin-bottom:20px;">
                <div style="font-size:2.5rem;">📚</div>
                <h3 style="margin:8px 0 4px; color:#6c63ff;"><?= htmlspecialchars($setting['nama_perpustakaan'] ?? '-') ?></h3>
                <p style="color:#888; font-size:0.9rem;"><?= htmlspecialchars($setting['keterangan'] ?? '') ?></p>
              </div>
              <hr style="border:none; border-top:1px solid #e0e0f0; margin:16px 0;">
              <div style="display:flex; flex-direction:column; gap:12px;">
                <?php if (!empty($setting['alamat'])): ?>
                <div style="display:flex; gap:10px; align-items:flex-start;">
                  <span style="font-size:1.2rem;">📍</span>
                  <div>
                    <small style="color:#888; display:block;">Alamat</small>
                    <span><?= htmlspecialchars($setting['alamat']) ?></span>
                  </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($setting['no_telepon'])): ?>
                <div style="display:flex; gap:10px; align-items:center;">
                  <span style="font-size:1.2rem;">📞</span>
                  <div>
                    <small style="color:#888; display:block;">Telepon</small>
                    <span><?= htmlspecialchars($setting['no_telepon']) ?></span>
                  </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($setting['email'])): ?>
                <div style="display:flex; gap:10px; align-items:center;">
                  <span style="font-size:1.2rem;">✉️</span>
                  <div>
                    <small style="color:#888; display:block;">Email</small>
                    <span><?= htmlspecialchars($setting['email']) ?></span>
                  </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($setting['website'])): ?>
                <div style="display:flex; gap:10px; align-items:center;">
                  <span style="font-size:1.2rem;">🌐</span>
                  <div>
                    <small style="color:#888; display:block;">Website</small>
                    <span><?= htmlspecialchars($setting['website']) ?></span>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
</body>
</html>