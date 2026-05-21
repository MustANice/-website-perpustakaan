<?php
session_start();
include 'koneksi.php';

if(!isset($_SESSION['login'])){
    header("Location: index.php");
}
?>

<h2>Peminjaman Buku</h2>

<form method="POST">
  <select name="id_buku">
    <?php
    $buku = mysqli_query($conn, "SELECT * FROM buku WHERE stok > 0");
    while($b = mysqli_fetch_array($buku)){
        echo "<option value='$b[id]'>$b[judul] (Stok: $b[stok])</option>";
    }
    ?>
  </select><br><br>

  <input type="date" name="tanggal_pinjam" required><br><br>

  <button name="pinjam">Pinjam</button>
</form>

<?php
if(isset($_POST['pinjam'])){
    $id_buku = $_POST['id_buku'];
    $tanggal = $_POST['tanggal_pinjam'];

    // Simpan peminjaman
    mysqli_query($conn, "INSERT INTO peminjaman (id_buku, id_user, tanggal_pinjam) 
    VALUES ('$id_buku', 1, '$tanggal')");

    // Kurangi stok
    mysqli_query($conn, "UPDATE buku SET stok = stok - 1 WHERE id = $id_buku");

    echo "Buku berhasil dipinjam!";
}
?>

<br><br>
<a href="dashboard.php">Kembali</a>