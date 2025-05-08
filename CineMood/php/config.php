<?php
// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);
//yedek
//a5217b902bb546eaa660805227ccb7f7

define('TMDB_API_KEY', '595b29f176d4e146a16d329e5bbbf3aa'); 

// API anahtarını kontrol et
if (empty(TMDB_API_KEY)) {
    die('TMDB API anahtarı bulunamadı. Lütfen config.php dosyasını kontrol edin.');
}
?> 