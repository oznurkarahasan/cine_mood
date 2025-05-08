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
    $first_name = $_POST['first_name'] ?? '';
    $last_name = $_POST['last_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Hata mesajları için dizi
    $errors = [];

    // Validasyonlar
    if (empty($first_name)) {
        $errors[] = "Ad alanı zorunludur.";
    }

    if (empty($last_name)) {
        $errors[] = "Soyad alanı zorunludur.";
    }

    if (empty($email)) {
        $errors[] = "E-posta alanı zorunludur.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir e-posta adresi giriniz.";
    }

    if (empty($phone)) {
        $errors[] = "Telefon numarası alanı zorunludur.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Geçerli bir telefon numarası giriniz (10 haneli).";
    }

    if (empty($birth_date)) {
        $errors[] = "Doğum tarihi alanı zorunludur.";
    } else {
        $birth_date_obj = new DateTime($birth_date);
        $today = new DateTime();
        $age = $today->diff($birth_date_obj)->y;
        if ($age < 13) {
            $errors[] = "13 yaşından küçük kullanıcılar kayıt olamaz.";
        }
    }

    if (empty($password)) {
        $errors[] = "Şifre alanı zorunludur.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Şifre en az 8 karakter olmalıdır.";
    }

    if ($password !== $password_confirm) {
        $errors[] = "Şifreler eşleşmiyor.";
    }

    // E-posta adresi kontrolü
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Bu e-posta adresi zaten kayıtlı.";
    }

    // Telefon numarası kontrolü
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = :phone");
    $stmt->execute(['phone' => $phone]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Bu telefon numarası zaten kayıtlı.";
    }

    // Hata varsa geri dön
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        header("Location: /CineMood/failRegister.html");
        exit();
    }

    // Şifreyi hashle
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Kullanıcıyı veritabanına ekle
    $sql = "INSERT INTO users (first_name, last_name, email, phone, birth_date, password) 
            VALUES (:first_name, :last_name, :email, :phone, :birth_date, :password)";
    $stmt = $pdo->prepare($sql);
    
    $params = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'phone' => $phone,
        'birth_date' => $birth_date,
        'password' => $hashed_password
    ];
    
    if ($stmt->execute($params)) {
        $_SESSION['register_success'] = true;
        $_SESSION['user_email'] = $email; // Başarılı kayıt sonrası e-posta bilgisini session'a kaydet
        header("Location: /CineMood/success.html");
    } else {
        $_SESSION['register_errors'] = ["Kayıt işlemi sırasında bir hata oluştu."];
        header("Location: /CineMood/register.html");
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    $_SESSION['register_errors'] = ["Veritabanı hatası oluştu. Lütfen daha sonra tekrar deneyin."];
    header("Location: /CineMood/register.html");
    exit();
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    $_SESSION['register_errors'] = ["Bir hata oluştu. Lütfen daha sonra tekrar deneyin."];
    header("Location: /CineMood/register.html");
    exit();
}
?>