<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }
if ($_SESSION['role'] !== 'superadmin') { header("Location: dashboard.php"); exit; }

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: users.php"); exit; }

// Ambil data user
$stmt = $conn->prepare("SELECT id, nama, username, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();

if (!$user) { header("Location: users.php"); exit; }

$msg      = '';
$msg_type = '';

// PROSES UPDATE
if (isset($_POST['update'])) {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $role     = trim($_POST['role']);
    $password = trim($_POST['password']);

    $allowed_roles = ['superadmin', 'admin', 'petugas'];

    if ($nama && $username && in_array($role, $allowed_roles)) {
        // Cek username sudah dipakai user lain
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $cek->bind_param("si", $username, $id);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $msg      = "⚠️ Username sudah digunakan user lain.";
            $msg_type = "warning";
        } else {
            if ($password !== '') {
                // Update dengan password baru
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, password=?, role=? WHERE id=?");
                $stmt->bind_param("ssssi", $nama, $username, $hash, $role, $id);
            } else {
                // Update tanpa ganti password
                $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id=?");
                $stmt->bind_param("sssi", $nama, $username, $role, $id);
            }

            if ($stmt->execute()) {
                header("Location: users.php?msg=updated");
                exit;
            } else {
                $msg      = "❌ Gagal mengupdate user.";
                $msg_type = "danger";
            }
        }
    } else {
        $msg      = "⚠️ Semua kolom wajib diisi.";
        $msg_type = "warning";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit User — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h2>Edit User</h2>
        <p>Ubah data akun pengguna</p>
      </div>
      <div class="topbar-right">
        <div class="topbar-user">
          <div class="avatar">A</div>
          <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>
        </div>
      </div>
    </div>

    <div class="page-content">
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a>
        <span class="sep">/</span>
        <a href="users.php">Kelola User</a>
        <span class="sep">/</span>
        <span>Edit User</span>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg_type) ?>">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <div style="max-width:480px;">
        <div class="card">
          <div class="card-header"><h3>✏️ Edit User: <?= htmlspecialchars($user['username']) ?></h3></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" maxlength="100"
                       value="<?= htmlspecialchars($user['nama']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" maxlength="50"
                       value="<?= htmlspecialchars($user['username']) ?>" required>
              </div>
              <div class="form-group">
                <label class="form-label">Password Baru <small class="text-muted">(kosongkan jika tidak ingin ganti)</small></label>
                <input type="password" name="password" class="form-control" placeholder="Password baru (opsional)">
              </div>
              <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                  <option value="superadmin" <?= $user['role']==='superadmin'?'selected':'' ?>>Super Admin</option>
                  <option value="admin"      <?= $user['role']==='admin'     ?'selected':'' ?>>Admin</option>
                  <option value="petugas"    <?= $user['role']==='petugas'   ?'selected':'' ?>>Petugas</option>
                </select>
              </div>
              <div style="display:flex; gap:10px; margin-top:8px;">
                <button name="update" type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="users.php" class="btn btn-outline">✕ Batal</a>
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
