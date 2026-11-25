<?php
require_once 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Handle Enrollment request from registered users clicking "Quick Enroll"
if (isset($_GET['enroll']) && $user_id) {
    $enroll_course_id = trim($_GET['enroll']);

    // Check if already enrolled
    $stmt_chk = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND course_id = ?");
    $stmt_chk->bind_param("is", $user_id, $enroll_course_id);
    $stmt_chk->execute();
    $res_chk = $stmt_chk->get_result();

    if ($res_chk->num_rows === 0) {
        $stmt_ins = $conn->prepare("INSERT INTO registrations (user_id, course_id) VALUES (?, ?)");
        $stmt_ins->bind_param("is", $user_id, $enroll_course_id);
        $stmt_ins->execute();
        $stmt_ins->close();
    }
    $stmt_chk->close();

    // AJAX call (used right after a mock payment succeeds) — respond with
    // a small JSON payload instead of redirecting, so the page doesn't reload.
    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'course_id' => $enroll_course_id]);
        exit;
    }

    header("Location: courses.php?enrolled_ok=1");
    exit;
}

// Fetch enrolled course IDs if logged in
$enrolled_ids = [];
if ($user_id) {
    $stmt_reg = $conn->prepare("SELECT course_id FROM registrations WHERE user_id = ?");
    $stmt_reg->bind_param("i", $user_id);
    $stmt_reg->execute();
    $res_reg = $stmt_reg->get_result();
    while ($row = $res_reg->fetch_assoc()) {
        $enrolled_ids[] = $row['course_id'];
    }
    $stmt_reg->close();
}

// ---- NEW: per-course completed-week counts, for the "X% complete" bars ----
$progress_by_course = []; // course_id => completed_count
$COURSE_TOTAL_WEEKS = 4;   // matches $syllabus_items count in course_detail.php
if ($user_id) {
    $stmt_prog_all = $conn->prepare(
        "SELECT course_id, COUNT(*) AS cnt
         FROM lecture_progress
         WHERE user_id = ? AND completed = 1
         GROUP BY course_id"
    );
    $stmt_prog_all->bind_param("i", $user_id);
    $stmt_prog_all->execute();
    $res_prog_all = $stmt_prog_all->get_result();
    while ($row = $res_prog_all->fetch_assoc()) {
        $progress_by_course[$row['course_id']] = (int) $row['cnt'];
    }
    $stmt_prog_all->close();
}

// Fetch the courses the student picked at registration time (interested_courses).
// These are already "unlocked" — no repeat payment needed to register for them here.
$interested_ids = [];
if ($user_id) {
    $stmt_int = $conn->prepare("SELECT course_id FROM interested_courses WHERE user_id = ?");
    if ($stmt_int !== false) {
        $stmt_int->bind_param("i", $user_id);
        $stmt_int->execute();
        $res_int = $stmt_int->get_result();
        while ($row = $res_int->fetch_assoc()) {
            $interested_ids[] = $row['course_id'];
        }
        $stmt_int->close();
    }
}

// ---- NEW: Student identity for the payment modal (name + email) ----
// Assumes a `users` table with an `email` column. Adjust the column name
// below if your schema differs.
$student_name = $user_id ? $_SESSION['username'] : '';
$student_email = '';
if ($user_id) {
    $stmt_email = $conn->prepare("SELECT email FROM users WHERE id = ?");
    if ($stmt_email !== false) {
        $stmt_email->bind_param("i", $user_id);
        $stmt_email->execute();
        $res_email = $stmt_email->get_result();
        if ($row_email = $res_email->fetch_assoc()) {
            $student_email = $row_email['email'];
        }
        $stmt_email->close();
    }
}

// Mock per-course pricing, tiered by course level (same model as register.php).
// Real deployments should pull an actual `price` column instead.
function get_course_price($level) {
    switch ($level) {
        case 'Advanced':     return 599;
        case 'Intermediate': return 399;
        default:              return 299; // Beginner
    }
}

// Fetch all courses
$courses_res = $conn->query("SELECT * FROM courses");
$courses = [];
if ($courses_res) {
    while ($row = $courses_res->fetch_assoc()) {
        $courses[] = $row;
    }
}

// ---- Extra content helpers: catalog stats ----
$total_courses_count = count($courses);
$total_categories_count = count(array_unique(array_column($courses, 'category')));
$total_students_sum = array_sum(array_column($courses, 'students_enrolled'));
$avg_rating_value = $total_courses_count > 0
    ? array_sum(array_column($courses, 'rating')) / $total_courses_count
    : 0;

// Determine the single "Most Popular" course (highest students_enrolled) to badge it
$most_popular_course_id = null;
if (!empty($courses)) {
    $top = $courses[0];
    foreach ($courses as $c) {
        if ((int)$c['students_enrolled'] > (int)$top['students_enrolled']) {
            $top = $c;
        }
    }
    $most_popular_course_id = $top['id'];
}

// ---- Enroll success flag (shown as a dismissible banner) ----
$show_enrolled_banner = isset($_GET['enrolled_ok']) && $_GET['enrolled_ok'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Course Catalog & Student Portal | E-Learning Hub</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <style>
    /* Additional custom helper states inside dashboard lists */
    .dashboard-hero {
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.82), rgba(67, 56, 202, 0.78)),
                  url('/e-learning-hub/src/assets/images/dash.jpg');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 50px;
      border-radius: var(--radius-lg);
      margin-bottom: 40px;
    }
    .dashboard-hero h1 { color: white; font-size: 2rem; margin-bottom: 6px; }
    .dashboard-hero p { color: #c7d2fe; font-size: 14px; line-height: 1.6; }
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 24px;
      margin-bottom: 50px;
    }
    .dash-card {
      background: white;
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 24px;
      display: flex;
      flex-direction: column;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }
    .dash-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); border-color: var(--primary); }
    .dash-card h4 { font-size: 1rem; margin-bottom: 8px; line-clamp: 1; }
    .dash-card p { font-size: 13px; color: var(--gray-500); line-clamp: 2; margin-bottom: 16px; flex-grow:1; }
    .dash-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-top: 1px solid var(--gray-50);
      padding-top: 12px;
      font-size: 11px;
    }

    /* Fallback helper for smooth rendering if image takes time to load */
    .card-img-box {
      background-color: #f1f5f9;
      position: relative;
    }

    /* --- New: Guest visitor hero banner with background image --- */
    .catalog-guest-hero {
      position: relative;
      background: linear-gradient(135deg, rgba(75, 77, 85, 0.86), rgba(107, 104, 141, 0.82)),
                  url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1400&q=80');
      background-size: cover;
      background-position: center;
      border-radius: var(--radius-lg);
      padding: 64px 40px;
      margin-bottom: 40px;
      text-align: center;
      color: white;
      overflow: hidden;
    }
    .catalog-guest-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,255,255,0.14);
      border: 1px solid rgba(255,255,255,0.25);
      padding: 6px 16px;
      border-radius: 50px;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: 0.3px;
      margin-bottom: 18px;
    }
    .catalog-guest-hero h1 {
      color: white;
      font-size: 2.4rem;
      letter-spacing: -1px;
      margin-bottom: 14px;
    }
    .catalog-guest-hero p {
      color: #dbe0ff;
      font-size: 14px;
      line-height: 1.7;
      max-width: 620px;
      margin: 0 auto;
    }
    .catalog-guest-cta {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin-top: 28px;
      flex-wrap: wrap;
    }

    /* --- New: Catalog quick-stats strip --- */
    .catalog-stats-strip {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      background: white;
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-lg);
      padding: 24px;
      margin-bottom: 30px;
      box-shadow: var(--shadow-sm);
      text-align: center;
    }
    .catalog-stat-item h3 {
      font-size: 1.6rem;
      background: var(--accent-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 2px;
    }
    .catalog-stat-item p {
      font-size: 11px;
      font-family: var(--font-mono);
      color: var(--gray-500);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* --- New: Popular badge ribbon on course card --- */
    .popular-badge {
      position: absolute;
      top: 16px;
      right: 16px;
      background: linear-gradient(135deg, #f59e0b, #ef4444);
      color: white;
      font-size: 10px;
      font-weight: 700;
      padding: 6px 10px;
      border-radius: var(--radius-sm);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }

    /* --- New: results toolbar (count + sort) --- */
    .results-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .results-count {
      font-size: 13px;
      color: var(--gray-500);
      font-weight: 500;
    }
    .results-count strong { color: var(--gray-700); }
    .sort-select-wrapper label {
      font-size: 12px;
      font-weight: 600;
      color: var(--gray-600);
      margin-right: 8px;
    }
    #course-sort-select {
      background-color: var(--gray-50);
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 500;
      color: var(--gray-700);
      cursor: pointer;
    }
    #course-sort-select:focus { border-color: var(--primary); }

    /* --- New: empty state when search/filter returns nothing --- */
    #no-results-state {
      display: none;
      text-align: center;
      padding: 60px 20px;
      background: white;
      border: 2px dashed var(--gray-300);
      border-radius: var(--radius-lg);
    }
    #no-results-state h3 { margin-bottom: 8px; }

    /* --- New: level filter row (Beginner / Intermediate / Advanced) --- */
    .level-filters {
      display: flex;
      gap: 8px;
      align-items: center;
      margin-top: 12px;
      padding-top: 12px;
      border-top: 1px dashed var(--gray-100);
      flex-wrap: wrap;
    }
    .level-filters span.level-filter-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--gray-500);
      font-family: var(--font-mono);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-right: 4px;
    }
    .level-btn {
      background-color: var(--gray-50);
      color: var(--gray-600);
      font-size: 12px;
      font-weight: 600;
      padding: 6px 14px;
      border-radius: 50px;
      border: 1px solid var(--gray-100);
      cursor: pointer;
      transition: var(--transition-fast);
    }
    .level-btn.active, .level-btn:hover {
      background: var(--accent-gradient);
      color: var(--white);
      border-color: transparent;
    }

    /* --- New: Wishlist / Save-for-later heart icon on cards --- */
    .wishlist-btn {
      position: absolute;
      top: 16px;
      left: 16px;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(4px);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      transition: var(--transition-fast);
      border: none;
      z-index: 2;
    }
    .wishlist-btn:hover { transform: scale(1.1); }
    .wishlist-btn svg {
      width: 17px;
      height: 17px;
      stroke: #64748b;
      fill: none;
      stroke-width: 2;
      transition: var(--transition-fast);
    }
    .wishlist-btn.saved svg {
      fill: #ef4444;
      stroke: #ef4444;
    }

    /* --- New: Wishlist quick-access bar --- */
    .wishlist-bar {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 600;
      color: var(--gray-600);
      background: var(--white);
      border: 1px solid var(--gray-100);
      padding: 8px 14px;
      border-radius: 50px;
      cursor: pointer;
      transition: var(--transition-fast);
    }
    .wishlist-bar:hover { border-color: var(--primary); color: var(--primary); }
    .wishlist-bar svg { width: 14px; height: 14px; stroke: #ef4444; fill: #ef4444; }
    #wishlist-count-num {
      background: var(--accent-gradient);
      color: white;
      font-size: 10px;
      padding: 1px 7px;
      border-radius: 50px;
    }

    /* --- New: Scroll-to-top floating button --- */
    #scroll-top-btn {
      position: fixed;
      bottom: 28px;
      right: 28px;
      width: 46px;
      height: 46px;
      border-radius: 50%;
      background: var(--accent-gradient);
      color: white;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: var(--shadow-lg);
      opacity: 0;
      pointer-events: none;
      transform: translateY(10px);
      transition: var(--transition);
      z-index: 90;
    }
    #scroll-top-btn.visible {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0);
    }
    #scroll-top-btn:hover { transform: translateY(-3px); box-shadow: var(--shadow-glow); }
    #scroll-top-btn svg { width: 20px; height: 20px; }
    #no-results-state p { font-size: 13px; color: var(--gray-500); }

    /* --- New: Enroll success toast banner --- */
    #enroll-success-toast {
      position: fixed;
      top: 24px;
      right: 24px;
      background: linear-gradient(135deg, #16a34a, #15803d);
      color: white;
      padding: 16px 22px;
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-lg);
      display: flex;
      align-items: center;
      gap: 12px;
      z-index: 999;
      font-size: 13px;
      font-weight: 600;
      transform: translateX(30px);
      opacity: 0;
      animation: toastIn 0.35s ease-out forwards, toastOut 0.35s ease-in forwards 3.2s;
    }
    #enroll-success-toast svg { width: 20px; height: 20px; flex-shrink: 0; }
    @keyframes toastIn {
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes toastOut {
      to { transform: translateX(30px); opacity: 0; }
    }

    /* --- New: Custom Enroll Confirmation Modal --- */
    #enroll-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(2px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      padding: 20px;
    }
    #enroll-modal-overlay.visible { display: flex; }
    .enroll-modal-box {
      background: white;
      border-radius: var(--radius-lg);
      max-width: 380px;
      width: 100%;
      padding: 28px 26px;
      box-shadow: var(--shadow-lg);
      text-align: center;
      transform: scale(0.92);
      opacity: 0;
      animation: modalPop 0.2s ease-out forwards;
    }
    @keyframes modalPop {
      to { transform: scale(1); opacity: 1; }
    }
    .enroll-modal-icon {
      width: 54px;
      height: 54px;
      border-radius: 50%;
      background: #eef2ff;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
    }
    .enroll-modal-icon svg { width: 26px; height: 26px; stroke: var(--primary); fill: none; stroke-width: 2; }
    .enroll-modal-box h3 { font-size: 16px; margin-bottom: 8px; }
    .enroll-modal-box p { font-size: 13px; color: var(--gray-500); line-height: 1.6; margin-bottom: 24px; }
    .enroll-modal-box p strong { color: var(--gray-700); }
    .enroll-modal-actions { display: flex; gap: 10px; }
    .enroll-modal-actions button {
      flex: 1;
      padding: 11px 0;
      border-radius: var(--radius-md);
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: var(--transition-fast);
    }
    #enroll-modal-cancel { background: var(--gray-100); color: var(--gray-700); }
    #enroll-modal-cancel:hover { background: var(--gray-300); }
    #enroll-modal-confirm { background: var(--accent-gradient); color: white; }
    #enroll-modal-confirm:hover { opacity: 0.9; }

    /* --- NEW: Locked course price badge (courses not picked at registration) --- */
    .locked-price-badge {
      position: absolute;
      bottom: 12px;
      right: 12px;
      display: flex;
      align-items: center;
      gap: 4px;
      background: rgba(15, 23, 42, 0.85);
      color: #f3e8ff;
      font-size: 11px;
      font-weight: 700;
      padding: 5px 10px;
      border-radius: 50px;
      font-family: var(--font-mono);
      letter-spacing: 0.3px;
      z-index: 2;
    }
    .locked-price-badge svg { width: 11px; height: 11px; stroke: #d8b4fe; fill: none; stroke-width: 2.5; }
    .course-card.locked-course .card-img-box { opacity: 0.92; }

    /* --- NEW: Mock payment modal (same pattern as register.php) --- */
    #payment-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.65);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1001;
      padding: 16px;
    }
    #payment-modal-overlay.active { display: flex; }
    .payment-modal-box {
      background: #ffffff;
      border-radius: var(--radius-lg, 12px);
      max-width: 380px;
      width: 100%;
      padding: 28px 26px;
      text-align: center;
      box-shadow: var(--shadow-lg);
      animation: modalPop 0.2s ease-out forwards;
    }
    .payment-modal-icon {
      width: 56px; height: 56px; border-radius: 50%;
      background: linear-gradient(135deg, #6366f1, #ec4899);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px;
    }
    .payment-modal-icon svg { width: 28px; height: 28px; stroke: white; fill: none; stroke-width: 2; }
    .payment-modal-box h3 { font-size: 16px; margin-bottom: 8px; color: var(--gray-800, #1e293b); }
    .payment-modal-box p { font-size: 13px; color: var(--gray-500); margin-bottom: 18px; line-height: 1.5; }
    .payment-amount { font-family: var(--font-mono); font-size: 28px; font-weight: 700; color: #4f46e5; margin-bottom: 20px; }
    .payment-student-info {
      background: var(--gray-50, #f8fafc);
      border: 1px solid var(--gray-100, #f1f5f9);
      border-radius: var(--radius-sm, 8px);
      padding: 14px 16px;
      margin-bottom: 18px;
      text-align: left;
    }
    .psi-row {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      font-size: 12px;
      padding: 5px 0;
      border-bottom: 1px dashed var(--gray-100, #e2e8f0);
    }
    .psi-row:last-child { border-bottom: none; }
    .psi-row span { color: var(--gray-500, #64748b); flex-shrink: 0; }
    .psi-row strong { color: var(--gray-800, #1e293b); font-weight: 600; text-align: right; word-break: break-word; }
    .payment-modal-actions { display: flex; flex-direction: column; gap: 10px; }
    .payment-pay-btn {
      background: linear-gradient(135deg, #6366f1, #ec4899);
      color: white; border: none; border-radius: var(--radius-sm, 8px);
      padding: 12px 0; font-weight: 600; font-size: 14px; cursor: pointer; width: 100%;
    }
    .payment-cancel-btn {
      background: transparent; border: 1px solid var(--gray-100);
      color: var(--gray-500); border-radius: var(--radius-sm, 8px);
      padding: 11px 0; font-weight: 500; font-size: 13px; cursor: pointer; width: 100%;
    }
    .payment-spinner {
      width: 48px; height: 48px; border-radius: 50%;
      border: 4px solid #e0e7ff; border-top-color: #4f46e5;
      margin: 0 auto 18px; animation: paywallSpin 0.8s linear infinite;
    }
    @keyframes paywallSpin { to { transform: rotate(360deg); } }
    .payment-success-icon {
      width: 56px; height: 56px; border-radius: 50%;
      background: linear-gradient(135deg, #22c55e, #16a34a);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px; animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .payment-success-icon svg { width: 26px; height: 26px; stroke: white; fill: none; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }

    /* --- NEW: Mock payment receipt shown after success --- */
    .payment-receipt-box {
      background: var(--gray-50, #f8fafc);
      border: 1px dashed var(--gray-300, #cbd5e1);
      border-radius: var(--radius-sm, 8px);
      padding: 14px 16px;
      margin: 4px 0 18px;
      text-align: left;
    }
    .receipt-row {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      font-size: 12px;
      padding: 5px 0;
      border-bottom: 1px dashed var(--gray-100, #e2e8f0);
    }
    .receipt-row:last-child { border-bottom: none; }
    .receipt-row span { color: var(--gray-500, #64748b); flex-shrink: 0; }
    .receipt-row strong { color: var(--gray-800, #1e293b); font-weight: 600; text-align: right; word-break: break-word; }
    .payment-receipt-download-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: white;
      border: 1px solid var(--gray-300, #cbd5e1);
      color: #4f46e5;
      border-radius: var(--radius-sm, 8px);
      padding: 11px 0;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer;
      width: 100%;
      transition: var(--transition-fast, 0.15s);
    }
    .payment-receipt-download-btn:hover { background: #eef2ff; border-color: #4f46e5; }
    .payment-receipt-download-btn svg { width: 15px; height: 15px; stroke: #4f46e5; fill: none; stroke-width: 2; }

    /* --- NEW: auto-registered confirmation state (post payment) --- */
    .registered-badge-link {
      background: linear-gradient(135deg, #16a34a, #15803d) !important;
      cursor: pointer;
    }

    /* --- NEW: Colorful Wishlist Modal (replaces alert()) --- */
    #wishlist-modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.6);
      backdrop-filter: blur(3px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1002;
      padding: 20px;
    }
    #wishlist-modal-overlay.visible { display: flex; }

    .wishlist-modal-box {
      background: linear-gradient(160deg, #ffffff, #f5f3ff);
      border-radius: var(--radius-lg, 16px);
      max-width: 420px;
      width: 100%;
      max-height: 80vh;
      overflow-y: auto;
      padding: 26px 24px;
      box-shadow: 0 20px 50px rgba(99, 102, 241, 0.25);
      transform: scale(0.92);
      opacity: 0;
      animation: modalPop 0.2s ease-out forwards;
    }

    .wishlist-modal-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 18px;
    }
    .wishlist-modal-header svg {
      width: 26px; height: 26px;
      stroke: #ef4444; fill: #ef4444;
    }
    .wishlist-modal-header h3 {
      font-size: 17px;
      background: linear-gradient(135deg, #6366f1, #ec4899);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .wishlist-modal-empty {
      text-align: center;
      padding: 30px 10px;
      color: var(--gray-500);
      font-size: 13px;
    }

    .wishlist-item-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 14px;
      border-radius: 12px;
      margin-bottom: 10px;
      font-size: 13px;
      font-weight: 600;
      color: #1e293b;
      animation: wishItemIn 0.25s ease-out backwards;
    }
    /* alternating colorful backgrounds */
    .wishlist-item-row:nth-child(5n+1) { background: linear-gradient(135deg, #fef3c7, #fde68a); }
    .wishlist-item-row:nth-child(5n+2) { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }
    .wishlist-item-row:nth-child(5n+3) { background: linear-gradient(135deg, #fce7f3, #fbcfe8); }
    .wishlist-item-row:nth-child(5n+4) { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
    .wishlist-item-row:nth-child(5n+5) { background: linear-gradient(135deg, #ede9fe, #ddd6fe); }

    .wishlist-item-row .wi-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: #ef4444; flex-shrink: 0;
    }

    @keyframes wishItemIn {
      from { opacity: 0; transform: translateX(-8px); }
      to { opacity: 1; transform: translateX(0); }
    }

    #wishlist-modal-close {
      margin-top: 16px;
      width: 100%;
      padding: 11px 0;
      border: none;
      border-radius: 10px;
      background: var(--accent-gradient, linear-gradient(135deg, #6366f1, #ec4899));
      color: white;
      font-weight: 600;
      font-size: 13px;
      cursor: pointer;
    }

    /* --- NEW: dashboard registered-course progress bar --- */
    .dash-progress-track {
      width: 100%;
      height: 8px;
      background: var(--gray-100);
      border-radius: 50px;
      overflow: hidden;
      margin-bottom: 14px;
    }
    .dash-progress-fill {
      height: 100%;
      border-radius: 50px;
      transition: width 0.3s ease;
    }
  </style>
</head>
<body>

  <?php if ($show_enrolled_banner): ?>
    <div id="enroll-success-toast">
      <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span>Enrolled successfully! Check "My Registered Curriculums" below.</span>
    </div>
  <?php endif; ?>

  <header>
    <div class="max-width-container nav-wrapper">
      <a href="index.php" class="logo-wrapper">
        <div class="logo-icon">
          <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>
        </div>
        <div class="logo-text">
          <h2>E-Learning Hub</h2>
          <p>Academic Portal</p>
        </div>
      </a>

      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="courses.php" class="active">Courses</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact Us</a></li>
      </ul>

      <div class="nav-actions">
        <?php if ($user_id): ?>
          <span style="font-size: 13px; font-weight: 600; color: var(--gray-600);">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
          <a href="logout.php" class="btn-secondary" style="color:#ef4444; padding:0 10px;">Logout</a>
        <?php else: ?>
          <a href="login.php" class="btn-secondary">Sign In</a>
          <a href="register.php" class="btn-primary">Register Now</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <div class="max-width-container" style="padding-top: 40px; padding-bottom: 80px;">
    
    <?php
      // Shared helper: resolve a safe display image for any course row
      function resolve_course_image($course) {
          $image_path = trim($course['image']);
          if (empty($image_path) || !string_contains_image_extension($image_path)) {
              return "https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=600";
          }
          return htmlspecialchars($image_path);
      }
    ?>

    <?php if ($user_id): ?>
      <div class="dashboard-hero" id="my-registered-curriculums">
        <span style="background: rgba(255,255,255,0.15); padding: 4px 12px; border-radius: 50px; font-size:11px; font-family:var(--font-mono); font-weight:bold; text-transform:uppercase;">Student Portal Active</span>
        <h1 style="margin-top: 12px;">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Your self-paced academic growth track is fully active. Click on any registered curriculum below to open the immersive Course Player with embedded lectures and live compiler sandboxes.</p>
      </div>

      <h2 style="font-size: 1.6rem; margin-bottom: 24px;">My Registered Curriculums</h2>

      <?php if (empty($enrolled_ids)): ?>
        <div style="background: white; border: 2px dashed var(--gray-300); text-align: center; padding: 40px; border-radius: var(--radius-lg); margin-bottom: 60px;">
          <h3 style="margin-bottom:8px;">No registered courses yet!</h3>
          <p style="font-size:14px; color: var(--gray-500); margin-bottom:20px;">Register for a program below to see its full details here on your dashboard.</p>
        </div>
      <?php else: ?>
        <div class="courses-grid" style="margin-bottom: 50px;">
          <?php foreach ($courses as $course):
            if (!in_all_enrolled($course['id'], $enrolled_ids)) continue;
            $display_image = resolve_course_image($course);

            // ---- NEW: progress % for this registered course ----
            $c_completed = isset($progress_by_course[$course['id']]) ? $progress_by_course[$course['id']] : 0;
            $c_pct = $COURSE_TOTAL_WEEKS > 0 ? round(($c_completed / $COURSE_TOTAL_WEEKS) * 100) : 0;
            $c_done = $c_completed >= $COURSE_TOTAL_WEEKS;
            ?>
            <div class="course-card">
              <div class="card-img-box">
                <img src="<?php echo $display_image; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600';">
                <span class="card-tag"><?php echo htmlspecialchars($course['category']); ?></span>
                <?php if ($course['id'] === $most_popular_course_id): ?>
                  <span class="popular-badge">🔥 Most Popular</span>
                <?php endif; ?>
              </div>

              <div class="card-body">
                <div class="card-stats">
                  <span class="rating">★ <?php echo number_format($course['rating'], 1); ?></span>
                  <span class="students"><?php echo number_format($course['students_enrolled']); ?> enrolled</span>
                </div>

                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                <span class="instructor-text">Instructed by <?php echo htmlspecialchars($course['instructor']); ?></span>
                <p><?php echo htmlspecialchars($course['description']); ?></p>

                <div class="card-footer">
                  <div class="meta-duration">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="height:14px;width:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><?php echo htmlspecialchars($course['duration']); ?></span>
                  </div>
                  <span class="meta-level <?php echo htmlspecialchars($course['level']); ?>"><?php echo htmlspecialchars($course['level']); ?></span>
                </div>

                <!-- NEW: registered-course progress bar + certificate button -->
                <div style="margin-top: 16px; border-top: 1px solid var(--gray-50); padding-top:16px;">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                    <span style="font-size:11px; font-weight:700; font-family:var(--font-mono); color:var(--gray-500); text-transform:uppercase;">Progress</span>
                    <span style="font-size:11px; font-weight:700; color: <?php echo $c_done ? '#16a34a' : 'var(--gray-600)'; ?>;"><?php echo $c_pct; ?>% complete</span>
                  </div>
                  <div class="dash-progress-track">
                    <div class="dash-progress-fill" style="width:<?php echo $c_pct; ?>%; background: <?php echo $c_done ? 'linear-gradient(135deg,#16a34a,#15803d)' : 'var(--accent-gradient)'; ?>;"></div>
                  </div>

                  <?php if ($c_done): ?>
                    <div style="display:flex; gap:8px;">
                      <a href="course_detail.php?id=<?php echo urlencode($course['id']); ?>" class="btn-secondary" style="font-size:12px; flex:1; text-align:center; padding:10px 0; border:1px solid var(--gray-300); border-radius:8px;">Review</a>
                      <a href="certificate.php?course_id=<?php echo urlencode($course['id']); ?>" class="btn-primary" style="font-size:12px; flex:1; justify-content:center; padding:10px 0; background: linear-gradient(135deg,#16a34a,#15803d);">🎓 Certificate</a>
                    </div>
                  <?php else: ?>
                    <a href="course_detail.php?id=<?php echo urlencode($course['id']); ?>" class="btn-primary" style="font-size:12px; width: 100%; justify-content:center; padding:10px 0;">Resume Player</a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div style="border-top: 1px solid var(--gray-100); padding-top: 40px; margin-bottom: 20px;">
        <h2 style="font-size: 1.4rem; margin-bottom: 6px;">Register for Another Course</h2>
        <p style="font-size: 13px; color: var(--gray-500); margin-bottom: 20px;">Courses you haven't chosen yet always stay listed here — register again to add any of them to your dashboard.</p>
      </div>
    <?php else: ?>
      <div class="catalog-guest-hero">
        <span class="catalog-guest-badge">✨ 15+ Curriculum Pathways Available</span>
        <h1>Browse our 15+ Advanced Programs</h1>
        <p>Explore curriculum paths led by accredited faculty. Register an account to save study lectures, track your enrolled courses, and launch live coding sandboxes — all in one student dashboard.</p>
        <div class="catalog-guest-cta">
          <a href="register.php" class="btn-primary">Create Free Account</a>
          <a href="#course-cards-catalog" class="btn-secondary" style="background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.3);">Browse Programs Below</a>
        </div>
      </div>
    <?php endif; ?>

    <!-- NEW: Catalog quick-stats strip -->
    <div class="catalog-stats-strip">
      <div class="catalog-stat-item">
        <h3><?php echo $total_courses_count; ?>+</h3>
        <p>Total Programs</p>
      </div>
      <div class="catalog-stat-item">
        <h3><?php echo $total_categories_count; ?></h3>
        <p>Categories</p>
      </div>
      <div class="catalog-stat-item">
        <h3><?php echo number_format($total_students_sum); ?></h3>
        <p>Students Enrolled</p>
      </div>
      <div class="catalog-stat-item">
        <h3><?php echo number_format($avg_rating_value, 1); ?>★</h3>
        <p>Average Rating</p>
      </div>
    </div>

    <div class="filters-wrapper" style="flex-direction: column; align-items: stretch;">
      <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap;">
        <div class="category-filters">
          <button class="cat-btn active" data-category="All">All Curriculums</button>
          <button class="cat-btn" data-category="Web Development">Web Development</button>
          <button class="cat-btn" data-category="Software Engineering">Software Engineering</button>
          <button class="cat-btn" data-category="Database">Database</button>
          <button class="cat-btn" data-category="Design">Design</button>
        </div>

        <div style="display:flex; align-items:center; gap:12px;">
          <!-- NEW: wishlist quick-access bar -->
          <button type="button" class="wishlist-bar" id="wishlist-bar-btn">
            <svg viewBox="0 0 24 24"><path d="M12 21s-6.7-4.35-9.3-8.1C.8 9.9 1.9 6.3 5.1 5.2 7.3 4.5 9.6 5.3 12 7.6c2.4-2.3 4.7-3.1 6.9-2.4 3.2 1.1 4.3 4.7 2.4 7.7C18.7 16.65 12 21 12 21z"/></svg>
            Saved <span id="wishlist-count-num">0</span>
          </button>

          <div class="search-box">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" id="course-search-input" placeholder="Search program titles...">
          </div>
        </div>
      </div>

      <!-- NEW: level filter row -->
      <div class="level-filters">
        <span class="level-filter-label">Level:</span>
        <button class="level-btn active" data-level="All">All Levels</button>
        <button class="level-btn" data-level="Beginner">Beginner</button>
        <button class="level-btn" data-level="Intermediate">Intermediate</button>
        <button class="level-btn" data-level="Advanced">Advanced</button>
      </div>
    </div>

    <!-- NEW: results count + sort toolbar -->
    <div class="results-toolbar">
      <span class="results-count" id="results-count-text">Showing <strong id="visible-count">0</strong> of <strong><?php echo $total_courses_count; ?></strong> programs</span>
      <div class="sort-select-wrapper">
        <label for="course-sort-select">Sort by</label>
        <select id="course-sort-select">
          <option value="default">Featured</option>
          <option value="rating-desc">Highest Rated</option>
          <option value="students-desc">Most Popular</option>
          <option value="title-asc">Title (A–Z)</option>
        </select>
      </div>
    </div>

    <div class="courses-grid" id="course-cards-catalog">
      <?php foreach ($courses as $course):
        // For a logged-in student, this grid only shows courses they have NOT registered for yet
        if ($user_id && in_all_enrolled($course['id'], $enrolled_ids)) continue;
        $display_image = resolve_course_image($course);

        // A course is "unlocked" for a logged-in student if they picked it
        // at registration time (free slot or already paid for there).
        // Anything else needs a one-time payment on this page to register.
        $is_interested = $user_id && in_array($course['id'], $interested_ids, true);
        $is_locked = $user_id && !$is_interested;
        $course_price = get_course_price($course['level']);
        ?>
        <div class="course-card<?php echo $is_locked ? ' locked-course' : ''; ?>" data-cat="<?php echo htmlspecialchars($course['category']); ?>" data-title="<?php echo htmlspecialchars(strtolower($course['title'])); ?>" data-rating="<?php echo htmlspecialchars($course['rating']); ?>" data-students="<?php echo htmlspecialchars($course['students_enrolled']); ?>" data-level="<?php echo htmlspecialchars($course['level']); ?>">
          <div class="card-img-box">
            <img src="<?php echo $display_image; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600';">
            <button type="button" class="wishlist-btn" data-course-id="<?php echo htmlspecialchars($course['id']); ?>" data-course-title="<?php echo htmlspecialchars($course['title']); ?>" title="Save for later">
              <svg viewBox="0 0 24 24"><path d="M12 21s-6.7-4.35-9.3-8.1C.8 9.9 1.9 6.3 5.1 5.2 7.3 4.5 9.6 5.3 12 7.6c2.4-2.3 4.7-3.1 6.9-2.4 3.2 1.1 4.3 4.7 2.4 7.7C18.7 16.65 12 21 12 21z"/></svg>
            </button>
            <span class="card-tag"><?php echo htmlspecialchars($course['category']); ?></span>
            <?php if ($course['id'] === $most_popular_course_id): ?>
              <span class="popular-badge">🔥 Most Popular</span>
            <?php endif; ?>
            <?php if ($is_locked): ?>
              <span class="locked-price-badge"><svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>₹<?php echo $course_price; ?></span>
            <?php endif; ?>
          </div>

          <div class="card-body">
            <div class="card-stats">
              <span class="rating">★ <?php echo number_format($course['rating'], 1); ?></span>
              <span class="students"><?php echo number_format($course['students_enrolled']); ?> enrolled</span>
            </div>

            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
            <span class="instructor-text">Instructed by <?php echo htmlspecialchars($course['instructor']); ?></span>
            <p><?php echo htmlspecialchars($course['description']); ?></p>

            <div class="card-footer">
              <div class="meta-duration">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="height:14px;width:14px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?php echo htmlspecialchars($course['duration']); ?></span>
              </div>

              <span class="meta-level <?php echo htmlspecialchars($course['level']); ?>"><?php echo htmlspecialchars($course['level']); ?></span>
            </div>

            <div style="margin-top: 16px; border-top: 1px solid var(--gray-50); padding-top:16px; display:flex; gap:10px;">
              <?php if ($user_id): ?>
                <?php if ($is_interested): ?>
                  <a href="courses.php?enroll=<?php echo urlencode($course['id']); ?>" class="btn-primary enroll-confirm-link" data-course-title="<?php echo htmlspecialchars($course['title']); ?>" style="font-size:12px; width: 100%; justify-content:center; padding:10px 0;">Register for Course</a>
                <?php else: ?>
                  <a href="courses.php?enroll=<?php echo urlencode($course['id']); ?>" class="btn-primary pay-unlock-link" data-course-id="<?php echo htmlspecialchars($course['id']); ?>" data-course-title="<?php echo htmlspecialchars($course['title']); ?>" data-course-price="<?php echo $course_price; ?>" style="font-size:12px; width: 100%; justify-content:center; padding:10px 0;">🔒 Pay ₹<?php echo $course_price; ?> & Register</a>
                <?php endif; ?>
              <?php else: ?>
                <a href="course_detail.php?id=<?php echo urlencode($course['id']); ?>" class="btn-secondary" style="font-size:12px; width: 100%; border: 1px solid var(--gray-300); text-align:center; padding:10px 0; border-radius:8px;">Preview Details</a>
                <a href="register.php" class="btn-primary" style="font-size:12px; width: 100%; justify-content:center; padding:10px 0;">Enroll Now</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- NEW: empty state shown via JS when filter/search has no matches -->
    <div id="no-results-state">
      <h3>No matching courses found</h3>
      <p>Try a different search term or reset the category filter.</p>
    </div>

  </div>

  <!-- NEW: Custom Enroll Confirmation Modal -->
  <div id="enroll-modal-overlay">
    <div class="enroll-modal-box">
      <div class="enroll-modal-icon">
        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4.5v15m7.5-7.5h-15"/></svg>
      </div>
      <h3>Confirm Enrollment</h3>
      <p>Enroll in <strong id="enroll-modal-course-name">this course</strong>? It will be added to your registered curriculums right away.</p>
      <div class="enroll-modal-actions">
        <button type="button" id="enroll-modal-cancel">Cancel</button>
        <button type="button" id="enroll-modal-confirm">Yes, Enroll</button>
      </div>
    </div>
  </div>

  <!-- NEW: Mock Payment Modal — unlocks a course that wasn't picked at registration.
       Same idle -> processing -> success pattern used on register.php. -->
  <div id="payment-modal-overlay">
    <div class="payment-modal-box">

      <div id="payment-state-idle">
        <div class="payment-modal-icon">
          <svg viewBox="0 0 24 24"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h3 id="payment-course-title">Unlock this course</h3>
        <p>This course wasn't part of your free selection at sign-up. Pay the one-time course fee to unlock and register for it.</p>

        <!-- NEW: student + course summary so they can confirm who's paying and for what -->
        <div class="payment-student-info" id="payment-student-info">
          <div class="psi-row"><span>Name</span><strong><?php echo htmlspecialchars($student_name); ?></strong></div>
          <div class="psi-row"><span>Email</span><strong><?php echo $student_email !== '' ? htmlspecialchars($student_email) : '—'; ?></strong></div>
          <div class="psi-row"><span>Paying For</span><strong id="payment-paying-for-course">—</strong></div>
        </div>

        <div class="payment-amount" id="payment-amount">₹0</div>
        <div class="payment-modal-actions">
          <button type="button" class="payment-pay-btn" id="payment-pay-btn">Pay Now</button>
          <button type="button" class="payment-cancel-btn" id="payment-cancel-btn">Cancel</button>
        </div>
      </div>

      <div id="payment-state-processing" style="display:none;">
        <div class="payment-spinner"></div>
        <h3>Processing payment…</h3>
        <p>Please wait while we confirm your transaction. Do not close this window.</p>
      </div>

      <div id="payment-state-success" style="display:none;">
        <div class="payment-success-icon">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <h3>Payment Successful</h3>
        <p id="payment-success-msg">Course unlocked.</p>

        <!-- NEW: mock receipt summary + download (demo only, generates a PDF client-side) -->
        <div class="payment-receipt-box" id="payment-receipt-box">
          <div class="receipt-row"><span>Receipt No</span><strong id="receipt-no">—</strong></div>
          <div class="receipt-row"><span>Date</span><strong id="receipt-date">—</strong></div>
          <div class="receipt-row"><span>Student</span><strong id="receipt-student">—</strong></div>
          <div class="receipt-row"><span>Email</span><strong id="receipt-email">—</strong></div>
          <div class="receipt-row"><span>Course</span><strong id="receipt-course">—</strong></div>
          <div class="receipt-row"><span>Amount Paid</span><strong id="receipt-amount">—</strong></div>
          <div class="receipt-row"><span>Status</span><strong style="color:#16a34a;">PAID</strong></div>
        </div>

        <!-- NEW: registration status line — confirms the course is already added -->
        <p id="payment-registration-status" style="font-size:12px; font-weight:600; color:#16a34a; margin:-8px 0 16px; display:none;">✓ Added to your registered curriculums</p>

        <button type="button" class="payment-receipt-download-btn" id="payment-receipt-download-btn">
          <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v13m0 0l-4-4m4 4l4-4M5 19h14"/></svg>
          Download Receipt (PDF)
        </button>
        <button type="button" class="payment-cancel-btn" id="payment-done-btn" style="margin-top:10px;">Done</button>
      </div>

    </div>
  </div>

  <!-- NEW: Colorful Wishlist Modal (replaces alert popup) -->
  <div id="wishlist-modal-overlay">
    <div class="wishlist-modal-box">
      <div class="wishlist-modal-header">
        <svg viewBox="0 0 24 24"><path d="M12 21s-6.7-4.35-9.3-8.1C.8 9.9 1.9 6.3 5.1 5.2 7.3 4.5 9.6 5.3 12 7.6c2.4-2.3 4.7-3.1 6.9-2.4 3.2 1.1 4.3 4.7 2.4 7.7C18.7 16.65 12 21 12 21z"/></svg>
        <h3>Your Saved Courses</h3>
      </div>
      <div id="wishlist-modal-list"></div>
      <button type="button" id="wishlist-modal-close">Close</button>
    </div>
  </div>

  <!-- NEW: Scroll-to-top floating button -->
  <button type="button" id="scroll-top-btn" title="Back to top">
    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
  </button>

  <footer style="margin-top: 80px;">
    <div class="max-width-container footer-top">
      <div class="footer-brand">
        <div class="logo-wrapper">
          <div class="logo-icon">
            <svg fill="currentColor" viewBox="0 0 24 24" style="height:20px;width:20px;"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>
          </div>
          <div class="logo-text">
            <h2 style="color:var(--white);">E-Learning Hub</h2>
          </div>
        </div>
        <p>Shaping tech minds worldwide with 15+ comprehensive programs. Access dynamic video materials, code compilers, and verified university syllabus architectures cleanly.</p>
      </div>

      <div class="footer-links">
        <h4>Navigations</h4>
        <ul>
          <li><a href="index.php">Main Homepage</a></li>
          <li><a href="courses.php">All 15+ Courses</a></li>
          <li><a href="about.php">Our Story</a></li>
          <li><a href="contact.php">Contact Support</a></li>
        </ul>
      </div>

      <div class="footer-links">
        <h4>XAMPP Info</h4>
        <p style="font-size: 13px; color: var(--gray-500); line-height: 1.6;">Your local PHP/MySQL e-learning application is now fully configured for Apache server deployments.</p>
      </div>
    </div>

    <div class="max-width-container footer-bottom">
      <span>© 2026 E-Learning Hub Academy. All rights reserved.</span>
      <span style="font-family: var(--font-mono); font-size:10px;">XAMPP INTEGRATED</span>
    </div>
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const searchInput = document.getElementById("course-search-input");
      const catButtons = document.querySelectorAll(".cat-btn");
      const levelButtons = document.querySelectorAll(".level-btn");
      const sortSelect = document.getElementById("course-sort-select");
      const courseGrid = document.getElementById("course-cards-catalog");
      // Scoped to the catalog grid only — the "My Registered Curriculums"
      // dashboard section above also uses .course-card but doesn't carry
      // data-title/data-cat/etc, which was crashing filterGrid() and
      // aborting every listener registered after it (including the
      // payment modal handlers further down this script).
      const courseCards = courseGrid ? Array.from(courseGrid.querySelectorAll(".course-card")) : [];
      const visibleCountEl = document.getElementById("visible-count");
      const noResultsState = document.getElementById("no-results-state");

      let currentCategory = "All";
      let currentLevel = "All";
      let currentQuery = "";

      const filterGrid = () => {
        let visibleCount = 0;

        courseCards.forEach(card => {
          const cat = card.getAttribute("data-cat");
          const title = card.getAttribute("data-title");
          const level = card.getAttribute("data-level");

          const matchesCat = (currentCategory === "All" || cat === currentCategory);
          const matchesLevel = (currentLevel === "All" || level === currentLevel);
          const matchesSearch = (title || "").includes(currentQuery);

          if (matchesCat && matchesLevel && matchesSearch) {
            card.style.display = "flex";
            visibleCount++;
          } else {
            card.style.display = "none";
          }
        });

        if (visibleCountEl) visibleCountEl.textContent = visibleCount;

        if (noResultsState) {
          noResultsState.style.display = visibleCount === 0 ? "block" : "none";
        }
        if (courseGrid) {
          courseGrid.style.display = visibleCount === 0 ? "none" : "grid";
        }
      };

      // NEW: sort handler — reorders the actual DOM nodes inside the grid
      const sortGrid = (mode) => {
        if (!courseGrid) return;
        const cardsArray = Array.from(courseGrid.querySelectorAll(".course-card"));

        let sorted = cardsArray;
        if (mode === "rating-desc") {
          sorted = cardsArray.sort((a, b) => parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating));
        } else if (mode === "students-desc") {
          sorted = cardsArray.sort((a, b) => parseInt(b.dataset.students, 10) - parseInt(a.dataset.students, 10));
        } else if (mode === "title-asc") {
          sorted = cardsArray.sort((a, b) => a.dataset.title.localeCompare(b.dataset.title));
        }
        // "default" (Featured) keeps original DOM order — no resort needed

        sorted.forEach(card => courseGrid.appendChild(card));
      };

      if (searchInput) {
        searchInput.addEventListener("input", (e) => {
          currentQuery = e.target.value.toLowerCase().trim();
          filterGrid();
        });
      }

      catButtons.forEach(btn => {
        btn.addEventListener("click", () => {
          catButtons.forEach(b => b.classList.remove("active"));
          btn.classList.add("active");
          currentCategory = btn.getAttribute("data-category");
          filterGrid();
        });
      });

      // NEW: level filter buttons wiring
      levelButtons.forEach(btn => {
        btn.addEventListener("click", () => {
          levelButtons.forEach(b => b.classList.remove("active"));
          btn.classList.add("active");
          currentLevel = btn.getAttribute("data-level");
          filterGrid();
        });
      });

      if (sortSelect) {
        sortSelect.addEventListener("change", (e) => sortGrid(e.target.value));
      }

      // Initial paint: set correct visible count on page load
      filterGrid();

      // ---- NEW: Wishlist / Save-for-later (persisted in localStorage) ----
      const WISHLIST_KEY = "elhub_wishlist_course_ids";
      const wishlistBtns = document.querySelectorAll(".wishlist-btn");
      const wishlistCountEl = document.getElementById("wishlist-count-num");
      const wishlistBarBtn = document.getElementById("wishlist-bar-btn");

      const getWishlist = () => {
        try {
          return JSON.parse(localStorage.getItem(WISHLIST_KEY)) || [];
        } catch (e) {
          return [];
        }
      };

      const saveWishlist = (ids) => {
        localStorage.setItem(WISHLIST_KEY, JSON.stringify(ids));
      };

      const refreshWishlistUI = () => {
        const ids = getWishlist();
        if (wishlistCountEl) wishlistCountEl.textContent = ids.length;
        wishlistBtns.forEach(btn => {
          const id = btn.getAttribute("data-course-id");
          btn.classList.toggle("saved", ids.includes(id));
        });
      };

      wishlistBtns.forEach(btn => {
        btn.addEventListener("click", (e) => {
          e.preventDefault();
          e.stopPropagation();
          const id = btn.getAttribute("data-course-id");
          let ids = getWishlist();
          if (ids.includes(id)) {
            ids = ids.filter(x => x !== id);
          } else {
            ids.push(id);
          }
          saveWishlist(ids);
          refreshWishlistUI();
        });
      });

      // ---- NEW: Colorful Wishlist Modal (replaces alert()) ----
      const wishlistModalOverlay = document.getElementById("wishlist-modal-overlay");
      const wishlistModalList = document.getElementById("wishlist-modal-list");
      const wishlistModalClose = document.getElementById("wishlist-modal-close");

      if (wishlistBarBtn) {
        wishlistBarBtn.addEventListener("click", () => {
          const ids = getWishlist();
          if (!wishlistModalList) return;

          if (ids.length === 0) {
            wishlistModalList.innerHTML = `<div class="wishlist-modal-empty">No saved courses yet. Tap the ❤️ icon on any course card to save it here.</div>`;
          } else {
            const titles = Array.from(wishlistBtns)
              .filter(b => ids.includes(b.getAttribute("data-course-id")))
              .map(b => b.getAttribute("data-course-title"));

            wishlistModalList.innerHTML = titles
              .map(t => `<div class="wishlist-item-row"><span class="wi-dot"></span>${t}</div>`)
              .join("");
          }

          if (wishlistModalOverlay) wishlistModalOverlay.classList.add("visible");
        });
      }

      if (wishlistModalClose) {
        wishlistModalClose.addEventListener("click", () => {
          if (wishlistModalOverlay) wishlistModalOverlay.classList.remove("visible");
        });
      }
      if (wishlistModalOverlay) {
        wishlistModalOverlay.addEventListener("click", (e) => {
          if (e.target === wishlistModalOverlay) wishlistModalOverlay.classList.remove("visible");
        });
      }

      refreshWishlistUI();

      // ---- NEW: Scroll-to-top floating button ----
      const scrollTopBtn = document.getElementById("scroll-top-btn");
      if (scrollTopBtn) {
        window.addEventListener("scroll", () => {
          scrollTopBtn.classList.toggle("visible", window.scrollY > 400);
        });
        scrollTopBtn.addEventListener("click", () => {
          window.scrollTo({ top: 0, behavior: "smooth" });
        });
      }

      // ---- NEW: Custom modal confirmation before enrolling in a course ----
      // Uses event delegation (document-level) instead of a one-time
      // querySelectorAll, because "pay-unlock-link" buttons get converted
      // into "enroll-confirm-link" buttons in-place after a successful
      // mock payment — a static listener wouldn't catch those.
      const enrollModalOverlay = document.getElementById("enroll-modal-overlay");
      const enrollModalCourseName = document.getElementById("enroll-modal-course-name");
      const enrollModalCancelBtn = document.getElementById("enroll-modal-cancel");
      const enrollModalConfirmBtn = document.getElementById("enroll-modal-confirm");
      let pendingEnrollUrl = null;

      const closeEnrollModal = () => {
        enrollModalOverlay.classList.remove("visible");
        pendingEnrollUrl = null;
      };

      if (enrollModalCancelBtn) {
        enrollModalCancelBtn.addEventListener("click", closeEnrollModal);
      }
      if (enrollModalConfirmBtn) {
        enrollModalConfirmBtn.addEventListener("click", () => {
          if (pendingEnrollUrl) {
            window.location.href = pendingEnrollUrl;
          }
        });
      }
      if (enrollModalOverlay) {
        // Clicking the dark backdrop (outside the box) also cancels
        enrollModalOverlay.addEventListener("click", (e) => {
          if (e.target === enrollModalOverlay) closeEnrollModal();
        });
      }

      // ---- NEW: Mock payment gate for courses NOT picked at registration ----
      // Same idle -> processing -> success pattern as register.php. No real
      // gateway — this is a UI demo only. As soon as payment "succeeds", we
      // silently call the enroll endpoint in the background so the course is
      // registered immediately — the student does NOT need to click anything
      // else afterward. They can still view/download the receipt before closing.
      const paymentModalOverlay = document.getElementById("payment-modal-overlay");
      const paymentStateIdle = document.getElementById("payment-state-idle");
      const paymentStateProcessing = document.getElementById("payment-state-processing");
      const paymentStateSuccess = document.getElementById("payment-state-success");
      const paymentCourseTitle = document.getElementById("payment-course-title");
      const paymentAmountEl = document.getElementById("payment-amount");
      const paymentSuccessMsg = document.getElementById("payment-success-msg");
      const paymentPayBtn = document.getElementById("payment-pay-btn");
      const paymentCancelBtn = document.getElementById("payment-cancel-btn");
      const paymentPayingForEl = document.getElementById("payment-paying-for-course");
      const paymentRegistrationStatus = document.getElementById("payment-registration-status");
      const receiptNoEl = document.getElementById("receipt-no");
      const receiptDateEl = document.getElementById("receipt-date");
      const receiptStudentEl = document.getElementById("receipt-student");
      const receiptEmailEl = document.getElementById("receipt-email");
      const receiptCourseEl = document.getElementById("receipt-course");
      const receiptAmountEl = document.getElementById("receipt-amount");
      const receiptDownloadBtn = document.getElementById("payment-receipt-download-btn");
      const STUDENT_NAME = <?php echo json_encode($student_name); ?>;
      const STUDENT_EMAIL = <?php echo json_encode($student_email !== '' ? $student_email : '—'); ?>;
      let lastReceipt = null;
      let pendingPayLink = null;
      let paymentModalHadSuccessfulEnroll = false;

      const showPaymentState = (state) => {
        paymentStateIdle.style.display = state === "idle" ? "block" : "none";
        paymentStateProcessing.style.display = state === "processing" ? "block" : "none";
        paymentStateSuccess.style.display = state === "success" ? "block" : "none";
      };

      const openPaymentModal = (link) => {
        pendingPayLink = link;
        paymentModalHadSuccessfulEnroll = false;
        const title = link.getAttribute("data-course-title") || "this course";
        const price = link.getAttribute("data-course-price") || "0";
        paymentCourseTitle.textContent = `Unlock "${title}"`;
        paymentAmountEl.textContent = `₹${price}`;
        if (paymentPayingForEl) paymentPayingForEl.textContent = title;
        if (paymentRegistrationStatus) paymentRegistrationStatus.style.display = "none";
        showPaymentState("idle");
        paymentModalOverlay.classList.add("active");
      };

      // Reloads the page (scrolled to the dashboard section) so "My Registered
      // Curriculums" reflects the course that was just paid for and registered.
      const goToMyCurriculumsAfterEnroll = () => {
        window.location.href = "courses.php?enrolled_ok=1#my-registered-curriculums";
      };

      const closePaymentModal = () => {
        paymentModalOverlay.classList.remove("active");
        const hadSuccess = paymentModalHadSuccessfulEnroll;
        pendingPayLink = null;
        setTimeout(() => showPaymentState("idle"), 250);
        if (hadSuccess) {
          goToMyCurriculumsAfterEnroll();
        }
      };

      if (paymentPayBtn) {
        paymentPayBtn.addEventListener("click", () => {
          if (!pendingPayLink) return;
          const link = pendingPayLink;
          const title = link.getAttribute("data-course-title") || "This course";
          const price = link.getAttribute("data-course-price") || "0";
          const courseId = link.getAttribute("data-course-id") || "";

          showPaymentState("processing");

          setTimeout(() => {
            paymentSuccessMsg.textContent = `₹${price} paid — "${title}" is now unlocked.`;

            // NEW: build the mock receipt shown + downloadable after success
            const receiptNo = "RCPT-" + Date.now().toString().slice(-8);
            const receiptDate = new Date().toLocaleString();
            if (receiptNoEl) receiptNoEl.textContent = receiptNo;
            if (receiptDateEl) receiptDateEl.textContent = receiptDate;
            if (receiptStudentEl) receiptStudentEl.textContent = STUDENT_NAME;
            if (receiptEmailEl) receiptEmailEl.textContent = STUDENT_EMAIL;
            if (receiptCourseEl) receiptCourseEl.textContent = title;
            if (receiptAmountEl) receiptAmountEl.textContent = `₹${price}`;
            lastReceipt = { receiptNo, receiptDate, title, price };

            showPaymentState("success");

            // Auto-register right now — no extra click needed. Uses the
            // ajax=1 flag so the server responds with JSON instead of a
            // redirect, and the current page never navigates away.
            fetch(`courses.php?enroll=${encodeURIComponent(courseId)}&ajax=1`, { credentials: "same-origin" })
              .then(res => res.json())
              .then(data => {
                paymentModalHadSuccessfulEnroll = !!(data && data.success);
                if (paymentRegistrationStatus) {
                  paymentRegistrationStatus.style.display = paymentModalHadSuccessfulEnroll ? "block" : "none";
                }
              })
              .catch(() => {
                // If the background call fails, fall back to a manual link
                // the student can click — nothing is silently lost.
                if (paymentRegistrationStatus) {
                  paymentRegistrationStatus.style.display = "none";
                }
                paymentSuccessMsg.innerHTML = `₹${price} paid, but we couldn't confirm registration automatically. <a href="courses.php?enroll=${encodeURIComponent(courseId)}" style="color:#4f46e5;font-weight:600;">Tap here to finish registering</a>.`;
              });

            const card = link.closest(".course-card");
            if (card) {
              card.classList.remove("locked-course");
              const badge = card.querySelector(".locked-price-badge");
              if (badge) badge.remove();
            }
          }, 1400);
        });
      }

      const paymentDoneBtn = document.getElementById("payment-done-btn");
      if (paymentDoneBtn) {
        paymentDoneBtn.addEventListener("click", closePaymentModal);
      }

      // ---- NEW: Receipt download as a real PDF (via jsPDF, loaded in <head>) ----
      if (receiptDownloadBtn) {
        receiptDownloadBtn.addEventListener("click", () => {
          if (!lastReceipt) return;
          const r = lastReceipt;

          if (!window.jspdf) {
            alert("PDF library failed to load. Check your internet connection and try again.");
            return;
          }

          const { jsPDF } = window.jspdf;
          const doc = new jsPDF({ unit: "pt", format: "a4" });

          // Header banner
          doc.setFillColor(79, 70, 229);
          doc.rect(0, 0, 595, 90, "F");
          doc.setTextColor(255, 255, 255);
          doc.setFontSize(20);
          doc.text("E-Learning Hub", 40, 45);
          doc.setFontSize(11);
          doc.text("Payment Receipt", 40, 65);

          // Body rows
          doc.setTextColor(30, 41, 59);
          doc.setFontSize(12);
          let y = 130;
          const rows = [
            ["Receipt No", r.receiptNo],
            ["Date", r.receiptDate],
            ["Student", STUDENT_NAME],
            ["Email", STUDENT_EMAIL],
            ["Course", r.title],
            ["Amount Paid", `Rs. ${r.price}`],
            ["Status", "PAID"]
          ];

          rows.forEach(([label, value]) => {
            doc.setFont(undefined, "normal");
            doc.setTextColor(100, 116, 139);
            doc.text(label, 40, y);
            doc.setFont(undefined, "bold");
            doc.setTextColor(30, 41, 59);
            doc.text(String(value), 250, y);
            doc.setDrawColor(226, 232, 240);
            doc.line(40, y + 8, 555, y + 8);
            y += 30;
          });

          doc.setFontSize(9);
          doc.setTextColor(148, 163, 184);
          doc.text("This is a demo receipt for testing purposes only.", 40, y + 20);

          doc.save(`receipt-${r.receiptNo}.pdf`);
        });
      }

      if (paymentCancelBtn) {
        paymentCancelBtn.addEventListener("click", closePaymentModal);
      }
      if (paymentModalOverlay) {
        paymentModalOverlay.addEventListener("click", (e) => {
          if (e.target === paymentModalOverlay) closePaymentModal();
        });
      }

      // Delegated click handling covers both the original server-rendered
      // links and any buttons converted client-side after payment succeeds.
      document.addEventListener("click", (e) => {
        const payLink = e.target.closest(".pay-unlock-link");
        if (payLink) {
          e.preventDefault();
          openPaymentModal(payLink);
          return;
        }

        const enrollLink = e.target.closest(".enroll-confirm-link");
        if (enrollLink) {
          // Always stop the link from navigating on its own —
          // navigation only happens if the student explicitly confirms in the modal.
          e.preventDefault();
          pendingEnrollUrl = enrollLink.getAttribute("href");
          enrollModalCourseName.textContent = enrollLink.getAttribute("data-course-title") || "this course";
          enrollModalOverlay.classList.add("visible");
        }
      });

      // ---- NEW: Auto-dismiss the enroll success toast (in case CSS animation is skipped) ----
      const enrollToast = document.getElementById("enroll-success-toast");
      if (enrollToast) {
        setTimeout(() => enrollToast.remove(), 3600);
      }
    });
  </script>

</body>
</html>
<?php
// PHP helper function to check registrations arrays
function in_all_enrolled($course_id, $array) {
    return is_array($array) && in_array($course_id, $array);
}

// Custom safety check to see if string actually contains a valid local or remote image asset file extension
function string_contains_image_extension($string) {
    $extensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', 'http://', 'https://'];
    foreach ($extensions as $ext) {
        if (stripos($string, $ext) !== false) {
            return true;
        }
    }
    return false;
}
?>