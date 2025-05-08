<?php
require "db.php";  // Veritabanı bağlantı kodunu dahil et

$sql = "SELECT * FROM cinemood";  // Tüm verileri çekmek için sorgu
$stmt = $pdo->prepare($sql);  // SQL sorgusunu hazırlıyoruz
$stmt->execute();  // Sorguyu çalıştırıyoruz

// Veriyi alıyoruz
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Veriyi ilişkilendirilmiş dizi olarak al

// HTML başlangıcı
echo "<!DOCTYPE html>";
echo "<html lang='tr'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Film Listesi</title>";
echo "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>";
echo "</head>";
echo "<body>";

echo "<div class='container mt-5'>";
echo "<h2>Film Listesi</h2>";
echo "<div class='row'>";  // Kartları yatayda düzenlemek için row sınıfı

// Verileri kartlar halinde gösteriyoruz
foreach ($movies as $movie) {
    echo "<div class='col-md-4'>";
    echo "<div class='card mb-4' style='width: 18rem;'>";
    echo "<div class='card-body'>";
    echo "<h5 class='card-title'>" . $movie['title'] . "</h5>";
    echo "<p class='card-text'><strong>Director:</strong> " . $movie['director'] . "</p>";
    echo "<p class='card-text'><strong>Release Year:</strong> " . $movie['release_year'] . "</p>";
    echo "<p class='card-text'><strong>Rating:</strong> " . $movie['rating'] . "</p>";
    echo "<p class='card-text'><strong>Description:</strong> " . $movie['description'] . "</p>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

echo "</div>";
echo "</div>";

echo "</body>";
echo "</html>";
?>
