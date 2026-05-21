<?php
include 'koneksi.php';

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM buku WHERE id=$id");
$d = mysqli_fetch_array($data);
?>

<h2>Edit Buku</h2>

<form method="POST">
  <input type="text" name="judul" value="<?= $d['judul']; ?>"><br><br>
  <input type="text" name="penulis" value="<?= $d['penulis']; ?>"><br><br>
  <input type="number" name="stok" value="<?= $d['stok']; ?>"><br><br>

  <button name="update">Update</button>
</form>

<?php
if(isset($_POST['update'])){
    mysqli_query($conn, "UPDATE buku SET 
    judul='$_POST[judul]',
    penulis='$_POST[penulis]',
    stok='$_POST[stok]'
    WHERE id=$id");

    header("Location: buku.php");
}
?>