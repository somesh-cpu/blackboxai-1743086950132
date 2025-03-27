<?php
require_once __DIR__ . '/../config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['department_id'])) {
    echo json_encode([]);
    exit;
}

$department_id = (int)$_GET['department_id'];
$courses = $pdo->prepare("SELECT id, name FROM courses WHERE department_id = ? ORDER BY name");
$courses->execute([$department_id]);

echo json_encode($courses->fetchAll(PDO::FETCH_ASSOC));
?>