<?php
require_once __DIR__ . '/../config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['course_id'])) {
    echo json_encode([]);
    exit;
}

$course_id = (int)$_GET['course_id'];
$semesters = $pdo->prepare("SELECT id, name FROM semesters WHERE course_id = ? ORDER BY semester_number");
$semesters->execute([$course_id]);

echo json_encode($semesters->fetchAll(PDO::FETCH_ASSOC));
?>