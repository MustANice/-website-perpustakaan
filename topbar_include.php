<?php
// Ambil data notifikasi & dark mode
// Include file ini di bagian paling atas setiap halaman setelah session_start & include koneksi

include_once 'functions.php';

// Cek & buat notifikasi otomatis
cek_denda_otomatis($conn);
cek_stok_rendah($conn);

// Ambil notifikasi
$notif_list  = get_notifikasi($conn, 10);
$notif_count = count_notifikasi_unread($conn);

// Dark mode
$dark_mode = $_SESSION['dark_mode'] ?? get_dark_mode($conn, (int)$_SESSION['id']);
$_SESSION['dark_mode'] = $dark_mode;
?>
