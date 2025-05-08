<?php
header('Content-Type: application/json');

try {
    $host = 'localhost';
    $dbname = "db_cinemood";
    $username = 'postgres';
    $password = '123456';
    $port = '5432';

    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Veritabanı bağlantı hatası: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Veritabanı bağlantısı kurulamadı: ' . $e->getMessage()]);
    exit;
}
?> 