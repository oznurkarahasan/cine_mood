<?php
$host = "localhost";
$dbname = "db_cinemood";
$user = "postgres";
$password = "123456";

try {
    // PDO ile veritabanı bağlantısı
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Formdan gelen verileri al
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Kullanıcıyı e-posta ile sorgula
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Şifreyi doğrula
        if (password_verify($password, $user['password'])) {
            // Oturum başlat ve introAnimate'e yönlendir
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['isim'];
            header("Location: /CineMood/introAnimate.html");
            exit;
        }
    } 
    
    // Şifre yanlışsa veya kullanıcı yoksa fail.html'e yönlendir
    header("Location: /CineMood/fail.html");
    exit();

} catch (PDOException $e) {
    // Hata mesajı
    echo "Bir hata oluştu: " . $e->getMessage();
}
?>
