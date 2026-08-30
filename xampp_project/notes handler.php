<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$course_id  = isset($_POST['course_id']) ? trim($_POST['course_id']) : '';
$week_index = isset($_POST['week_index']) ? (int) $_POST['week_index'] : -1;
$note_text  = isset($_POST['note_text']) ? trim($_POST['note_text']) : '';

if ($course_id === '' || $week_index < 0) {
    echo json_encode(['success' => false, 'error' => 'Missing course_id or week_index']);
    exit;
}

$stmt_chk = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND course_id = ?");
$stmt_chk->bind_param("is", $user_id, $course_id);
$stmt_chk->execute();
$res_chk = $stmt_chk->get_result();
if ($res_chk->num_rows === 0) {
    $stmt_chk->close();
    echo json_encode(['success' => false, 'error' => 'Not enrolled in this course']);
    exit;
}
$stmt_chk->close();

$stmt = $conn->prepare(
    "INSERT INTO lecture_notes (user_id, course_id, week_index, note_text)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE note_text = VALUES(note_text)"
);
$stmt->bind_param("isis", $user_id, $course_id, $week_index, $note_text);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();

