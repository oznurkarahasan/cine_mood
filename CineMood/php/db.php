<?php
$host = "localhost";
$dbname = "db_cinemood";
$user = "postgres";
$pass = "123456";

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    throw new PDOException("Veritabanı bağlantı hatası: " . $e->getMessage());
}
?>
