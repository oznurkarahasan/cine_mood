<?php
class TMDB {
    private $apiKey;
    private $baseUrl = 'https://api.themoviedb.org/3';
    private $imageBaseUrl = 'https://image.tmdb.org/t/p/';

    public function __construct($apiKey) {
        if (empty($apiKey)) {
            throw new Exception('API anahtarı boş olamaz');
        }
        $this->apiKey = $apiKey;
    }

    // Film arama
    public function searchMovies($query, $page = 1) {
        $url = "{$this->baseUrl}/search/movie?api_key={$this->apiKey}&query=" . urlencode($query) . "&page={$page}&language=tr-TR";
        return $this->makeRequest($url);
    }

    // Dizi arama
    public function searchTVShows($query, $page = 1) {
        $url = "{$this->baseUrl}/search/tv?api_key={$this->apiKey}&query=" . urlencode($query) . "&page={$page}&language=tr-TR";
        return $this->makeRequest($url);
    }

    // Popüler filmler
    public function getPopularMovies($page = 1) {
        $url = "{$this->baseUrl}/movie/popular?api_key={$this->apiKey}&page={$page}&language=tr-TR";
        return $this->makeRequest($url);
    }

    // Popüler diziler
    public function getPopularTVShows($page = 1) {
        $url = "{$this->baseUrl}/tv/popular?api_key={$this->apiKey}&page={$page}&language=tr-TR";
        return $this->makeRequest($url);
    }

    // Film detayları
    public function getMovieDetails($movieId) {
        $url = "{$this->baseUrl}/movie/{$movieId}?api_key={$this->apiKey}&language=tr-TR";
        return $this->makeRequest($url);
    }

    // Dizi detayları
    public function getTVShowDetails($tvId) {
        $url = "{$this->baseUrl}/tv/{$tvId}?api_key={$this->apiKey}&language=tr-TR";
        return $this->makeRequest($url);
    }

    // Poster URL'si oluşturma
    public function getPosterUrl($posterPath, $size = 'w500') {
        if (!$posterPath) return null;
        return $this->imageBaseUrl . $size . $posterPath;
    }

    // Tüm filmleri getir
    public function getAllMovies($page = 1) {
        $url = "{$this->baseUrl}/discover/movie?api_key={$this->apiKey}&page={$page}&sort_by=popularity.desc&include_adult=false&include_video=false&language=tr-TR";
        return $this->makeRequest($url);
    }

    // Film türlerini getir
    public function getMovieGenres() {
        $url = "{$this->baseUrl}/genre/movie/list?api_key={$this->apiKey}&language=tr-TR";
        return $this->makeRequest($url);
    }

    // Dizi türlerini getir
    public function getTVGenres() {
        $url = "{$this->baseUrl}/genre/tv/list?api_key={$this->apiKey}&language=tr-TR";
        return $this->makeRequest($url);
    }

    // Tür ID'lerine göre film arama
    public function searchMoviesByGenres($genreIds, $page = 1, $language = 'tr-TR') {
        if (empty($genreIds)) {
            return ['results' => []];
        }
    
        $combinedResults = [];
        $seenMovieIds = [];
    
        foreach ($genreIds as $genreId) {
            $url = "{$this->baseUrl}/discover/movie?api_key={$this->apiKey}&with_genres={$genreId}&page={$page}&sort_by=popularity.desc&language={$language}&include_adult=false";
            $results = $this->makeRequest($url);
    
            // Eğer Türkçe sonuç yoksa İngilizce dene
            if (empty($results['results']) && $language === 'tr-TR') {
                $url = "{$this->baseUrl}/discover/movie?api_key={$this->apiKey}&with_genres={$genreId}&page={$page}&sort_by=popularity.desc&language=en-US&include_adult=false";
                $results = $this->makeRequest($url);
            }
    
            // Filmleri topla, aynıları ekleme
            foreach ($results['results'] as $movie) {
                if (!in_array($movie['id'], $seenMovieIds)) {
                    $combinedResults[] = $movie;
                    $seenMovieIds[] = $movie['id'];
                }
            }
        }
    
        return ['results' => $combinedResults];
    }
    

    // Tür ID'lerine göre dizi arama
    public function searchTVShowsByGenres($genreIds, $page = 1, $language = 'tr-TR') {
        if (empty($genreIds)) {
            return ['results' => []];
        }
    
        $combinedResults = [];
        $seenTVIds = [];
    
        foreach ($genreIds as $genreId) {
            $url = "{$this->baseUrl}/discover/tv?api_key={$this->apiKey}&with_genres={$genreId}&page={$page}&sort_by=popularity.desc&language={$language}&include_adult=false";
            $results = $this->makeRequest($url);
    
            // Eğer Türkçe sonuç yoksa İngilizce dene
            if (empty($results['results']) && $language === 'tr-TR') {
                $url = "{$this->baseUrl}/discover/tv?api_key={$this->apiKey}&with_genres={$genreId}&page={$page}&sort_by=popularity.desc&language=en-US&include_adult=false";
                $results = $this->makeRequest($url);
            }
    
            // Dizileri topla, tekrar edenleri çıkar
            foreach ($results['results'] as $tv) {
                if (!in_array($tv['id'], $seenTVIds)) {
                    $combinedResults[] = $tv;
                    $seenTVIds[] = $tv['id'];
                }
            }
        }
    
        return ['results' => $combinedResults];
    }
    

    // API isteği yapma
    private function makeRequest($url) {
        // Debug için URL'yi logla
        error_log("TMDB API Request URL: " . $url);

        // cURL başlat
        $ch = curl_init();
        
        // cURL seçeneklerini ayarla
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: PHP/1.0'
            ]
        ]);
        
        // İsteği gönder
        $response = curl_exec($ch);
        
        // Hata kontrolü
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log("TMDB API CURL Error: " . $error);
            throw new Exception('CURL Hatası: ' . $error);
        }
        
        // HTTP durum kodu kontrolü
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Debug için yanıtı logla
        error_log("TMDB API Response Code: " . $httpCode);
        error_log("TMDB API Response: " . $response);
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = isset($errorData['status_message']) ? $errorData['status_message'] : 'Bilinmeyen hata';
            throw new Exception('API Hatası: ' . $errorMessage . ' (HTTP ' . $httpCode . ')');
        }
        
        // JSON yanıtını ayrıştır
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("TMDB API JSON Error: " . json_last_error_msg());
            throw new Exception('JSON Ayrıştırma Hatası: ' . json_last_error_msg());
        }
        
        return $data;
    }
}
?>