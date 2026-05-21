<?php
include 'koneksi.php';
?>

<h2>Pengembalian Buku</h2>

<table border="1">
<tr>
  <th>ID</th>
  <th>Buku</th>
  <th>Tanggal Pinjam</th>
  <th>Aksi</th>
</tr>

<?php
$data = mysqli_query($conn, "SELECT p.*, b.judul FROM peminjaman p 
JOIN buku b ON p.id_buku = b.id 
WHERE tanggal_kembali IS NULL");

while($d = mysqli_fetch_array($data)){
?>
<tr>
  <td><?= $d['id']; ?></td>
  <td><?= $d['judul']; ?></td>
  <td><?= $d['tanggal_pinjam']; ?></td>
  <td>
    <a href="proses_kembali.php?id=<?= $d['id']; ?>&buku=<?= $d['id_buku']; ?>">Kembalikan</a>
  </td>
</tr>
<?php } ?>
</table>