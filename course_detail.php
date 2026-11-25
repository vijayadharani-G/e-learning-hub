<?php
require_once 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!isset($_GET['id'])) {
    header("Location: courses.php");
    exit;
}

$course_id = trim($_GET['id']);

// Fetch course details securely
$stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->bind_param("s", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: courses.php");
    exit;
}

$course = $result->fetch_assoc();
$stmt->close();

// Check if logged in user is actually enrolled
$is_enrolled = false;
if ($user_id) {
    $stmt_chk = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND course_id = ?");
    $stmt_chk->bind_param("is", $user_id, $course_id);
    $stmt_chk->execute();
    $res_chk = $stmt_chk->get_result();
    if ($res_chk->num_rows > 0) {
        $is_enrolled = true;
    }
    $stmt_chk->close();
}

// Format objectives list (stored as semi-colon separated lists)
$objectives = array_filter(array_map('trim', explode(';', $course['objectives'])));

// Weekly topics list simulation for the sidebar
$syllabus_items = [
  ['week' => 'Week 1', 'topic' => 'Environment Setup & Basics', 'details' => 'Initializing frameworks, understanding base variables and scopes.'],
  ['week' => 'Week 2', 'topic' => 'Deep Dive Modules', 'details' => 'In-depth review of conditional algorithms, arrays, and standard functions.'],
  ['week' => 'Week 3', 'topic' => 'Integration Frameworks', 'details' => 'Handling standard file layouts, database tables, and input routing.'],
  ['week' => 'Week 4', 'topic' => 'Accreditation Projects', 'details' => 'Deploying the final compiled project securely and testing parameters.']
];
$total_weeks = count($syllabus_items);

// ---- Progress tracking: which weeks has this user completed for this course? ----
$completed_weeks = [];
if ($user_id && $is_enrolled) {
    $stmt_prog = $conn->prepare("SELECT week_index FROM lecture_progress WHERE user_id = ? AND course_id = ? AND completed = 1");
    $stmt_prog->bind_param("is", $user_id, $course_id);
    $stmt_prog->execute();
    $res_prog = $stmt_prog->get_result();
    while ($row = $res_prog->fetch_assoc()) {
        $completed_weeks[] = (int) $row['week_index'];
    }
    $stmt_prog->close();
}
$completed_count = count($completed_weeks);
$all_complete = $user_id && $is_enrolled && $completed_count >= $total_weeks;

// ---- Resume: figure out which week the player should open on load ----
// First not-yet-completed week, in order; if every week is complete, land on the last week.
$resume_index = 0;
if ($user_id && $is_enrolled) {
    $resume_index = $total_weeks - 1; // fallback: last week
    for ($i = 0; $i < $total_weeks; $i++) {
        if (!in_array($i, $completed_weeks, true)) {
            $resume_index = $i;
            break;
        }
    }
}
$resume_item = $syllabus_items[$resume_index];
$resume_already_done = in_array($resume_index, $completed_weeks, true);

// ---- Notes: fetch any saved notes per week for this user/course ----
$notes_map = [];
if ($user_id && $is_enrolled) {
    $stmt_notes = $conn->prepare("SELECT week_index, note_text FROM lecture_notes WHERE user_id = ? AND course_id = ?");
    $stmt_notes->bind_param("is", $user_id, $course_id);
    $stmt_notes->execute();
    $res_notes = $stmt_notes->get_result();
    while ($row = $res_notes->fetch_assoc()) {
        $notes_map[(int) $row['week_index']] = $row['note_text'];
    }
    $stmt_notes->close();
}

// ---- Reviews: this student's own review (if any) + everyone else's reviews ----
$my_review = null;
if ($user_id && $is_enrolled) {
    $stmt_myrev = $conn->prepare("SELECT rating, comment FROM course_reviews WHERE user_id = ? AND course_id = ?");
    $stmt_myrev->bind_param("is", $user_id, $course_id);
    $stmt_myrev->execute();
    $res_myrev = $stmt_myrev->get_result();
    if ($row_myrev = $res_myrev->fetch_assoc()) {
        $my_review = $row_myrev;
    }
    $stmt_myrev->close();
}

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($course['title']); ?> | Student Portal</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* --- Progress tracking, notes & certificate additions --- */
    .progress-summary {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 14px;
      gap: 10px;
    }
    .progress-summary span {
      font-size: 11px;
      font-weight: 700;
      font-family: var(--font-mono);
      color: var(--gray-500);
      text-transform: uppercase;
    }
    .progress-track {
      width: 100%;
      height: 8px;
      background: var(--gray-100);
      border-radius: 50px;
      overflow: hidden;
      margin-bottom: 20px;
    }
    .progress-fill {
      height: 100%;
      background: var(--accent-gradient);
      border-radius: 50px;
      transition: width 0.3s ease;
    }
    .sidebar-btn-check {
      flex-shrink: 0;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      border: 2px solid var(--gray-300);
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      margin-left: 8px;
      transition: var(--transition-fast);
    }
    .sidebar-btn-check svg {
      width: 12px;
      height: 12px;
      stroke: white;
      fill: none;
      stroke-width: 3;
      opacity: 0;
    }
    .sidebar-btn-check.done {
      background: #16a34a;
      border-color: #16a34a;
    }
    .sidebar-btn-check.done svg { opacity: 1; }
    .sidebar-btn { display: flex; align-items: center; }

    .certificate-banner {
      background: linear-gradient(135deg, #16a34a, #15803d);
      color: white;
      border-radius: var(--radius-lg);
      padding: 20px 26px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 24px;
      flex-wrap: wrap;
    }
    .certificate-banner h3 { color: white; margin-bottom: 4px; font-size: 15px; }
    .certificate-banner p { color: #dcfce7; font-size: 12px; margin: 0; }

    .notes-save-status {
      font-size: 12px;
      color: #16a34a;
      font-weight: 600;
      margin-left: 12px;
      opacity: 0;
      transition: opacity 0.2s ease;
    }
    .notes-save-status.visible { opacity: 1; }
    #lecture-notes-textarea {
      width: 100%;
      min-height: 220px;
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 14px;
      font-size: 13px;
      font-family: inherit;
      resize: vertical;
      color: var(--gray-700);
    }
    #lecture-notes-textarea:focus { border-color: var(--primary); outline: none; }
    .notes-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }

    /* --- NEW: Reviews tab --- */
    .reviews-summary-bar {
      display: flex;
      align-items: center;
      gap: 14px;
      background: var(--gray-50);
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 16px 18px;
      margin-bottom: 20px;
    }
    .reviews-summary-bar .rs-score { font-size: 28px; font-weight: 700; color: var(--gray-800, #1e293b); }
    .reviews-summary-bar .rs-stars { color: #f59e0b; font-size: 14px; letter-spacing: 1px; }
    .reviews-summary-bar .rs-count { font-size: 12px; color: var(--gray-500); }

    .my-review-box {
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 18px;
      margin-bottom: 26px;
      background: white;
    }
    .my-review-box h4 { font-size: 14px; margin-bottom: 12px; }
    .star-rating-input { display: flex; gap: 6px; margin-bottom: 14px; }
    .star-rating-input button {
      background: none;
      border: none;
      cursor: pointer;
      padding: 0;
      font-size: 26px;
      line-height: 1;
      color: var(--gray-300, #cbd5e1);
      transition: color 0.15s ease, transform 0.1s ease;
    }
    .star-rating-input button:hover { transform: scale(1.15); }
    .star-rating-input button.active,
    .star-rating-input button.hovered { color: #f59e0b; }
    #review-comment-textarea {
      width: 100%;
      min-height: 90px;
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 12px;
      font-size: 13px;
      font-family: inherit;
      resize: vertical;
      color: var(--gray-700);
      margin-bottom: 12px;
    }
    #review-comment-textarea:focus { border-color: var(--primary); outline: none; }
    #review-save-status {
      font-size: 12px;
      color: #16a34a;
      font-weight: 600;
      margin-left: 12px;
      opacity: 0;
      transition: opacity 0.2s ease;
    }
    #review-save-status.visible { opacity: 1; }

    .review-item {
      border-bottom: 1px solid var(--gray-50);
      padding: 16px 0;
    }
    .review-item:last-child { border-bottom: none; }
    .review-item-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 6px;
    }
    .review-item-user { font-weight: 700; font-size: 13px; color: var(--gray-800, #1e293b); }
    .review-item-stars { color: #f59e0b; font-size: 12px; letter-spacing: 1px; }
    .review-item-date { font-size: 11px; color: var(--gray-500); }
    .review-item-comment { font-size: 13px; color: var(--gray-600); line-height: 1.6; }
    .no-reviews-msg { font-size: 13px; color: var(--gray-500); text-align: center; padding: 30px 0; }

    /* --- NEW: video-container now hosts a JS-controlled player div instead of a plain iframe --- */
    #lecture-player { width: 100%; height: 100%; }
    #lecture-player iframe { width: 100%; height: 100%; border: 0; }

    /* --- NEW: "up next" auto-advance toast shown briefly after a lecture ends --- */
    #autoplay-next-toast {
      position: fixed;
      bottom: 24px;
      right: 24px;
      background: linear-gradient(135deg, #16a34a, #15803d);
      color: white;
      padding: 14px 20px;
      border-radius: var(--radius-md);
      box-shadow: var(--shadow-lg);
      font-size: 13px;
      font-weight: 600;
      z-index: 999;
      display: none;
      align-items: center;
      gap: 10px;
    }
    #autoplay-next-toast.visible { display: flex; }
    #autoplay-next-toast svg { width: 18px; height: 18px; flex-shrink: 0; }

    /* --- NEW: live video watch-percentage badge next to the lecture label ---
         Grey/neutral while watching, flips to solid green the instant the
         watched percentage crosses the completion threshold. --- */
    .lecture-header-top-row {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }
    .watch-percent-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 700;
      font-family: var(--font-mono);
      padding: 4px 10px;
      border-radius: 50px;
      background: var(--gray-100);
      color: var(--gray-600);
      transition: background 0.3s ease, color 0.3s ease;
      white-space: nowrap;
    }
    .watch-percent-badge.complete {
      background: #16a34a;
      color: white;
    }
  </style>
</head>
<body>

  <!-- Header Navigation -->
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
          <span style="font-size:13px; font-weight:600;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
          <a href="courses.php" class="btn-primary" style="font-size:12px; padding: 10px 20px;">My Portal</a>
        <?php else: ?>
          <a href="login.php" class="btn-secondary">Sign In</a>
          <a href="register.php" class="btn-primary">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Player Main Layout -->
  <div class="max-width-container">

    <?php if ($all_complete): ?>
      <div class="certificate-banner">
        <div>
          <h3>🎉 You've completed all <?php echo $total_weeks; ?> weeks of this course!</h3>
          <p>Your certificate of completion is ready to download.</p>
        </div>
        <a href="certificate.php?course_id=<?php echo urlencode($course_id); ?>" class="btn-primary" style="background: white; color: #15803d;">View Certificate</a>
      </div>
    <?php endif; ?>

    <div class="player-grid">
      
      <!-- Left side: Lecture player and tabs -->
      <div class="player-left">
        
        <!-- Video box / block -->
        <?php if ($user_id && $is_enrolled): ?>
          <!-- Logged in and registered: access complete dynamic player.
               NOTE: this used to be a plain <iframe>. It's now a JS-controlled
               container so we can detect when a lecture ends (YouTube IFrame
               Player API) and auto-tick + auto-advance to the next week. -->
          <div class="video-container">
            <div id="lecture-player" data-video-src="<?php echo htmlspecialchars($course['video_url']); ?>"></div>
          </div>
        <?php else: ?>
          <!-- Guest state: prompt to enroll -->
          <div class="video-container" style="background: linear-gradient(135deg, #1e293b, #0f172a); display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white; padding: 40px;">
            <div style="background: rgba(99, 102, 241, 0.2); border: 1px dashed #6366f1; padding: 20px; border-radius: 50%; margin-bottom: 20px;">
              <!-- Lock icon -->
              <svg fill="currentColor" viewBox="0 0 24 24" style="height:32px; width:32px; color: #818cf8;"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
            </div>
            <h3 style="color: white; margin-bottom: 10px;">Lecture Material Locked</h3>
            <p style="font-size: 13px; color: #94a3b8; max-width: 380px; line-height: 1.6; margin-bottom: 24px;">This premium video lecture is locked. Register or enroll in this curriculum to save progress and unlock live playbacks.</p>
            
            <?php if ($user_id): ?>
              <a href="courses.php?enroll=<?php echo urlencode($course['id']); ?>" class="btn-primary" style="box-shadow: 0 4px 14px 0 rgba(99,102,241,0.4);">Enroll in Course Instantly</a>
            <?php else: ?>
              <a href="register.php" class="btn-primary" style="box-shadow: 0 4px 14px 0 rgba(99,102,241,0.4);">Register & Enroll Now</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <!-- Lecture Meta info: pre-filled with the RESUME week (first incomplete, or last week if all done) -->
        <div class="lecture-header">
          <div class="lecture-header-top-row">
            <span id="active-lecture-week"><?php echo strtoupper('ACTIVE LECTURE • ' . $resume_item['week']); ?></span>
            <?php if ($user_id && $is_enrolled): ?>
              <!-- NEW: live watch-percentage badge. Starts at 100%/green if this
                   lecture was already marked complete on a previous visit,
                   otherwise starts at 0% and fills in as the video plays. -->
              <span id="video-watch-percent" class="watch-percent-badge <?php echo $resume_already_done ? 'complete' : ''; ?>">
                <?php echo $resume_already_done ? '✓ Completed' : '0% watched'; ?>
              </span>
            <?php endif; ?>
          </div>
          <h1 id="active-lecture-title"><?php echo htmlspecialchars($course['title'] . ': ' . $resume_item['topic']); ?></h1>
          <p style="font-size:13px; color:var(--gray-500); margin-top: 4px;">Program: <?php echo htmlspecialchars($course['title']); ?> • Instructor: <?php echo htmlspecialchars($course['instructor']); ?></p>
        </div>

        <!-- Tab controls -->
        <div class="tabs-header">
          <button class="tab-btn active" data-tab="overview">Overview & Objectives</button>
          <button class="tab-btn" data-tab="syllabus">Curriculum syllabus</button>
          <button class="tab-btn" data-tab="sandbox">Interactive Code Sandbox</button>
          <?php if ($user_id && $is_enrolled): ?>
            <button class="tab-btn" data-tab="notes">My Notes</button>
          <?php endif; ?>
          <button class="tab-btn" data-tab="reviews">Reviews <?php echo $review_count > 0 ? '(' . $review_count . ')' : ''; ?></button>
        </div>

        <!-- Tab contents -->
        <div class="tabs-body">
          
          <!-- Tab 1: Overview -->
          <div class="tab-pane active" id="tab-overview">
            <div class="overview-box">
              <div>
                <h3 style="margin-bottom:8px;">About this Program</h3>
                <p><?php echo htmlspecialchars($course['overview']); ?></p>
              </div>

              <div>
                <h3 style="margin-bottom:12px;">What you will master</h3>
                <div class="objectives-list">
                  <?php foreach ($objectives as $obj): ?>
                    <div class="obj-item">
                      <svg fill="currentColor" viewBox="0 0 24 24" style="height:16px;width:16px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                      <span><?php echo htmlspecialchars($obj); ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>

          <!-- Tab 2: Syllabus -->
          <div class="tab-pane" id="tab-syllabus">
            <h3 style="margin-bottom: 16px;">Weekly Program Outlines</h3>
            <div style="display:flex; flex-direction:column; gap:16px;">
              <?php foreach ($syllabus_items as $item): ?>
                <div style="padding: 16px; border: 1px solid var(--gray-100); border-radius: var(--radius-md); background-color: var(--gray-50);">
                  <span style="font-size:10px; font-family:var(--font-mono); color:var(--primary); font-weight:bold;"><?php echo $item['week']; ?></span>
                  <h4 style="margin-top:2px; font-size:14px;"><?php echo $item['topic']; ?></h4>
                  <p style="font-size:12px; color:var(--gray-500); margin-top:4px;"><?php echo $item['details']; ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Tab 3: Interactive Sandbox -->
          <div class="tab-pane" id="tab-sandbox">
            <div class="sandbox-grid">
              
              <div class="sandbox-left">
                <div class="sandbox-bar">
                  <span>Live Sandbox Compiler</span>
                  <div style="display:flex; gap:8px;">
                    <button id="reset-sandbox-btn" style="background:#cbd5e1; color:#1e293b;">Reset</button>
                    <button id="run-sandbox-btn">Run Code</button>
                  </div>
                </div>
                <div class="code-editor-area">
                  <textarea id="sandbox-code-editor" rows="12" spellcheck="false"><?php echo htmlspecialchars($course['code_example'] ? $course['code_example'] : "<!-- Edit this HTML/CSS to practice! -->\n<h1>Welcome to learning!</h1>\n<p style='color:indigo;'>Try adding styles inside <style> tags!</p>"); ?></textarea>
                </div>
              </div>

              <div class="sandbox-right">
                <div class="sandbox-bar" style="background-color: var(--gray-100);">
                  <span>Live Sandbox Output</span>
                </div>
                <div id="sandbox-browser-preview" class="browser-preview">
                  <div style="text-align: center; color: var(--gray-500); padding-top: 50px;">
                    Click 'Run Code' to view output here
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Tab 4: My Notes (only for enrolled students) -->
          <?php if ($user_id && $is_enrolled): ?>
          <div class="tab-pane" id="tab-notes">
            <div class="notes-toolbar">
              <h3 id="notes-active-label">Notes — <?php echo htmlspecialchars($resume_item['week']); ?></h3>
              <div>
                <button id="save-notes-btn" class="btn-primary" style="font-size:12px; padding:8px 18px;">Save Note</button>
                <span class="notes-save-status" id="notes-save-status">Saved!</span>
              </div>
            </div>
            <textarea id="lecture-notes-textarea" placeholder="Write your notes for this lecture here..."></textarea>
          </div>
          <?php endif; ?>

          <!-- Tab 5: Reviews (NEW) -->
          <div class="tab-pane" id="tab-reviews">
            <div class="reviews-summary-bar">
              <div class="rs-score"><?php echo number_format($review_avg, 1); ?></div>
              <div>
                <div class="rs-stars"><?php echo str_repeat('★', (int) round($review_avg)) . str_repeat('☆', 5 - (int) round($review_avg)); ?></div>
                <div class="rs-count"><?php echo $review_count; ?> student review<?php echo $review_count === 1 ? '' : 's'; ?></div>
              </div>
            </div>

            <?php if ($user_id && $is_enrolled): ?>
              <div class="my-review-box">
                <h4><?php echo $my_review ? 'Update Your Review' : 'Rate this Course'; ?></h4>
                <div class="star-rating-input" id="star-rating-input">
                  <?php for ($s = 1; $s <= 5; $s++): ?>
                    <button type="button" data-star="<?php echo $s; ?>" class="<?php echo ($my_review && (int) $my_review['rating'] >= $s) ? 'active' : ''; ?>">★</button>
                  <?php endfor; ?>
                </div>
                <textarea id="review-comment-textarea" placeholder="Share your thoughts about this course (optional)..."><?php echo $my_review ? htmlspecialchars($my_review['comment']) : ''; ?></textarea>
                <div>
                  <button id="save-review-btn" class="btn-primary" style="font-size:12px; padding:8px 18px;"><?php echo $my_review ? 'Update Review' : 'Submit Review'; ?></button>
                  <span class="notes-save-status" id="review-save-status">Saved!</span>
                </div>
              </div>
            <?php elseif ($user_id): ?>
              <p class="no-reviews-msg">Enroll in this course to leave a review.</p>
            <?php endif; ?>

            <div id="reviews-list">
              <?php if (empty($all_reviews)): ?>
                <p class="no-reviews-msg">No reviews yet — be the first to share your experience!</p>
              <?php else: ?>
                <?php foreach ($all_reviews as $rev): ?>
                  <div class="review-item">
                    <div class="review-item-head">
                      <span class="review-item-user"><?php echo htmlspecialchars($rev['username']); ?></span>
                      <span class="review-item-date"><?php echo date('M j, Y', strtotime($rev['created_at'])); ?></span>
                    </div>
                    <div class="review-item-stars"><?php echo str_repeat('★', (int) $rev['rating']) . str_repeat('☆', 5 - (int) $rev['rating']); ?></div>
                    <?php if (!empty($rev['comment'])): ?>
                      <p class="review-item-comment"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>

      <!-- Right side: Chapters navigation sidebar -->
      <div class="player-right">
        <div class="syllabus-sidebar">
          <h3>Lectures Outline</h3>

          <?php if ($user_id && $is_enrolled): ?>
            <div class="progress-summary">
              <span>Your Progress</span>
              <span id="progress-count-text"><?php echo $completed_count; ?>/<?php echo $total_weeks; ?> Completed</span>
            </div>
            <div class="progress-track">
              <div class="progress-fill" id="progress-fill-bar" style="width: <?php echo $total_weeks > 0 ? round(($completed_count / $total_weeks) * 100) : 0; ?>%;"></div>
            </div>
          <?php endif; ?>

          <div class="sidebar-items">
            <?php foreach ($syllabus_items as $index => $item): ?>
              <div class="sidebar-btn <?php echo $index === $resume_index ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                <button class="sidebar-btn-play"
                        style="display:flex; align-items:center; gap:12px; flex:1; background:none; border:none; padding:0; cursor:pointer; text-align:left;"
                        data-video="<?php echo htmlspecialchars($course['video_url']); ?>" 
                        data-topic="<?php echo htmlspecialchars($course['title'] . ': ' . $item['topic']); ?>" 
                        data-week="Week <?php echo $index + 1; ?>"
                        data-index="<?php echo $index; ?>">
                  <div class="sidebar-btn-icon">
                    <!-- Play icon -->
                    <svg fill="currentColor" viewBox="0 0 24 24" style="height:14px;width:14px;"><path d="M8 5v14l11-7z"/></svg>
                  </div>
                  <div class="sidebar-btn-info">
                    <span><?php echo $item['week']; ?></span>
                    <h4><?php echo htmlspecialchars($item['topic']); ?></h4>
                  </div>
                </button>

                <?php if ($user_id && $is_enrolled): ?>
                  <div class="sidebar-btn-check <?php echo in_array($index, $completed_weeks) ? 'done' : ''; ?>"
                       data-index="<?php echo $index; ?>" title="Mark this lecture as complete">
                    <svg viewBox="0 0 24 24" stroke="currentColor"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- NEW: brief "playing next lecture" toast, shown right after a video auto-completes -->
  <div id="autoplay-next-toast">
    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <span id="autoplay-next-toast-text">Lecture complete — playing next…</span>
  </div>

  <!-- Footer -->
  <footer>
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

  <!-- Embed server data for script.js to use -->
  <script>
    window.COURSE_ID = <?php echo json_encode($course_id); ?>;
    window.TOTAL_WEEKS = <?php echo json_encode($total_weeks); ?>;
    window.IS_ENROLLED = <?php echo json_encode((bool) ($user_id && $is_enrolled)); ?>;
    window.NOTES_DATA = <?php echo json_encode($notes_map); ?>;
    window.RESUME_INDEX = <?php echo json_encode($resume_index); ?>;
    window.MY_REVIEW_RATING = <?php echo json_encode($my_review ? (int) $my_review['rating'] : 0); ?>;
    window.COMPLETED_WEEKS = <?php echo json_encode($completed_weeks); ?>;
  </script>

  <!-- NEW: YouTube IFrame Player API — needed so script.js can detect when a
       lecture video ends and auto-tick + auto-advance to the next week.
       If this fails to load (e.g. no internet on the XAMPP machine), the
       player still falls back to a normal embedded iframe; only the
       auto-tick/auto-advance behavior is skipped in that case. -->
  <script src="https://www.youtube.com/iframe_api"></script>

  <!-- Connect script.js -->
  <script src="script.js"></script>

</body>
</html>