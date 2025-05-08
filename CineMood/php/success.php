<?php
session_start();

// Check if registration was successful
if (!isset($_SESSION['registration_success']) {
    header("Location: /CineMood/register.html");
    exit();
}

// Clear the session after showing the message
unset($_SESSION['registration_success']);
$user_email = $_SESSION['user_email'] ?? '';
unset($_SESSION['user_email']);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Başarılı</title>
    <link rel="stylesheet" href="/CineMood/css/style.css">
</head>
<body>
    <div class="login-box">
        <h2>Kayıt Başarılı!</h2>
        <p>Merhaba, <strong><?php echo htmlspecialchars($user_email); ?></strong> hesabınız başarıyla oluşturuldu.</p>
        <p><a href="/CineMood/login.html" class="btn">Giriş Yap</a></p>
    </div>
</body>
</html>