<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['login'])) { header("Location: index.php"); exit; }

require_once 'fpdf.php';

$result = mysqli_query($conn, "SELECT * FROM buku ORDER BY id DESC");
$data   = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

$pdf = new FPDF('P', 'mm', 'A4'); // Portrait A4
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

// Judul
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Laporan Data Buku', 0, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Perpustakaan Kampus Digital - ' . date('d/m/Y'), 0, 1, 'C');
$pdf->Ln(4);

// Header tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(108, 99, 255);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(200, 200, 200);

$pdf->Cell(12,  8, 'No',         1, 0, 'C', true);
$pdf->Cell(95,  8, 'Judul Buku', 1, 0, 'C', true);
$pdf->Cell(60,  8, 'Penulis',    1, 0, 'C', true);
$pdf->Cell(18,  8, 'Stok',       1, 1, 'C', true);

// Isi tabel
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0);
$no = 1;
foreach ($data as $d) {
    $fill = ($no % 2 === 0);
    $pdf->SetFillColor(245, 245, 255);

    $pdf->Cell(12,  7, $no++,          1, 0, 'C', $fill);
    $pdf->Cell(95,  7, $d['judul'],    1, 0, 'L', $fill);
    $pdf->Cell(60,  7, $d['penulis'],  1, 0, 'L', $fill);
    $pdf->Cell(18,  7, $d['stok'],     1, 1, 'C', $fill);
}

// Total
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(185, 7, 'Total Buku: ' . count($data), 1, 1, 'R');

$tanggal = date('Y-m-d');
$pdf->Output('D', "laporan_buku_{$tanggal}.pdf");
exit;
