<?php
require_once __DIR__ . '/../config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['semester_id'])) {
    echo json_encode([]);
    exit;
}

$semester_id = (int)$_GET['semester_id'];
$subjects = $pdo->prepare("SELECT id, name, code FROM subjects WHERE semester_id = ? ORDER BY name");
$subjects->execute([$semester_id]);

echo json_encode($subjects->fetchAll(PDO::FETCH_ASSOC));
?>