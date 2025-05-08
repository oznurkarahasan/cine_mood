<?php
require "db.php";

$mode_id = $_GET['mode'] ?? null;

if ($mode_id) {
    $sql = "SELECT * FROM cinemood WHERE mode= ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$mode_id]);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($movies);
} else {
    echo json_encode([]);
}
