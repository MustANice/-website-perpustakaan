<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }

// Hanya superadmin yang boleh akses halaman ini
if ($_SESSION['role'] !== 'superadmin') {
    header("Location: dashboard.php");
    exit;
}

$msg      = '';
$msg_type = '';

// TAMBAH USER
if (isset($_POST['tambah'])) {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = trim($_POST['role']);

    $allowed_roles = ['superadmin', 'admin', 'petugas'];

    if ($nama && $username && $password && in_array($role, $allowed_roles)) {
        // Cek username sudah dipakai atau belum
        $cek = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $cek->bind_param("s", $username);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $msg      = "⚠️ Username sudah digunakan, pilih username lain.";
            $msg_type = "warning";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $username, $hash, $role);
            if ($stmt->execute()) {
                $msg      = "✅ User berhasil ditambahkan!";
                $msg_type = "success";
            } else {
                $msg      = "❌ Gagal menambahkan user.";
                $msg_type = "danger";
            }
        }
    } else {
        $msg      = "⚠️ Semua kolom wajib diisi.";
        $msg_type = "warning";
    }
}

// PESAN DARI REDIRECT
if (isset($_GET['msg'])) {
    $get_msg = $_GET['msg'];
    if ($get_msg === 'updated') {
        $msg      = "✅ User berhasil diupdate!";
        $msg_type = "success";
    } elseif ($get_msg === 'deleted') {
        $msg      = "✅ User berhasil dihapus!";
        $msg_type = "success";
    } elseif ($get_msg === 'cannot_delete') {
        $msg      = "❌ Tidak bisa menghapus akun sendiri!";
        $msg_type = "danger";
    }
}

// AMBIL SEMUA USER
$stmt  = $conn->prepare("SELECT id, nama, username, role FROM users ORDER BY id ASC");
$stmt->execute();
$data  = $stmt->get_result();
$total = $data->num_rows;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola User — Perpustakaan Kampus</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h2>Kelola User</h2>
        <p>Manajemen akun pengguna sistem</p>
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
        <span>Kelola User</span>
      </div>

      <?php if ($msg): ?>
        <div class="alert alert-<?= htmlspecialchars($msg_type) ?>">
          <?= htmlspecialchars($msg) ?>
        </div>
      <?php endif; ?>

      <div style="display:grid; grid-template-columns:340px 1fr; gap:20px; align-items:start;">

        <!-- FORM TAMBAH USER -->
        <div class="card">
          <div class="card-header"><h3>➕ Tambah User Baru</h3></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" maxlength="100" required>
              </div>
              <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Username login" maxlength="50" required>
              </div>
              <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
              </div>
              <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-control" required>
                  <option value="">-- Pilih Role --</option>
                  <option value="superadmin">Super Admin</option>
                  <option value="admin">Admin</option>
                  <option value="petugas">Petugas</option>
                </select>
              </div>
              <button name="tambah" type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                ➕ Tambah User
              </button>
            </form>
          </div>
        </div>

        <!-- DAFTAR USER -->
        <div class="card">
          <div class="card-header">
            <h3>
              👥 Daftar User
              <span class="badge badge-blue" style="margin-left:8px;"><?= $total ?> user</span>
            </h3>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nama</th>
                  <th>Username</th>
                  <th>Role</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($total === 0): ?>
                  <tr>
                    <td colspan="5">
                      <div class="empty-state">
                        <div class="empty-icon">👤</div>
                        <p>Belum ada user.</p>
                      </div>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php $no = 1; while ($d = $data->fetch_assoc()): ?>
                  <tr>
                    <td class="text-muted"><?= $no++ ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($d['nama']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($d['username']) ?></td>
                    <td>
                      <?php
                        $role_label = ['superadmin' => ['label' => 'Super Admin', 'badge' => 'badge-blue'],
                                       'admin'      => ['label' => 'Admin',       'badge' => 'badge-green'],
                                       'petugas'    => ['label' => 'Petugas',     'badge' => 'badge-amber']];
                        $r = $role_label[$d['role']] ?? ['label' => $d['role'], 'badge' => 'badge-blue'];
                      ?>
                      <span class="badge <?= $r['badge'] ?>"><?= $r['label'] ?></span>
                    </td>
                    <td>
                      <div style="display:flex; gap:6px;">
                        <a href="edit_user.php?id=<?= (int)$d['id'] ?>" class="btn btn-outline btn-sm">✏️ Edit</a>
                        <?php if ((int)$d['id'] !== (int)($_SESSION['id'] ?? $_SESSION['id_user'] ?? 0)): ?>
                          <a href="hapus_user.php?id=<?= (int)$d['id'] ?>" class="btn btn-danger btn-sm"
                             onclick="return confirm('Yakin ingin menghapus user ini?')">🗑️</a>
                        <?php else: ?>
                          <span class="btn btn-sm" style="opacity:0.4;cursor:not-allowed;" title="Tidak bisa hapus akun sendiri">🗑️</span>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
</body>
</html>
