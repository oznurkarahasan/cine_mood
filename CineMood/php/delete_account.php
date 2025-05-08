<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Oturum açmanız gerekiyor'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require 'db.php';

try {
    $user_id = $_SESSION['user_id'];
    
    // Önce kullanıcının e-posta adresini al
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception('Kullanıcı bulunamadı');
    }
    
    $email = $user['email'];
    
    // Transaction başlat
    $pdo->beginTransaction();
    
    try {
        // İlişkili kayıtları sil
        // 1. password_resets tablosundan
        $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        // 2. Son olarak users tablosundan
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Transaction'ı onayla
        $pdo->commit();
        
        // Session'ı temizle
        session_destroy();
        
        $response = [
            'success' => true,
            'message' => 'Hesabınız başarıyla silindi'
        ];
        
    } catch (PDOException $e) {
        // Hata durumunda transaction'ı geri al
        $pdo->rollBack();
        throw new Exception('Veritabanı hatası: ' . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log("Hesap silme hatası: " . $e->getMessage());
    $response = [
        'success' => false,
        'error' => 'Hesap silinirken hata oluştu: ' . $e->getMessage()
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
