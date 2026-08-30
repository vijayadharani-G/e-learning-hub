<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$course_id   = isset($_POST['course_id']) ? trim($_POST['course_id']) : '';
$week_index  = isset($_POST['week_index']) ? (int) $_POST['week_index'] : -1;
$completed   = isset($_POST['completed']) && $_POST['completed'] === '1' ? 1 : 0;
$total_weeks = isset($_POST['total_weeks']) ? (int) $_POST['total_weeks'] : 4;

if ($course_id === '' || $week_index < 0) {
    echo json_encode(['success' => false, 'error' => 'Missing course_id or week_index']);
    exit;
}

// Confirm the student is actually enrolled before recording progress
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

// Upsert this week's completion state
$stmt = $conn->prepare(
    "INSERT INTO lecture_progress (user_id, course_id, week_index, completed)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE completed = VALUES(completed), updated_at = CURRENT_TIMESTAMP"
);
$stmt->bind_param("isii", $user_id, $course_id, $week_index, $completed);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Database error saving progress: ' . $stmt->error]);
    $stmt->close();
    exit;
}
$stmt->close();

// Recompute completed count for this course
$stmt_count = $conn->prepare(
    "SELECT COUNT(*) AS cnt FROM lecture_progress WHERE user_id = ? AND course_id = ? AND completed = 1"
);
$stmt_count->bind_param("is", $user_id, $course_id);
$stmt_count->execute();
$res_count = $stmt_count->get_result();
$completed_count = 0;
if ($row = $res_count->fetch_assoc()) {
    $completed_count = (int) $row['cnt'];
}
$stmt_count->close();

$all_complete = $total_weeks > 0 && $completed_count >= $total_weeks;

echo json_encode([
    'success'         => true,
    'completed_count' => $completed_count,
    'total_weeks'     => $total_weeks,
    'all_complete'    => $all_complete
]);