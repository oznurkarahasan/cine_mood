<?php
// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Gerekli dosyaları dahil et
require_once __DIR__ . '/TMDB.php';
require_once __DIR__ . '/config.php';

// TMDB sınıfını başlat
$tmdb = new TMDB(TMDB_API_KEY);

// HTML başlangıcı
echo "<!DOCTYPE html>";
echo "<html lang='tr'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>TMDB API Test</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; }";
echo ".container { max-width: 1200px; margin: 0 auto; }";
echo ".section { margin-bottom: 30px; }";
echo ".card { border: 1px solid #ddd; padding: 10px; margin: 10px; display: inline-block; width: 200px; }";
echo ".card img { max-width: 100%; }";
echo ".debug { background: #f5f5f5; padding: 10px; margin: 10px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";

// Film türlerini getir
echo "<div class='section'>";
echo "<h2>Film Türleri</h2>";
try {
    $genres = $tmdb->getMovieGenres();
    echo "<div class='debug'>";
    echo "<pre>";
    print_r($genres);
    echo "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<p>Hata: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Dizi türlerini getir
echo "<div class='section'>";
echo "<h2>Dizi Türleri</h2>";
try {
    $genres = $tmdb->getTVGenres();
    echo "<div class='debug'>";
    echo "<pre>";
    print_r($genres);
    echo "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<p>Hata: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Popüler filmleri getir
echo "<div class='section'>";
echo "<h2>Popüler Filmler</h2>";
try {
    $movies = $tmdb->getPopularMovies();
    echo "<div class='debug'>";
    echo "<pre>";
    print_r($movies);
    echo "</pre>";
    echo "</div>";
    
    // Film kartlarını göster
    foreach ($movies['results'] as $movie) {
        echo "<div class='card'>";
        if ($movie['poster_path']) {
            echo "<img src='https://image.tmdb.org/t/p/w200" . $movie['poster_path'] . "' alt='" . $movie['title'] . "'>";
        }
        echo "<h3>" . $movie['title'] . "</h3>";
        echo "<p>Puan: " . $movie['vote_average'] . "</p>";
        echo "<p>Tarih: " . $movie['release_date'] . "</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<p>Hata: " . $e->getMessage() . "</p>";
}
echo "</div>";

// Popüler dizileri getir
echo "<div class='section'>";
echo "<h2>Popüler Diziler</h2>";
try {
    $tvShows = $tmdb->getPopularTVShows();
    echo "<div class='debug'>";
    echo "<pre>";
    print_r($tvShows);
    echo "</pre>";
    echo "</div>";
    
    // Dizi kartlarını göster
    foreach ($tvShows['results'] as $tvShow) {
        echo "<div class='card'>";
        if ($tvShow['poster_path']) {
            echo "<img src='https://image.tmdb.org/t/p/w200" . $tvShow['poster_path'] . "' alt='" . $tvShow['name'] . "'>";
        }
        echo "<h3>" . $tvShow['name'] . "</h3>";
        echo "<p>Puan: " . $tvShow['vote_average'] . "</p>";
        echo "<p>Tarih: " . $tvShow['first_air_date'] . "</p>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<p>Hata: " . $e->getMessage() . "</p>";
}
echo "</div>";

// HTML sonu
echo "</div>";
echo "</body>";
echo "</html>";
?> 