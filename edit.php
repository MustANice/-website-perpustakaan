<<?php
session_start();
include 'koneksi.php';
include 'functions.php';
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

// ✅ CEK ROLE
$role = $_SESSION['role'] ?? 'user';
if(!in_array($role, ['admin', 'superadmin'])){
    header("Location: buku.php?error=forbidden"); exit;
}

$id=(int)($_GET['id']??0);
if($id<=0){ header("Location: buku.php"); exit; }

$stmt=$conn->prepare("SELECT * FROM buku WHERE id=?");
$stmt->bind_param("i",$id); $stmt->execute();
$result=$stmt->get_result();
if($result->num_rows===0){ header("Location: buku.php"); exit; }
$d=$result->fetch_assoc();
$msg='';

if(isset($_POST['update'])){
    $judul=trim($_POST['judul']); $penulis=trim($_POST['penulis']); $stok=(int)$_POST['stok'];
    if($judul && $penulis && $stok>=0){
        $stmt=$conn->prepare("UPDATE buku SET judul=?,penulis=?,stok=? WHERE id=?");
        $stmt->bind_param("ssii",$judul,$penulis,$stok,$id);
        if($stmt->execute()){ header("Location: buku.php?msg=updated"); exit; }
        else { $msg="❌ Gagal mengupdate."; }
    } else { $msg="⚠️ Semua kolom wajib diisi."; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Edit Buku — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-left"><h2>Edit Buku</h2><p>Ubah informasi buku</p></div>
      <div class="topbar-right"><div class="topbar-user"><div class="avatar">A</div><?= htmlspecialchars($_SESSION['username']??'Admin') ?></div></div>
    </div>
    <div class="page-content">
      <div class="breadcrumb"><a href="dashboard.php">Dashboard</a><span class="sep">/</span><a href="buku.php">Data Buku</a><span class="sep">/</span><span>Edit</span></div>
      <div style="max-width:520px;">
        <div class="card">
          <div class="card-header"><h3>✏️ Edit Data Buku</h3><a href="buku.php" class="btn btn-outline btn-sm">← Kembali</a></div>
          <div class="card-body">
            <?php if($msg): ?><div class="alert alert-warning"><?= $msg ?></div><?php endif; ?>
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;margin-bottom:20px;">
              <p class="text-sm text-muted" style="margin-bottom:4px;">Sedang mengedit:</p>
              <p style="font-weight:700;font-size:15px;"><?= htmlspecialchars($d['judul']) ?></p>
            </div>
            <form method="POST">
              <div class="form-group">
                <label class="form-label">Judul Buku</label>
                <input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($d['judul']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Penulis</label>
                <input type="text" name="penulis" class="form-control" value="<?= htmlspecialchars($d['penulis']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Stok</label>
                <input type="number" name="stok" class="form-control" value="<?= (int)$d['stok'] ?>" min="0" required>
              </div>
              <div style="display:flex;gap:10px;">
                <button name="update" type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">💾 Simpan Perubahan</button>
                <a href="buku.php" class="btn btn-outline">Batal</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>