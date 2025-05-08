<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

// Session kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Oturum açmanız gerekiyor'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    require 'db.php';
    
    $input = file_get_contents('php://input');
    error_log("Received input: " . $input);

    $data = json_decode($input, true);
    error_log("Decoded data: " . print_r($data, true));

    $avatar = $data['avatar'] ?? '';

    if (empty($avatar)) {
        throw new Exception('Avatar seçilmedi');
    }

    $user_id = $_SESSION['user_id'];
    error_log("Updating avatar for user_id: " . $user_id);
    
    $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
    $stmt->bindParam(':avatar', $avatar, PDO::PARAM_STR);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $result = $stmt->execute();

    if ($result) {
        $response = [
            'success' => true,
            'message' => 'Avatar başarıyla güncellendi'
        ];
    } else {
        throw new Exception('Avatar güncellenirken bir hata oluştu');
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $response = [
        'success' => false,
        'error' => 'Veritabanı hatası: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?> 