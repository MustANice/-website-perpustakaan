<?php
session_start();
include 'koneksi.php';
include 'functions.php';

if(!isset($_SESSION['login'])) exit;

$action = $_GET['action'] ?? '';

if($action === 'read_all'){
    tandai_semua_dibaca($conn);
    echo json_encode(['status'=>'ok']);
}

if($action === 'dark_mode'){
    $mode = (int)($_GET['mode'] ?? 0);
    toggle_dark_mode($conn, $_SESSION['id_user'], $mode);
    $_SESSION['dark_mode'] = $mode;
    echo json_encode(['status'=>'ok']);
}
?>