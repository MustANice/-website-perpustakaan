<?php
// ============================================
// FUNCTIONS.PHP — Helper semua fitur
// ============================================

// ===== LOG AKTIVITAS =====
function log_aktivitas($conn, $id_user, $username, $aksi, $detail = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '-';
    $stmt = $conn->prepare(
        "INSERT INTO log_aktivitas (id_user, username, aksi, detail, ip_address)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("issss", $id_user, $username, $aksi, $detail, $ip);
    $stmt->execute();
}

// ===== BUAT NOTIFIKASI =====
function buat_notifikasi($conn, $judul, $pesan, $tipe = 'info') {
    // Cek duplikat dalam 1 jam terakhir
    $cek = $conn->prepare(
        "SELECT id FROM notifikasi 
         WHERE judul = ? AND created_at > NOW() - INTERVAL 1 HOUR"
    );
    $cek->bind_param("s", $judul);
    $cek->execute();
    if($cek->get_result()->num_rows > 0) return; // skip duplikat

    $stmt = $conn->prepare(
        "INSERT INTO notifikasi (judul, pesan, tipe) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $judul, $pesan, $tipe);
    $stmt->execute();
}

// ===== CEK DENDA OTOMATIS =====
function cek_denda_otomatis($conn) {
    // Ambil semua peminjaman yang belum kembali
    $data = mysqli_query($conn,
        "SELECT p.*, b.judul 
         FROM peminjaman p 
         JOIN buku b ON p.id_buku = b.id
         WHERE p.tanggal_kembali IS NULL"
    );

    while($d = mysqli_fetch_assoc($data)) {
        $hari = (int)floor((time() - strtotime($d['tanggal_pinjam'])) / 86400);
        $terlambat = max(0, $hari - 7);

        if($terlambat > 0) {
            $denda = number_format($terlambat * 1000, 0, ',', '.');
            buat_notifikasi(
                $conn,
                "Keterlambatan: {$d['judul']}",
                "Buku \"{$d['judul']}\" terlambat {$terlambat} hari. Denda: Rp {$denda}",
                'danger'
            );
        } elseif($hari >= 6) {
            buat_notifikasi(
                $conn,
                "Jatuh Tempo Besok: {$d['judul']}",
                "Buku \"{$d['judul']}\" harus dikembalikan besok atau akan kena denda.",
                'warning'
            );
        }
    }
}

// ===== CEK STOK RENDAH =====
function cek_stok_rendah($conn) {
    $data = mysqli_query($conn, "SELECT * FROM buku WHERE stok <= 2");
    while($b = mysqli_fetch_assoc($data)) {
        if($b['stok'] == 0) {
            buat_notifikasi(
                $conn,
                "Stok Habis: {$b['judul']}",
                "Buku \"{$b['judul']}\" stok habis! Segera tambah stok.",
                'danger'
            );
        } else {
            buat_notifikasi(
                $conn,
                "Stok Rendah: {$b['judul']}",
                "Buku \"{$b['judul']}\" tersisa {$b['stok']} stok.",
                'warning'
            );
        }
    }
}

// ===== AMBIL NOTIFIKASI BELUM DIBACA =====
function get_notifikasi($conn, $limit = 10) {
    $result = mysqli_query($conn,
        "SELECT * FROM notifikasi 
         ORDER BY created_at DESC 
         LIMIT $limit"
    );
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function count_notifikasi_unread($conn) {
    $result = mysqli_query($conn,
        "SELECT COUNT(*) as total FROM notifikasi WHERE sudah_dibaca = 0"
    );
    $row = mysqli_fetch_assoc($result);
    return (int)$row['total'];
}

// ===== TANDAI SEMUA DIBACA =====
function tandai_semua_dibaca($conn) {
    mysqli_query($conn, "UPDATE notifikasi SET sudah_dibaca = 1");
}

// ===== GET/SET DARK MODE =====
function get_dark_mode($conn, $id_user) {
    $stmt = $conn->prepare("SELECT dark_mode FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return (int)($row['dark_mode'] ?? 0);
}

function toggle_dark_mode($conn, $id_user, $mode) {
    $stmt = $conn->prepare("UPDATE users SET dark_mode = ? WHERE id = ?");
    $stmt->bind_param("ii", $mode, $id_user);
    $stmt->execute();
}

// ===== FORMAT TANGGAL =====
function format_tgl($tgl) {
    if(!$tgl) return '—';
    $bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $d = date('j', strtotime($tgl));
    $m = (int)date('n', strtotime($tgl));
    $y = date('Y', strtotime($tgl));
    return "$d {$bulan[$m]} $y";
}

// ===== FORMAT RUPIAH =====
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>