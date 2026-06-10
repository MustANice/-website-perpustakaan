<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }

require_once 'SimpleXLSXGen.php';
use Shuchkin\SimpleXLSXGen;

$result = mysqli_query($conn, "SELECT * FROM buku ORDER BY id DESC");

$rows = [
    ['<b>No</b>', '<b>Judul Buku</b>', '<b>Penulis</b>', '<b>Stok</b>']
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
SimpleXLSXGen::fromArray($rows, 'Data Buku')->downloadAs("laporan_buku_{$tanggal}.xlsx");
exit;
