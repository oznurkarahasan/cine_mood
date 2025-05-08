<?php
require "db.php";

$sql = "SELECT * FROM mode";
$stmt = $pdo->query($sql);
$modes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($modes);
