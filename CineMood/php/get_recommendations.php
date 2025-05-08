<?php
// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// JSON header'ı ekle
header('Content-Type: application/json');

try {
    // Gerekli dosyaları kontrol et
    if (!file_exists(__DIR__ . '/TMDB.php')) {
        throw new Exception('TMDB.php dosyası bulunamadı');
    }
    if (!file_exists(__DIR__ . '/config.php')) {
        throw new Exception('config.php dosyası bulunamadı');
    }

    require_once __DIR__ . '/TMDB.php';
    require_once __DIR__ . '/config.php';

    // TMDB API anahtarını kontrol et
    if (!defined('TMDB_API_KEY')) {
        throw new Exception('TMDB API anahtarı bulunamadı');
    }

    // TMDB sınıfını başlat
    $tmdb = new TMDB(TMDB_API_KEY);

    // Parametreleri al
    $moods = isset($_GET['moods']) ? explode(',', $_GET['moods']) : ['mutlu'];
    $type = isset($_GET['type']) ? $_GET['type'] : 'movie';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    // Mod-tür eşleştirmesi (optimize edilmiş)
    $moodGenres = [
        'mutlu' => [35, 10751], // Komedi, Aile
        'uzgun' => [18, 10749], // Drama, Romantik
        'kaygili' => [35, 99], // Komedi, Belgesel
        'huzurlu' => [99, 10402], // Belgesel, Müzik
        'bikkın' => [35, 12], // Komedi, Macera
        'yalniz' => [18, 10749], // Drama, Romantik
        'heyecanli' => [10759, 12], // Aksiyon & Macera, Macera
        'dusunceli' => [99, 36], // Belgesel, Tarih
        'motive' => [10759, 12], // Aksiyon & Macera, Macera
        'yeniliklereAcik' => [10765, 14], // Bilim Kurgu & Fantastik, Fantastik
        'yorgun' => [35, 10767], // Komedi, Talk Show
        'karmasik' => [9648, 53], // Gizem, Gerilim
        'bilmiyorum' => [35, 18] // Komedi, Drama
    ];
/*
        'mutlu' => [35, 10751, 16, 10402, 10749, 12], // Komedi, Aile, Animasyon, Müzik, Romantik, Macera
        'uzgun' => [18, 10749, 10766, 35, 10402], // Drama, Romantik, Sabun Dizisi, Komedi, Müzik
        'kaygili' => [35, 10751, 16, 99, 18, 10749], // Komedi, Aile, Animasyon, Belgesel, Drama, Romantik
        'huzurlu' => [99, 10402, 10751, 10749, 18, 35, 16], // Belgesel, Müzik, Aile, Romantik, Drama, Komedi, Animasyon
        'bikkın' => [35, 12, 16, 10759, 10751], // Komedi, Macera, Animasyon, Aksiyon & Macera, Aile
        'yalniz' => [18, 10749, 9648, 35, 10402, 10751], // Drama, Romantik, Gizem, Komedi, Müzik, Aile
        'heyecanli' => [10759, 12, 53, 10765, 35, 16], // Aksiyon & Macera, Macera, Gerilim, Bilim Kurgu & Fantastik, Komedi, Animasyon
        'dusunceli' => [99, 36, 9648, 10767, 18, 10749], // Belgesel, Tarih, Gizem, Talk Show, Drama, Romantik
        'motive' => [10759, 12, 16, 10402, 18, 35, 10751], // Aksiyon & Macera, Macera, Animasyon, Müzik, Drama, Komedi, Aile
        'yeniliklereAcik' => [10765, 14, 12, 10759, 16, 35], // Bilim Kurgu & Fantastik, Fantastik, Macera, Aksiyon & Macera, Animasyon, Komedi
        'yorgun' => [35, 10751, 10767, 99, 10402, 18], // Komedi, Aile, Talk Show, Belgesel, Müzik, Drama
        'karmasik' => [9648, 53, 80, 18, 35, 10749], // Gizem, Gerilim, Suç, Drama, Komedi, Romantik
        'bilmiyorum' => [35, 10759, 18, 99, 16, 12, 10751, 10402] // Rastgele: Komedi, Aksiyon & Macera, Drama, Belgesel, Animasyon, Macera, Aile, Müzik
*/
    // Öneriler
    $recommendations = [];
    $results = [];
    $seenIds = []; // Aynı içeriğin tekrarlanmasını önlemek için
    $failedMoods = []; // Boş sonuç veren modları takip et
    $maxPages = 10; // İlk n sayfayı çek

    if ($type === 'movie') {
        // Her mod için ayrı sorgu yap
        foreach ($moods as $mood) {
            if (!isset($moodGenres[$mood])) {
                $failedMoods[] = $mood;
                continue;
            }
            $genreIds = $moodGenres[$mood];
            // İlk 2 sayfayı çek
            for ($p = 1; $p <= $maxPages; $p++) { // Her mod için hep 1'den başla
                $movies = $tmdb->searchMoviesByGenres($genreIds, $p);
                if (empty($movies['results'])) {
                    if ($p === $page) {
                        $failedMoods[] = $mood; // İlk sayfada sonuç yoksa mod başarısız
                    }
                    continue;
                }
                foreach ($movies['results'] as $movie) {
                    if (!in_array($movie['id'], $seenIds)) {
                        $results[] = $movie;
                        $seenIds[] = $movie['id'];
                    }
                }
            }
        }
        $recommendations['movies'] = $results;
        
        // Eğer sonuç yoksa, popüler filmleri getir
        if (empty($results)) {
            $movies = $tmdb->getPopularMovies($page);
            $recommendations['movies'] = $movies['results'] ?? [];
            $recommendations['fallback'] = true;
            $recommendations['failed_moods'] = $failedMoods;
        }
    } else if ($type === 'tv') {
        // Her mod için ayrı sorgu yap
        foreach ($moods as $mood) {
            if (!isset($moodGenres[$mood])) {
                $failedMoods[] = $mood;
                continue;
            }
            $genreIds = $moodGenres[$mood];
            // İlk 2 sayfayı çek
            for ($p = 1; $p <= $maxPages; $p++) { // Her mod için hep 1'den başla
                $tvShows = $tmdb->searchTVShowsByGenres($genreIds, $p);
                if (empty($tvShows['results'])) {
                    if ($p === $page) {
                        $failedMoods[] = $mood; // İlk sayfada sonuç yoksa mod başarısız
                    }
                    continue;
                }
                foreach ($tvShows['results'] as $tvShow) {
                    if (!in_array($tvShow['id'], $seenIds)) {
                        $results[] = $tvShow;
                        $seenIds[] = $tvShow['id'];
                    }
                }
            }
            // Türkçe sonuç yoksa, İngilizce fallback
            if (empty($results)) {
                for ($p = 1; $p <= $maxPages; $p++) { // Her mod için hep 1'den başla
                    $tvShows = $tmdb->searchTVShowsByGenres($genreIds, $p, 'en-US');
                    if (empty($tvShows['results'])) {
                        continue;
                    }
                    foreach ($tvShows['results'] as $tvShow) {
                        if (!in_array($tvShow['id'], $seenIds)) {
                            $results[] = $tvShow;
                            $seenIds[] = $tvShow['id'];
                        }
                    }
                }
            }
        }
        $recommendations['tvShows'] = $results;
        
        // Eğer sonuç yoksa, popüler dizileri getir
        if (empty($results)) {
            $tvShows = $tmdb->getPopularTVShows($page);
            $recommendations['tvShows'] = $tvShows['results'] ?? [];
            $recommendations['fallback'] = true;
            $recommendations['failed_moods'] = $failedMoods;
        }
    }

    // Sonuçları döndür
    echo json_encode($recommendations);

} catch (Exception $e) {
    // Hata durumunda JSON formatında hata mesajı döndür
    error_log("Error in get_recommendations.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>