<?php
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userid'], $_SESSION['usertype']) || $_SESSION['usertype'] !== 'user') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Expected payload:
// { "answers": [ { "quizId": 123, "selectedIndex": 0..3 | null } ] }
// Back-compat: selectedOption (string) is still accepted.
$postData = json_decode(file_get_contents('php://input'), true);
if (!is_array($postData) || !isset($postData['answers']) || !is_array($postData['answers'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
    exit;
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || !isset($postData['csrf_token']) || !is_string($postData['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $postData['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$answers = $postData['answers'];
$correctCount = 0;
$wrongCount = 0;
$incompleteCount = 0;

$quizStmt = $dbConnect->prepare('SELECT correctAnswer FROM quizzes WHERE id = ? LIMIT 1');
if (!$quizStmt) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
    exit;
}

$quizStmt->bind_param('i', $quizIdParam);
$quizStmt->bind_result($correctAnswerStr);

foreach ($answers as $answer) {
    if (!is_array($answer) || !isset($answer['quizId'])) {
        continue;
    }

    $quizId = filter_var($answer['quizId'], FILTER_VALIDATE_INT);
    if ($quizId === false || $quizId === null || $quizId <= 0) {
        continue;
    }

    // Prefer selectedIndex (stable even when options are shuffled client-side).
    $selectedIndex = null;
    if (array_key_exists('selectedIndex', $answer) && $answer['selectedIndex'] !== null) {
        $selectedIndex = filter_var($answer['selectedIndex'], FILTER_VALIDATE_INT);
        if ($selectedIndex === false || $selectedIndex === null) {
            $selectedIndex = null;
        }
    }

    // Back-compat: allow selectedOption string. (Less reliable if options contain duplicates/encoding issues.)
    $selectedOption = null;
    if ($selectedIndex === null && array_key_exists('selectedOption', $answer) && $answer['selectedOption'] !== null) {
        $selectedOption = trim((string)$answer['selectedOption']);
        if ($selectedOption === '') {
            $selectedOption = null;
        }
    }

    if ($selectedIndex === null && $selectedOption === null) {
        $incompleteCount++;
        continue;
    }

    $quizIdParam = $quizId;
    $correctAnswerStr = null;
    $quizStmt->execute();
    $quizStmt->store_result();
    if ($quizStmt->num_rows !== 1 || !$quizStmt->fetch()) {
        $incompleteCount++;
        continue;
    }

    $correctIndex = (int)$correctAnswerStr;
    if ($selectedIndex !== null) {
        if ($selectedIndex === $correctIndex) {
            $correctCount++;
        } else {
            $wrongCount++;
        }
        continue;
    }

    // Fallback string-compare path (only if selectedIndex wasn't provided).
    $optStmt = $dbConnect->prepare('SELECT options FROM quizzes WHERE id = ? LIMIT 1');
    if (!$optStmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server error']);
        exit;
    }
    $optStmt->bind_param('i', $quizId);
    $optStmt->execute();
    $optStmt->bind_result($optionsStr);
    $optionsStr = null;
    $optStmt->fetch();
    $optStmt->close();

    $options = array_map('trim', explode(',', (string)$optionsStr));
    $correctOption = $options[$correctIndex] ?? null;
    if ($correctOption === null) {
        $incompleteCount++;
        continue;
    }

    if (hash_equals($correctOption, $selectedOption)) {
        $correctCount++;
    } else {
        $wrongCount++;
    }
}

$quizStmt->close();

$userId = (int)$_SESSION['userid'];
$today = date('Y-m-d');

// Upsert user result
$select = $dbConnect->prepare('SELECT id FROM user_quiz_result WHERE user_id = ? LIMIT 1');
$select->bind_param('i', $userId);
$select->execute();
$existing = $select->get_result()->fetch_assoc();
$select->close();

if (!empty($existing)) {
    $update = $dbConnect->prepare('UPDATE user_quiz_result SET correct_answer = ?, wrong_answer = ?, incomplete_answer = ?, result_date = ? WHERE user_id = ?');
    $update->bind_param('iiisi', $correctCount, $wrongCount, $incompleteCount, $today, $userId);
    $ok = $update->execute();
    $update->close();
} else {
    $insert = $dbConnect->prepare('INSERT INTO user_quiz_result (user_id, correct_answer, wrong_answer, incomplete_answer, result_date) VALUES (?, ?, ?, ?, ?)');
    $insert->bind_param('iiiis', $userId, $correctCount, $wrongCount, $incompleteCount, $today);
    $ok = $insert->execute();
    $insert->close();
}

if (!isset($ok) || $ok !== true) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error saving data']);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Data saved successfully',
    'correctCount' => $correctCount,
    'wrongCount' => $wrongCount,
    'incompleteCount' => $incompleteCount,
]);


?>
