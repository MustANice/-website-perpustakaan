<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['login'])){
    header("Location: index.php");
}
?>

<h2>Tambah Buku</h2>
<form method="POST">
  <input type="text" name="judul" placeholder="Judul"><br><br>
  <input type="text" name="penulis" placeholder="Penulis"><br><br>
  <input type="number" name="stok" placeholder="Stok"><br><br>
  <button name="tambah">Tambah</button>
</form>

<?php
if(isset($_POST['tambah'])){
    mysqli_query($conn, "INSERT INTO buku (judul, penulis, stok) 
    VALUES ('$_POST[judul]','$_POST[penulis]','$_POST[stok]')");
}
?>

<h2>Daftar Buku</h2>
<table border="1">
<tr>
  <th>Judul</th>
  <th>Penulis</th>
  <th>Stok</th>
  <th>Aksi</th>
</tr>

<?php
$data = mysqli_query($conn, "SELECT * FROM buku");
while($d = mysqli_fetch_array($data)){
?>
<tr>
  <td><?= $d['judul']; ?></td>
  <td><?= $d['penulis']; ?></td>
  <td><?= $d['stok']; ?></td>
  <td>
    <a href="edit.php?id=<?= $d['id']; ?>">Edit</a> |
    <a href="hapus.php?id=<?= $d['id']; ?>">Hapus</a>
  </td>
</tr>
<?php } ?>
</table>

<br><br>
<a href="dashboard.php">Kembali ke Dashboard</a>