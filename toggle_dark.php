<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login'])) { http_response_code(403); exit; }

$id   = (int)$_SESSION['id'];
$mode = (int)($_POST['mode'] ?? 0);

$stmt = $conn->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
$stmt->bind_param("ii", $mode, $id);
$stmt->execute();

$_SESSION['dark_mode'] = $mode;
echo json_encode(['ok' => true]);
