<?php
// Hata raporlamayı aktif et
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        // E-posta adresinin geçerli olup olmadığını kontrol et
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Geçersiz e-posta adresi']);
            exit;
        }

        // Veritabanında e-posta adresini kontrol et
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Sorgu hazırlama hatası: " . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Sorgu çalıştırma hatası: " . $stmt->error);
        }
        
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Rastgele 6 haneli kod oluştur
            $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Şifreyi hashle
            $hashed_password = password_hash($verification_code, PASSWORD_DEFAULT);
            
            // Veritabanında şifreyi güncelle
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            if (!$update_stmt) {
                throw new Exception("Güncelleme sorgusu hazırlama hatası: " . $conn->error);
            }
            
            $update_stmt->bind_param("ss", $hashed_password, $email);
            if (!$update_stmt->execute()) {
                throw new Exception("Güncelleme sorgusu çalıştırma hatası: " . $update_stmt->error);
            }

            // Başarılı mesajı ve yeni şifreyi döndür
            echo json_encode([
                'success' => true, 
                'message' => 'Şifreniz başarıyla sıfırlandı.',
                'new_password' => $verification_code
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Bu e-posta adresi ile kayıtlı bir hesap bulunamadı.']);
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Şifre sıfırlama hatası: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek.']);
}
?> 