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
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_SESSION['verified_email'] ?? '';

    // Validasyon
    if (empty($new_password)) {
        $_SESSION['error'] = "Yeni şifre gereklidir.";
        header("Location: /CineMood/reset-password.html");
        exit();
    }

    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "Şifre en az 8 karakter olmalıdır.";
        header("Location: /CineMood/reset-password.html");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Şifreler eşleşmiyor.";
        header("Location: /CineMood/reset-password.html");
        exit();
    }

    if (empty($email)) {
        $_SESSION['error'] = "Oturum süresi doldu. Lütfen tekrar deneyin.";
        header("Location: /CineMood/forgot-password.html");
        exit();
    }

    // Şifreyi hashle
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Şifreyi güncelle
    $stmt = $pdo->prepare("UPDATE users 
                          SET password = :password 
                          WHERE email = :email");
    $stmt->execute([
        'password' => $hashed_password,
        'email' => $email
    ]);

    // Session'ı temizle
    unset($_SESSION['verified_email']);
    unset($_SESSION['email']);

    // Başarılı mesajı ile login sayfasına yönlendir
    $_SESSION['success'] = "Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.";
    header("Location: /CineMood/login.html");

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['error'] = "Bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
    header("Location: /CineMood/reset-password.html");
    exit();
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    $_SESSION['error'] = "Bir hata oluştu. Lütfen daha sonra tekrar deneyin.";
    header("Location: /CineMood/reset-password.html");
    exit();
}
?> 