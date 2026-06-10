<?php
session_start();
include 'koneksi.php';
include 'functions.php';
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

// ✅ CEK ROLE
$role = $_SESSION['role'] ?? 'user';
if(!in_array($role, ['admin', 'superadmin'])){
    header("Location: kembali.php?error=forbidden"); exit;
}

$id=(int)($_GET['id']??0);
$id_buku=(int)($_GET['buku']??0);
if($id<=0||$id_buku<=0){ header("Location: kembali.php"); exit; }

$stmt=$conn->prepare("SELECT * FROM peminjaman WHERE id=? AND tanggal_kembali IS NULL");
$stmt->bind_param("i",$id); $stmt->execute();
if($stmt->get_result()->num_rows===0){ header("Location: kembali.php"); exit; }

$stmt=$conn->prepare("SELECT tanggal_pinjam FROM peminjaman WHERE id=?");
$stmt->bind_param("i",$id); $stmt->execute();
$d=$stmt->get_result()->fetch_assoc();

$tgl_kembali=date("Y-m-d");
$hari=(int)floor((strtotime($tgl_kembali)-strtotime($d['tanggal_pinjam']))/86400);
$denda=max(0,$hari-7)*1000;

$upd=$conn->prepare("UPDATE peminjaman SET tanggal_kembali=?,denda=? WHERE id=?");
$upd->bind_param("sii",$tgl_kembali,$denda,$id); $upd->execute();

$stok=$conn->prepare("UPDATE buku SET stok=stok+1 WHERE id=?");
$stok->bind_param("i",$id_buku); $stok->execute();

header("Location: kembali.php?msg=returned"); exit;
?>