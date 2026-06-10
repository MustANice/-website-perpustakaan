<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login'])) { http_response_code(403); exit; }

mysqli_query($conn, "UPDATE notifikasi SET sudah_dibaca = 1");
echo json_encode(['ok' => true]);
