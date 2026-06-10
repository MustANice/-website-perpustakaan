<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }

require_once 'SimpleXLSXGen.php';

$result = mysqli_query($conn, "SELECT * FROM buku ORDER BY id DESC");

$rows = [
    ['No', 'Judul Buku', 'Penulis', 'Stok']
];

$no = 1;
while ($d = mysqli_fetch_assoc($result)) {
    $rows[] = [
        $no++,
        $d['judul'],
        $d['penulis'],
        (int)$d['stok'],
    ];
}

$tanggal = date('Y-m-d');
SimpleXLSXGen::fromArray($rows)->downloadAs("laporan_buku_{$tanggal}.xlsx");
exit;
