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
    
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("SELECT first_name, created_at, avatar FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $response = [
            'success' => true,
            'first_name' => htmlspecialchars($user['first_name']),
            'created_at' => date("d.m.Y", strtotime($user['created_at'])),
            'avatar' => $user['avatar'] ?? 'blank-profile-picture-973460_1280.webp'
        ];
    } else {
        $response = [
            'success' => true,
            'first_name' => "Kullanıcı",
            'created_at' => "-",
            'avatar' => 'blank-profile-picture-973460_1280.webp'
        ];
    }

} catch (PDOException $e) {
    $response = [
        'success' => false,
        'error' => 'Veritabanı hatası'
    ];
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>

