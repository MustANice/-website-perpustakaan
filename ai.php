<?php
?>

<h2>Rekomendasi Buku (Google Books API)</h2>

<form method="POST">
  <input type="text" name="judul" placeholder="Masukkan judul buku"><br><br>
  <button name="cari">Cari</button>
</form>

<?php
if(isset($_POST['cari'])){
    $judul = urlencode($_POST['judul']);
    $url = "https://www.googleapis.com/books/v1/volumes?q=".$judul;

    // PAKAI CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if(isset($data['items'])){
        echo "<h3>Hasil:</h3>";
        foreach($data['items'] as $item){
            echo "- " . $item['volumeInfo']['title'] . "<br>";
        }
    } else {
        echo "Data tidak ditemukan";
    }
}
?>