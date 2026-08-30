<?php
require_once 'db_connect.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['course_id'])) {
    header("Location: courses.php");
    exit;
}

$course_id = trim($_GET['course_id']);

// Fetch course
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

// Confirm enrollment
$stmt_chk = $conn->prepare("SELECT id FROM registrations WHERE user_id = ? AND course_id = ?");
$stmt_chk->bind_param("is", $user_id, $course_id);
$stmt_chk->execute();
$is_enrolled = $stmt_chk->get_result()->num_rows > 0;
$stmt_chk->close();

if (!$is_enrolled) {
    header("Location: courses.php");
    exit;
}

// This must match the syllabus length used in course_detail.php
$total_weeks = 4;

// Confirm all weeks are complete
$stmt_prog = $conn->prepare(
    "SELECT COUNT(*) AS cnt FROM lecture_progress WHERE user_id = ? AND course_id = ? AND completed = 1"
);
$stmt_prog->bind_param("is", $user_id, $course_id);
$stmt_prog->execute();
$completed_count = 0;
if ($row = $stmt_prog->get_result()->fetch_assoc()) {
    $completed_count = (int) $row['cnt'];
}
$stmt_prog->close();

$all_complete = $completed_count >= $total_weeks;

if (!$all_complete) {
    header("Location: course_detail.php?id=" . urlencode($course_id));
    exit;
}

$student_name = htmlspecialchars($_SESSION['username']);
$certificate_id = 'CERT-' . strtoupper(substr(md5($user_id . $course_id), 0, 8));
$issue_date = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Certificate | <?php echo htmlspecialchars($course['title']); ?></title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <style>
    .cert-page-wrap {
      max-width: 900px;
      margin: 60px auto;
      padding: 0 20px;
      text-align: center;
    }
    .cert-actions {
      display: flex;
      justify-content: center;
      gap: 14px;
      margin-bottom: 30px;
    }
    #certificate-canvas {
      background: linear-gradient(135deg, #ffffff, #f8fafc);
      border: 10px solid #4f46e5;
      border-radius: 12px;
      padding: 60px 50px;
      position: relative;
      box-shadow: 0 20px 50px rgba(0,0,0,0.12);
    }
    #certificate-canvas::before {
      content: "";
      position: absolute;
      inset: 14px;
      border: 2px solid #c7d2fe;
      border-radius: 6px;
      pointer-events: none;
    }
    .cert-brand {
      font-size: 13px;
      letter-spacing: 3px;
      text-transform: uppercase;
      color: #6366f1;
      font-weight: 700;
      margin-bottom: 18px;
    }
    .cert-heading {
      font-size: 14px;
      color: #64748b;
      margin-bottom: 6px;
    }
    .cert-name {
      font-size: 2.4rem;
      font-weight: 700;
      color: #1e293b;
      margin: 10px 0 18px;
      font-family: Georgia, 'Times New Roman', serif;
    }
    .cert-body-text {
      font-size: 14px;
      color: #475569;
      line-height: 1.7;
      max-width: 560px;
      margin: 0 auto 26px;
    }
    .cert-course-title {
      font-size: 1.4rem;
      font-weight: 700;
      color: #4f46e5;
      margin-bottom: 30px;
    }
    .cert-footer-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-top: 40px;
      font-size: 12px;
      color: #64748b;
    }
    .cert-footer-row strong { color: #1e293b; display: block; font-size: 13px; }
    .cert-seal {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: linear-gradient(135deg, #f59e0b, #ef4444);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 26px;
      margin: 0 auto 8px;
      box-shadow: 0 6px 16px rgba(239,68,68,0.35);
    }
  </style>
</head>
<body>
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
      <div class="nav-actions">
        <a href="courses.php" class="btn-secondary">Back to My Courses</a>
      </div>
    </div>
  </header>

  <div class="cert-page-wrap">
    <div class="cert-actions">
      <button type="button" id="download-cert-btn" class="btn-primary">Download Certificate (PDF)</button>
      <a href="course_detail.php?id=<?php echo urlencode($course_id); ?>" class="btn-secondary">Back to Course</a>
    </div>

    <div id="certificate-canvas">
      <div class="cert-seal">🎓</div>
      <div class="cert-brand">E-Learning Hub Academy</div>
      <div class="cert-heading">This certifies that</div>
      <div class="cert-name"><?php echo $student_name; ?></div>
      <div class="cert-body-text">has successfully completed all coursework and requirements for the program</div>
      <div class="cert-course-title"><?php echo htmlspecialchars($course['title']); ?></div>

      <div class="cert-footer-row">
        <div style="text-align:left;">
          <strong><?php echo $issue_date; ?></strong>
          Date Issued
        </div>
        <div style="text-align:right;">
          <strong><?php echo $certificate_id; ?></strong>
          Certificate ID
        </div>
      </div>
    </div>
  </div>

  <script>
    const STUDENT_NAME = <?php echo json_encode($_SESSION['username']); ?>;
    const COURSE_TITLE = <?php echo json_encode($course['title']); ?>;
    const CERT_ID = <?php echo json_encode($certificate_id); ?>;
    const ISSUE_DATE = <?php echo json_encode($issue_date); ?>;

    document.getElementById('download-cert-btn').addEventListener('click', () => {
      if (!window.jspdf) {
        alert('PDF library failed to load. Check your internet connection and try again.');
        return;
      }
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });
      const w = doc.internal.pageSize.getWidth();
      const h = doc.internal.pageSize.getHeight();

      // Border
      doc.setDrawColor(79, 70, 229);
      doc.setLineWidth(6);
      doc.rect(20, 20, w - 40, h - 40);
      doc.setDrawColor(199, 210, 254);
      doc.setLineWidth(1.5);
      doc.rect(34, 34, w - 68, h - 68);

      doc.setTextColor(99, 102, 241);
      doc.setFontSize(13);
      doc.setFont(undefined, 'bold');
      doc.text('E-LEARNING HUB ACADEMY', w / 2, 90, { align: 'center' });

      doc.setTextColor(100, 116, 139);
      doc.setFontSize(12);
      doc.setFont(undefined, 'normal');
      doc.text('This certifies that', w / 2, 130, { align: 'center' });

      doc.setTextColor(30, 41, 59);
      doc.setFontSize(30);
      doc.setFont(undefined, 'bold');
      doc.text(STUDENT_NAME, w / 2, 170, { align: 'center' });

      doc.setTextColor(71, 85, 105);
      doc.setFontSize(12);
      doc.setFont(undefined, 'normal');
      doc.text('has successfully completed all coursework and requirements for the program', w / 2, 200, { align: 'center' });

      doc.setTextColor(79, 70, 229);
      doc.setFontSize(18);
      doc.setFont(undefined, 'bold');
      doc.text(COURSE_TITLE, w / 2, 235, { align: 'center' });

      doc.setTextColor(100, 116, 139);
      doc.setFontSize(10);
      doc.setFont(undefined, 'normal');
      doc.text('Date Issued: ' + ISSUE_DATE, 70, h - 60);
      doc.text('Certificate ID: ' + CERT_ID, w - 70, h - 60, { align: 'right' });

      doc.save('certificate-' + CERT_ID + '.pdf');
    });
  </script>
</body>
</html>