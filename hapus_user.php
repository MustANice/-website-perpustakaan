<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }
if ($_SESSION['role'] !== 'superadmin') { header("Location: dashboard.php"); exit; }

$id = (int)($_GET['id'] ?? 0);

// Tidak boleh hapus akun sendiri
if ($id <= 0 || $id === (int)$_SESSION['id']) {
    header("Location: users.php?msg=cannot_delete");
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: users.php?msg=deleted");
} else {
    header("Location: users.php?msg=cannot_delete");
}
exit;
