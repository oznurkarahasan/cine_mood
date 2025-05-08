<?php
// Hata raporlamayı kapat (JSON yanıtlarını bozmamak için)
error_reporting(0);
ini_set('display_errors', 0);

// JSON header'ı ekle
header('Content-Type: application/json');

session_start();

// Veritabanı bağlantısını kontrol et
if (!isset($pdo)) {
    require_once 'db_connection.php';
}

// Kullanıcı giriş yapmamışsa hata döndür
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Oturum açmanız gerekiyor']);
    exit;
}

$user_id = $_SESSION['user_id'];

// POST isteği ile favori ekleme/çıkarma
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['content_id']) || !isset($data['content_type']) || !isset($data['title'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Eksik parametreler']);
        exit;
    }

    $content_id = $data['content_id'];
    $content_type = $data['content_type'];
    $title = $data['title'];
    $poster_path = $data['poster_path'] ?? null;

    try {
        // Önce favori var mı kontrol et
        $check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND content_id = ? AND content_type = ?");
        $check->execute([$user_id, $content_id, $content_type]);
        $exists = $check->fetch();

        if ($exists) {
            // Favoriyi kaldır
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND content_id = ? AND content_type = ?");
            $stmt->execute([$user_id, $content_id, $content_type]);
            echo json_encode(['status' => 'removed']);
        } else {
            // Favori ekle
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, content_id, content_type, title, poster_path) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $content_id, $content_type, $title, $poster_path]);
            echo json_encode(['status' => 'added']);
        }
    } catch (PDOException $e) {
        error_log("Favori işlemi hatası: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}

// GET isteği ile kullanıcının favorilerini getir
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT content_id, content_type, title, poster_path FROM favorites WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['favorites' => $favorites]);
    } catch (PDOException $e) {
        error_log("Favorileri getirme hatası: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Veritabanı hatası: ' . $e->getMessage()]);
    }
}
?> 