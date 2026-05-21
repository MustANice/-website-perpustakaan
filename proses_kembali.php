<?php
include 'koneksi.php';

$id = $_GET['id'];
$id_buku = $_GET['buku'];

$tgl_kembali = date("Y-m-d");

// Ambil data pinjam
$data = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id=$id");
$d = mysqli_fetch_array($data);

// Hitung keterlambatan (misal max 7 hari)
$pinjam = strtotime($d['tanggal_pinjam']);
$kembali = strtotime($tgl_kembali);

$selisih = ($kembali - $pinjam) / (60*60*24);
$denda = 0;

if($selisih > 7){
    $denda = ($selisih - 7) * 1000;
}

// Update data
mysqli_query($conn, "UPDATE peminjaman SET 
tanggal_kembali='$tgl_kembali', 
denda='$denda' 
WHERE id=$id");

// Tambah stok lagi
mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id=$id_buku");

header("Location: kembali.php");