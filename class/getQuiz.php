<?php
session_start();
require 'db_connect.php';

// Fetch quizzes and return JSON for frontend.
// Note: do NOT send correct answers to the client.
$sqlQuery = "SELECT id, question, options FROM quizzes";
$result = $dbConnect->query($sqlQuery);
header('Content-Type: application/json');
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch quizzes']);
    exit;
}

$data = $result->fetch_all(MYSQLI_ASSOC);
$result->free_result();

$records = [];
foreach ($data as $row) {
    $options = [];
    if (isset($row['options']) && $row['options'] !== null) {
        $options = array_map('trim', explode(',', (string)$row['options']));
    }

    $records[] = [
        'id' => (int)$row['id'],
        'question' => (string)$row['question'],
        'options' => $options,
    ];
}

echo json_encode($records);


?>
