<?php
session_start();
if(!isset($_SESSION['login'])){
    header("Location: index.php");
}
?>

<h1>Dashboard</h1>

<a href="buku.php">Data Buku</a><br>
<a href="pinjam.php">Peminjaman Buku</a><br>
<a href="kembali.php">Pengembalian Buku</a><br>
<a href="ai.php">AI Rekomendasi</a><br>
<a href="logout.php">Logout</a>