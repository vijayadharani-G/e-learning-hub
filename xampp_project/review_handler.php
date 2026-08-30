<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$course_id = isset($_POST['course_id']) ? trim($_POST['course_id']) : '';
$rating    = isset($_POST['rating']) ? (int) $_POST['rating'] : 0;
$comment   = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($course_id === '' || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'error' => 'Missing course_id or invalid rating']);
    exit;
}

// Confirm the student is actually enrolled before allowing a review
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

// Upsert this student's review for the course
$stmt = $conn->prepare(
    "INSERT INTO course_reviews (user_id, course_id, rating, comment)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), created_at = CURRENT_TIMESTAMP"
);
$stmt->bind_param("isis", $user_id, $course_id, $rating, $comment);

if (!$stmt->execute()) {
    $stmt->close();
    echo json_encode(['success' => false, 'error' => 'Database error saving review']);
    exit;
}
$stmt->close();

// Rebuild the reviews list + summary so the frontend can refresh instantly
$all_reviews = [];
$stmt_rev = $conn->prepare(
    "SELECT r.rating, r.comment, r.created_at, u.username
     FROM course_reviews r
     JOIN users u ON u.id = r.user_id
     WHERE r.course_id = ?
     ORDER BY r.created_at DESC"
);
$stmt_rev->bind_param("s", $course_id);
$stmt_rev->execute();
$res_rev = $stmt_rev->get_result();
while ($row = $res_rev->fetch_assoc()) {
    $all_reviews[] = $row;
}
$stmt_rev->close();

$review_count = count($all_reviews);
$review_avg = $review_count > 0 ? array_sum(array_column($all_reviews, 'rating')) / $review_count : 0;

// Build the HTML for the reviews list (same markup as course.php)
ob_start();
if (empty($all_reviews)) {
    echo '<p class="no-reviews-msg">No reviews yet — be the first to share your experience!</p>';
} else {
    foreach ($all_reviews as $rev) {
        echo '<div class="review-item">';
        echo '<div class="review-item-head">';
        echo '<span class="review-item-user">' . htmlspecialchars($rev['username']) . '</span>';
        echo '<span class="review-item-date">' . date('M j, Y', strtotime($rev['created_at'])) . '</span>';
        echo '</div>';
        echo '<div class="review-item-stars">' . str_repeat('★', (int) $rev['rating']) . str_repeat('☆', 5 - (int) $rev['rating']) . '</div>';
        if (!empty($rev['comment'])) {
            echo '<p class="review-item-comment">' . nl2br(htmlspecialchars($rev['comment'])) . '</p>';
        }
        echo '</div>';
    }
}
$reviews_html = ob_get_clean();

echo json_encode([
    'success'      => true,
    'reviews_html' => $reviews_html,
    'avg_rating'   => $review_avg,
    'review_count' => $review_count
]);