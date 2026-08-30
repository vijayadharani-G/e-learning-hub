<?php
require_once 'db_connect.php';

$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS));

    if (!empty($name) && !empty($email) && !empty($message)) {
        // Form submitted successfully
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Our Advisory Office | E-Learning Hub</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Extra styles for enhanced Contact page */
    .contact-side-image {
      width: 100%;
      max-height: 260px;
      overflow: hidden;
      border-radius: var(--radius-md);
      position: relative;
    }
    .contact-side-image img { width: 100%; height: 260px; object-fit: cover; display: block;   }
    .contact-side-image .badge {
      position: absolute; top: 16px; left: 16px;
      background: rgba(255,255,255,0.95);
      color: var(--primary);
      font-size: 11px; font-weight: 700;
      padding: 6px 12px; border-radius: 20px;
      font-family: var(--font-mono);
    }

    .contact-hours-card {
      background: var(--gray-50);
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 20px 22px;
    }
    .contact-hours-card h4 { font-size: 14px; margin-bottom: 12px; color: var(--gray-700); }
    .hours-row { display: flex; justify-content: space-between; font-size: 13px; color: var(--gray-500); padding: 6px 0; border-bottom: 1px dashed var(--gray-100); }
    .hours-row:last-child { border-bottom: none; }
    .hours-row span:last-child { color: var(--gray-700); font-weight: 600; }

    .contact-socials { display: flex; gap: 10px; }
    .contact-socials a {
      width: 38px; height: 38px; border-radius: 50%;
      background: var(--primary-light); color: var(--primary);
      display: flex; align-items: center; justify-content: center;
      transition: var(--transition);
    }
    .contact-socials a:hover { background: var(--primary); color: #fff; }

    .contact-mini-features { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin-top: 4px; }
    .contact-mini-feature {
      display: flex; align-items: flex-start; gap: 10px;
      background: var(--white); border: 1px solid var(--gray-100);
      border-radius: var(--radius-md); padding: 14px;
    }
    .contact-mini-feature svg { flex-shrink: 0; color: var(--primary); height: 18px; width: 18px; margin-top: 2px; }
    .contact-mini-feature h5 { font-size: 12.5px; color: var(--gray-700); margin-bottom: 2px; }
    .contact-mini-feature p { font-size: 11.5px; color: var(--gray-500); line-height: 1.4; margin: 0; }
    /* Fix: right form box had a big empty gap above "Send an Inquiry" 
       because style.css likely centers it vertically. Force top alignment. */
    .split-layout { align-items: flex-start !important; }
    .contact-form-box {
      display: block !important;
      justify-content: flex-start !important;
      align-items: stretch !important;
      height: auto !important;
      align-self: flex-start !important;
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
        <li><a href="courses.php">Courses</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php" class="active">Contact Us</a></li>
      </ul>

      <div class="nav-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
          <span style="font-size:13px; font-weight:600;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
          <a href="courses.php" class="btn-primary" style="font-size:12px; padding: 10px 20px;">My Portal</a>
        <?php else: ?>
          <a href="login.php" class="btn-secondary">Sign In</a>
          <a href="register.php" class="btn-primary">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Split contact layout -->
  <div class="max-width-container split-layout" style="padding-top: 60px; padding-bottom: 80px;">

    <!-- Left side details -->
    <div class="split-left" style="display:flex; flex-direction:column; gap:24px;">

      <!-- NEW: Side image with badge -->
      <div class="contact-side-image">
        <img src="/e-learning-hub/src/assets/images/contact.jpg" alt="Academic Advisory Office">
        <span class="badge">📍 Academic City Campus</span>
      </div>

      <div>
        <span style="font-size: 12px; font-family: var(--font-mono); color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 2px;">GET IN TOUCH</span>
        <h1 style="margin-top: 8px; font-size: 2.2rem;">Contact Our Help Desk</h1>
        <p style="margin-top: 8px;">Have questions about enrollment eligibility, course syllabi, XAMPP packages, or student credentials? Drop us a line and our academic advisors will respond within 24 hours.</p>
      </div>

      <div style="display:flex; flex-direction:column; gap:20px; margin-top:10px;">
        <div style="display:flex; align-items:center; gap:16px;">
          <div style="background-color: var(--primary-light); color: var(--primary); padding:12px; border-radius:12px; display:flex;">
            <svg fill="currentColor" viewBox="0 0 24 24" style="height:20px; width:20px;"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
          </div>
          <div>
            <span style="font-size:10px; font-family:var(--font-mono); font-weight:bold; color:var(--gray-400); text-transform:uppercase;">Institutional Phone</span>
            <span style="display:block; font-size:14px; font-weight:600; margin-top:2px;">+91 98765 43210</span>
          </div>
        </div>

        <div style="display:flex; align-items:center; gap:16px;">
          <div style="background-color: var(--primary-light); color: var(--primary); padding:12px; border-radius:12px; display:flex;">
            <svg fill="currentColor" viewBox="0 0 24 24" style="height:20px; width:20px;"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
          </div>
          <div>
            <span style="font-size:10px; font-family:var(--font-mono); font-weight:bold; color:var(--gray-400); text-transform:uppercase;">Advisor Email</span>
            <span style="display:block; font-size:14px; font-weight:600; margin-top:2px;">support@elearninghub.com</span>
          </div>
        </div>

        <div style="display:flex; align-items:center; gap:16px;">
          <div style="background-color: var(--primary-light); color: var(--primary); padding:12px; border-radius:12px; display:flex;">
            <svg fill="currentColor" viewBox="0 0 24 24" style="height:20px; width:20px;"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
          </div>
          <div>
            <span style="font-size:10px; font-family:var(--font-mono); font-weight:bold; color:var(--gray-400); text-transform:uppercase;">Main Building Location</span>
            <span style="display:block; font-size:14px; font-weight:600; margin-top:2px;">Block C, Tech Corridor, Academic City</span>
          </div>
        </div>
      </div>

      <!-- NEW: Office hours -->
      <div class="contact-hours-card">
        <h4>🕒 Advisory Office Hours</h4>
        <div class="hours-row"><span>Monday – Friday</span><span>9:00 AM – 7:00 PM</span></div>
        <div class="hours-row"><span>Saturday</span><span>10:00 AM – 4:00 PM</span></div>
        <div class="hours-row"><span>Sunday</span><span>Closed</span></div>
      </div>

      <!-- NEW: Mini features -->
      <div class="contact-mini-features">
        <div class="contact-mini-feature">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          <div>
            <h5>Fast Response</h5>
            <p>Replies within 24 hours on business days.</p>
          </div>
        </div>
        <div class="contact-mini-feature">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <div>
            <h5>Verified Advisors</h5>
            <p>Talk to real academic staff, not bots.</p>
          </div>
        </div>
        <div class="contact-mini-feature">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <div>
            <h5>Book a Visit</h5>
            <p>Walk-ins welcome during office hours.</p>
          </div>
        </div>
        <div class="contact-mini-feature">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l1.586-1.586z"/></svg>
          <div>
            <h5>Live Chat Support</h5>
            <p>Instant help via the student dashboard.</p>
          </div>
        </div>
      </div>

      <!-- NEW: Socials -->
      <div>
        <span style="font-size:10px; font-family:var(--font-mono); font-weight:bold; color:var(--gray-400); text-transform:uppercase; display:block; margin-bottom:10px;">Follow Us</span>
        <div class="contact-socials">
          <a href="#" aria-label="Facebook"><svg fill="currentColor" viewBox="0 0 24 24" style="height:16px;width:16px;"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95z"/></svg></a>
          <a href="#" aria-label="Instagram"><svg fill="currentColor" viewBox="0 0 24 24" style="height:16px;width:16px;"><path d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm10 2a1 1 0 110 2 1 1 0 010-2zm-5 2a5 5 0 110 10 5 5 0 010-10zm0 2a3 3 0 100 6 3 3 0 000-6z"/></svg></a>
          <a href="#" aria-label="LinkedIn"><svg fill="currentColor" viewBox="0 0 24 24" style="height:16px;width:16px;"><path d="M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14zM8.34 18v-7.4H5.67V18h2.67zM7 9.5A1.5 1.5 0 107 6.5a1.5 1.5 0 000 3zM18.5 18v-4.2c0-2.25-1.2-3.3-2.8-3.3-1.29 0-1.87.71-2.19 1.2h.03v-1.03H11v7.33h2.67v-4.1c0-.62.12-1.23.9-1.23.77 0 .78.72.78 1.27V18h2.65z"/></svg></a>
        </div>
      </div>
    </div>

    <!-- Right side form box -->
    <div class="contact-form-box">
      <h3>Send an Inquiry</h3>

      <?php if ($success): ?>
        <div class="alert-success">
          <svg fill="currentColor" viewBox="0 0 24 24" style="height:20px; width:20px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
          <div>
            <strong>Inquiry Sent Successfully!</strong>
            <p style="font-size:12px; margin-top:2px; color:#065f46;">Thank you! Our academic advisors will contact you shortly.</p>
          </div>
        </div>
      <?php endif; ?>

      <form action="contact.php" method="POST">
        <div class="form-group">
          <label>Your Name</label>
          <input type="text" name="name" class="form-control" placeholder="Your full name" required>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="your.email@domain.com" required>
        </div>

        <div class="form-group">
          <label>Inquiry Details</label>
          <textarea name="message" class="form-control" placeholder="Type details regarding classes or technical configurations..." required></textarea>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px 0;">
          Send Advisory Request
        </button>
      </form>
    </div>

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

</body>
</html>