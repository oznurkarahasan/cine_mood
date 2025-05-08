<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Oturum açmanız gerekiyor'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_POST['new_name']) || empty($_POST['new_name'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Yeni isim boş olamaz'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require 'db.php';

try {
    $user_id = $_SESSION['user_id'];
    $new_name = trim($_POST['new_name']);
    
    $stmt = $pdo->prepare("UPDATE users SET first_name = :name WHERE id = :id");
    $stmt->bindParam(':name', $new_name, PDO::PARAM_STR);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $response = [
            'success' => true,
            'message' => 'İsminiz başarıyla güncellendi'
        ];
    } else {
        $response = [
            'success' => false,
            'error' => 'İsim güncellenirken bir hata oluştu'
        ];
    }
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'İsim güncellenirken bir hata oluştu: ' . $e->getMessage()
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
