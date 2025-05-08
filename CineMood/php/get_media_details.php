<?php
// Çıktı tamponlamasını başlat
ob_start();

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hataları ekranda gösterme

// JSON header'ı ekle
header('Content-Type: application/json; charset=utf-8');

// Hata işleyici fonksiyonu
function handleError($errno, $errstr, $errfile, $errline) {
    ob_clean(); // Tamponu temizle
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'PHP Hatası: ' . $errstr
    ]);
    exit;
}

// Fatal error handler
function handleFatalError() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean(); // Tamponu temizle
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Kritik PHP Hatası: ' . $error['message']
        ]);
        exit;
    }
}

// Hata işleyicileri kaydet
set_error_handler('handleError');
register_shutdown_function('handleFatalError');

try {
    // Gerekli dosyaları dahil et
    if (!file_exists('config.php')) {
        throw new Exception('config.php dosyası bulunamadı');
    }
    if (!file_exists('auth.php')) {
        throw new Exception('auth.php dosyası bulunamadı');
    }

    require_once 'config.php';
    require_once 'auth.php';

    // Oturum kontrolü
    if (!isLoggedIn()) {
        throw new Exception('Oturum açmanız gerekiyor');
    }

    // ID ve tip parametrelerini al
    $id = $_GET['id'] ?? null;
    $type = $_GET['type'] ?? 'movie';

    if (!$id) {
        throw new Exception('ID parametresi gerekli');
    }

    if (!in_array($type, ['movie', 'tv'])) {
        throw new Exception('Geçersiz medya tipi');
    }

    // TMDB API'den detayları al
    $detailsUrl = "https://api.themoviedb.org/3/{$type}/{$id}?api_key=" . TMDB_API_KEY . "&language=tr-TR";
    $creditsUrl = "https://api.themoviedb.org/3/{$type}/{$id}/credits?api_key=" . TMDB_API_KEY . "&language=tr-TR";

    // cURL oturumu başlat
    $ch = curl_init();
    if ($ch === false) {
        throw new Exception('cURL başlatılamadı');
    }

    // cURL ayarları
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

    // Detaylar için istek
    curl_setopt($ch, CURLOPT_URL, $detailsUrl);
    $detailsResponse = curl_exec($ch);
    
    if ($detailsResponse === false) {
        throw new Exception('Detaylar alınamadı: ' . curl_error($ch));
    }

    // Oyuncular için istek
    curl_setopt($ch, CURLOPT_URL, $creditsUrl);
    $creditsResponse = curl_exec($ch);
    
    if ($creditsResponse === false) {
        throw new Exception('Oyuncular alınamadı: ' . curl_error($ch));
    }

    curl_close($ch);

    // Yanıtları JSON'a çevir
    $details = json_decode($detailsResponse, true);
    $credits = json_decode($creditsResponse, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON çözümleme hatası: ' . json_last_error_msg());
    }

    if (!$details || !$credits) {
        throw new Exception('API yanıtı alınamadı');
    }

    // Tamponu temizle
    ob_clean();

    // Başarılı yanıt döndür
    echo json_encode([
        'success' => true,
        'details' => $details,
        'credits' => $credits
    ]);

} catch (Exception $e) {
    // Tamponu temizle
    ob_clean();
    
    // Hata durumunda JSON yanıtı döndür
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Tamponu temizle ve gönder
ob_end_flush();
?> 