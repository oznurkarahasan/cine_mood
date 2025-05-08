<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$host = "localhost";
$dbname = "db_cinemood";
$user = "postgres";
$password = "123456";
try {
    // PDO connection
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Form verilerini al
    $verification_code = $_POST['verification_code'] ?? '';
    $email = $_SESSION['email'] ?? '';

    // Validasyon
    if (empty($verification_code)) {
        $_SESSION['error'] = "Doğrulama kodu gereklidir.";
        header("Location: /CineMood/verify-code.html");
        exit();
    }

    if (empty($email)) {
        $_SESSION['error'] = "Oturum süresi doldu. Lütfen tekrar deneyin.";
        header("Location: /CineMood/forgot-password.html");
        exit();
    }

    // Kodu veritabanında kontrol et
    $stmt = $pdo->prepare("SELECT * FROM password_resets 
                          WHERE email = :email 
                          AND verification_code = :code 
                          AND created_at > NOW() - INTERVAL '1 hour'
                          ORDER BY created_at DESC 
                          LIMIT 1");
    $stmt->execute([
        'email' => $email,
        'code' => $verification_code
    ]);

    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Geçersiz veya süresi dolmuş doğrulama kodu.";
        header("Location: /CineMood/verify-code.html");
        exit();
    }

    // Kodu kullanıldı olarak işaretle
    $stmt = $pdo->prepare("UPDATE password_resets 
                          SET used = true 
                          WHERE email = :email 
                          AND verification_code = :code");
    $stmt->execute([
        'email' => $email,
        'code' => $verification_code
    ]);

    // Yeni şifre sayfasına yönlendir
    $_SESSION['verified_email'] = $email;
    header("Location: /CineMood/reset-password.html");

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error'] = "Bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
    header("Location: /CineMood/verify-code.html");
    exit();
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    $_SESSION['error'] = "Bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
    header("Location: /CineMood/verify-code.html");
    exit();
}
?> 