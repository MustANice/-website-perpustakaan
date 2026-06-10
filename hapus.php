<?php
session_start();
include 'koneksi.php';
include 'functions.php';
if(!isset($_SESSION['login'])){ header("Location: index.php"); exit; }

// ✅ CEK ROLE
$role = $_SESSION['role'] ?? 'user';
if(!in_array($role, ['admin', 'superadmin'])){
    header("Location: buku.php?error=forbidden"); exit;
}