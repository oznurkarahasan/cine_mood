<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Giriş yapılmamış.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$query = $db->prepare("SELECT first_name FROM users WHERE id = :id");
$query->execute(['id' => $user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(['first_name' => $user['first_name']]);
} else {
    echo json_encode(['error' => 'Kullanıcı bulunamadı.']);
}
