<?php
require_once 'db_connect.php';

$error = "";
$registration_success = false;
$new_username = "";
$total_selected_count = 0;

// Fetch courses list to populate the interest selector
$courses_res = $conn->query("SELECT id, title, category, level FROM courses ORDER BY title ASC");
$courses_list = [];
if ($courses_res) {
    while ($row = $courses_res->fetch_assoc()) {
        $courses_list[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $selected_course_ids = isset($_POST['course_ids']) && is_array($_POST['course_ids']) ? $_POST['course_ids'] : [];

    // Remove empty/duplicate values before counting
    $selected_course_ids = array_values(array_unique(array_filter($selected_course_ids, function ($v) {
        return trim($v) !== '';
    })));

    // Registration is always free — up to FREE_SLOTS courses can be picked here.
    // Anything beyond that must be unlocked later on the courses page via payment.
    $free_slots = 2;

    // Password must contain at least one letter and one number, min 6 chars.
    // This blocks weak all-numeric passwords like "123456".
    $password_is_strong = preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,}$/', $password) === 1;

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($selected_course_ids)) {
        $error = "All registration fields are mandatory. Please select at least 1 course you're interested in.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!$password_is_strong) {
        $error = "Password is too weak. Use at least 6 characters with a mix of letters and numbers (e.g. not just \"123456\").";
    } elseif ($password !== $confirm_password) {
        $error = "Password and Confirm Password do not match.";
    } elseif (count($selected_course_ids) > $free_slots) {
        $error = "You can select up to {$free_slots} free courses at sign-up. Additional courses can be unlocked later from the courses page.";
    } else {
        // Check if username already exists
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");

        if ($stmt_check === false) {
            $error = "Database error while checking existing user: " . htmlspecialchars($conn->error);
        } else {
            $stmt_check->bind_param("ss", $username, $email);
            $stmt_check->execute();
            $res_check = $stmt_check->get_result();

            if ($res_check->num_rows > 0) {
                $error = "Username or Email already registered.";
            } else {
                // Hash password safely
                $pass_hash = password_hash($password, PASSWORD_BCRYPT);

                // Insert new user
                $stmt_insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

                if ($stmt_insert === false) {
                    $error = "Database error while creating account: " . htmlspecialchars($conn->error);
                } else {
                    $stmt_insert->bind_param("sss", $username, $email, $pass_hash);

                    if ($stmt_insert->execute()) {
                        $new_user_id = $stmt_insert->insert_id;

                        // Save selected courses as "interested" (marks them unlocked/free
                        // so they don't show a paywall later on the courses page).
                        $stmt_interest = $conn->prepare("INSERT INTO interested_courses (user_id, course_id) VALUES (?, ?)");

                        if ($stmt_interest === false) {
                            // Table missing or column mismatch — don't fatal-error, just warn and continue.
                            // Run the interested_courses CREATE TABLE SQL in phpMyAdmin to fix this.
                            error_log("interested_courses prepare failed: " . $conn->error);
                        } else {
                            foreach ($selected_course_ids as $selected_course_id) {
                                $selected_course_id = trim($selected_course_id);
                                if ($selected_course_id === '') continue;
                                $stmt_interest->bind_param("is", $new_user_id, $selected_course_id);
                                $stmt_interest->execute();
                            }
                            $stmt_interest->close();
                        }

                        // Also auto-register the student for the courses picked at sign-up,
                        // so they show up right away under "My Registered Curriculums".
                        $stmt_autoreg = $conn->prepare("INSERT INTO registrations (user_id, course_id) VALUES (?, ?)");

                        if ($stmt_autoreg === false) {
                            error_log("registrations prepare failed: " . $conn->error);
                        } else {
                            foreach ($selected_course_ids as $selected_course_id) {
                                $selected_course_id = trim($selected_course_id);
                                if ($selected_course_id === '') continue;
                                $stmt_autoreg->bind_param("is", $new_user_id, $selected_course_id);
                                $stmt_autoreg->execute();
                            }
                            $stmt_autoreg->close();
                        }

                        // Start Session
                        $_SESSION['user_id'] = $new_user_id;
                        $_SESSION['username'] = $username;

                        $total_selected_count = count($selected_course_ids);

                        // Instead of an immediate redirect, show a welcome animation first
                        $registration_success = true;
                        $new_username = $username;
                    } else {
                        $error = "Registration failed. Try again later.";
                    }
                    $stmt_insert->close();
                }
            }
            $stmt_check->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Portal Register | E-Learning Hub</title>
  <link rel="stylesheet" href="style.css">
  <?php if ($registration_success): ?>
  <style>
    /* Page stays visible/scrollable underneath — this is a modal, not a takeover */
    .welcome-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(3px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 999;
      padding: 20px;
    }

    .welcome-card {
      background: #ffffff;
      border-radius: var(--radius-lg, 16px);
      max-width: 400px;
      width: 100%;
      padding: 40px 36px 32px;
      text-align: center;
      box-shadow: 0 20px 60px rgba(15, 23, 42, 0.35);
      transform: scale(0.94) translateY(10px);
      opacity: 0;
      animation: cardPop 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
      position: relative;
    }
    @keyframes cardPop {
      to { transform: scale(1) translateY(0); opacity: 1; }
    }

    .welcome-burst {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background: linear-gradient(135deg, #6366f1, #ec4899);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
    }
    .welcome-burst svg {
      width: 30px;
      height: 30px;
      stroke: white;
      fill: none;
      stroke-width: 3;
      stroke-linecap: round;
      stroke-linejoin: round;
      stroke-dasharray: 40;
      stroke-dashoffset: 40;
      animation: checkDraw 0.4s ease-out 0.3s forwards;
    }

    @keyframes checkDraw {
      to { stroke-dashoffset: 0; }
    }

    .welcome-text h1 {
      color: var(--gray-800, #1e293b);
      font-size: 1.4rem;
      margin-bottom: 8px;
      opacity: 0;
      transform: translateY(10px);
      animation: fadeUp 0.5s ease-out 0.3s forwards;
    }
    .welcome-text p {
      color: var(--gray-500, #64748b);
      font-size: 13px;
      line-height: 1.6;
      max-width: 320px;
      margin: 0 auto;
      opacity: 0;
      transform: translateY(10px);
      animation: fadeUp 0.5s ease-out 0.4s forwards;
    }
    .welcome-text span.username-highlight {
      background: var(--accent-gradient, linear-gradient(135deg,#6366f1,#ec4899));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    @keyframes fadeUp {
      to { opacity: 1; transform: translateY(0); }
    }

    .welcome-eyebrow {
      display: inline-block;
      font-family: var(--font-mono);
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: #6366f1;
      background: #eef2ff;
      border: 1px solid #e0e7ff;
      padding: 4px 12px;
      border-radius: 50px;
      margin-bottom: 12px;
      opacity: 0;
      animation: fadeUp 0.5s ease-out 0.15s forwards;
    }

    .welcome-summary {
      width: 100%;
      background: var(--gray-50, #f8fafc);
      border: 1px solid var(--gray-100, #f1f5f9);
      border-radius: var(--radius-md, 10px);
      padding: 14px 18px;
      margin-top: 22px;
      opacity: 0;
      transform: translateY(10px);
      animation: fadeUp 0.5s ease-out 0.5s forwards;
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 12px;
      color: var(--gray-500, #64748b);
      padding: 7px 0;
      border-bottom: 1px dashed var(--gray-100, #e2e8f0);
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-row strong { color: var(--gray-800, #1e293b); font-size: 13px; }

    .welcome-redirect-note {
      margin-top: 22px;
      font-size: 11px;
      color: #6366f1;
      font-family: var(--font-mono);
      letter-spacing: 1px;
      text-transform: uppercase;
      opacity: 0;
      animation: fadeUp 0.5s ease-out 0.6s forwards;
    }
  </style>
  <?php endif; ?>

  <?php if (!$registration_success): ?>
  <style>
    /* Locked course row (once the free limit is reached) */
    .course-row.locked {
      opacity: 0.55;
      cursor: not-allowed;
      position: relative;
      background: var(--gray-50, #f8fafc) !important;
    }
    .course-row .lock-chip {
      display: none;
      align-items: center;
      gap: 4px;
      font-size: 10px;
      font-weight: 700;
      color: #9333ea;
      background: #f3e8ff;
      padding: 3px 8px;
      border-radius: 50px;
      font-family: var(--font-mono);
      letter-spacing: 0.4px;
      text-transform: uppercase;
      flex-shrink: 0;
    }
    .course-row.locked .lock-chip { display: inline-flex; }
    .course-row.locked .lock-chip svg { width: 10px; height: 10px; stroke: #9333ea; fill: none; stroke-width: 2.5; }

    /* Confirm-password mismatch hint */
    #confirm-password-hint {
      font-size: 12px;
      font-weight: 600;
      margin-top: 6px;
      display: none;
    }
    #confirm-password-hint.error { color: #ef4444; display: block; }
    #confirm-password-hint.ok { color: var(--success, #10b981); display: block; }

    #password-strength-hint {
      font-size: 11px;
      color: var(--gray-500, #64748b);
      margin-top: 6px;
    }

    /* Password field with eye-toggle icon */
    .password-input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }
    .password-input-wrapper input {
      width: 100%;
      padding-right: 42px;
    }
    .password-toggle-btn {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      padding: 4px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--gray-500, #64748b);
      line-height: 0;
    }
    .password-toggle-btn:hover {
      color: var(--primary, #6366f1);
    }
    .password-toggle-btn svg {
      width: 18px;
      height: 18px;
      stroke: currentColor;
      fill: none;
      stroke-width: 2;
      stroke-linecap: round;
      stroke-linejoin: round;
    }
    .password-toggle-btn .eye-off-icon { display: none; }
    .password-toggle-btn.is-visible .eye-icon { display: none; }
    .password-toggle-btn.is-visible .eye-off-icon { display: block; }
  </style>
  <?php endif; ?>
</head>
<body>

  <?php if ($registration_success): ?>
    <!-- Professional centered success modal (not a full-screen takeover) -->
    <div class="welcome-overlay" id="welcome-overlay">
      <div class="welcome-card">
        <div class="welcome-burst">
          <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <div class="welcome-text">
          <span class="welcome-eyebrow">Account Created</span>
          <h1>Welcome aboard, <span class="username-highlight"><?php echo htmlspecialchars($new_username); ?></span></h1>
          <p>Your student account has been set up successfully. Here's a quick summary of your registration:</p>
        </div>

        <div class="welcome-summary">
          <div class="summary-row">
            <span>Courses selected</span>
            <strong><?php echo (int)$total_selected_count; ?></strong>
          </div>
          <div class="summary-row">
            <span>Plan</span>
            <strong>Free registration</strong>
          </div>
        </div>

        <button type="button" id="welcome-continue-btn" class="btn-primary" style="width: 100%; justify-content: center; padding: 12px 0; margin-top: 24px;">
          Continue to Course Catalog
        </button>
        <div class="welcome-redirect-note" id="welcome-redirect-note">Redirecting automatically in <span id="welcome-countdown">4</span>s…</div>
      </div>
    </div>

    <script>
      (function () {
        const countdownEl = document.getElementById('welcome-countdown');
        const continueBtn = document.getElementById('welcome-continue-btn');
        let secondsLeft = 4;
        let redirected = false;

        const goToCatalog = () => {
          if (redirected) return;
          redirected = true;
          window.location.href = 'courses.php';
        };

        const timer = setInterval(function () {
          secondsLeft -= 1;
          if (secondsLeft <= 0) {
            clearInterval(timer);
            goToCatalog();
            return;
          }
          if (countdownEl) countdownEl.textContent = secondsLeft;
        }, 1000);

        if (continueBtn) {
          continueBtn.addEventListener('click', function () {
            clearInterval(timer);
            goToCatalog();
          });
        }
      })();
    </script>
  <?php else: ?>

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
        <li><a href="contact.php">Contact Us</a></li>
      </ul>

      <div class="nav-actions">
        <a href="login.php" class="btn-secondary">Sign In</a>
      </div>
    </div>
  </header>

  <!-- Register Card Container -->
  <div class="auth-container">
    <div class="auth-card">

      <!-- Left Side decoration -->
      <div class="auth-decor" style="background-image: linear-gradient(180deg, rgba(15,23,42,0.55), rgba(30,27,75,0.75) 60%, rgba(238,70,154,0.55)), url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80'); background-size: cover; background-position: center;">
        <div>
          <span style="font-family: var(--font-mono); font-size: 10px; border-bottom: 2px solid #6366f1; padding-bottom: 4px; letter-spacing: 2px;">SECURE REGISTRATION</span>
        </div>
        <div style="margin-top: 100px;">
          <h2>Academic Portal Registration</h2>
          <p>Gain access to dynamic lecture syllabi, verified learning modules, and live browser playground blocks. Tell us what you're interested in, then enroll from your dashboard.</p>
        </div>
        <div style="font-size: 10px; color: #a5b4fc; font-family: var(--font-mono); margin-top: 50px;">
          XAMPP SECURITY VERIFIED
        </div>
      </div>

      <!-- Right Side form -->
      <div class="auth-form-side" style="padding: 30px 48px;">
        <h3>Student Sign-Up</h3>
        <p class="subtitle">Create an account to track your study lectures.</p>

        <?php if (!empty($error)): ?>
          <div class="alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" id="register-form">
          <div class="form-group" style="margin-bottom: 15px;">
            <label>Desired Username</label>
            <input type="text" name="username" class="form-control" placeholder="e.g. LearnerDharani" required>
          </div>

          <div class="form-group" style="margin-bottom: 15px;">
            <label>Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. learner@domain.com" required>
          </div>

          <div class="form-group" style="margin-bottom: 15px;">
            <label>
              Courses You're Interested In
              <span id="course-count-badge" style="font-family: var(--font-mono); font-size: 11px; font-weight: 700; color: var(--gray-500);">(0 / 2 selected)</span>
            </label>
            <div id="course-checkbox-grid" style="display: flex; flex-direction: column; gap: 8px; max-height: 240px; overflow-y: auto; border: 1px solid var(--gray-100); border-radius: var(--radius-md); padding: 12px; background-color: var(--gray-50);">
              <?php foreach ($courses_list as $course): ?>
                <label class="course-row" style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 500; color: var(--gray-700); background: var(--white); border: 1px solid var(--gray-100); border-radius: var(--radius-sm); padding: 10px 12px; cursor: pointer; width: 100%;">
                  <input type="checkbox" name="course_ids[]" value="<?php echo htmlspecialchars($course['id']); ?>" class="course-select-checkbox" data-title="<?php echo htmlspecialchars($course['title']); ?>" style="width: 16px; height: 16px; flex-shrink: 0; accent-color: var(--primary);">
                  <span style="flex: 1;"><?php echo htmlspecialchars($course['title']); ?> <span style="color: var(--gray-500); font-weight: 400;">(<?php echo htmlspecialchars($course['category']); ?>)</span></span>
                  <span class="lock-chip"><svg viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>Locked</span>
                </label>
              <?php endforeach; ?>
            </div>
            <p id="course-min-warning" style="font-size: 12px; font-weight: 600; color: #ef4444; margin-top: 8px; display: none;">⚠ Please select at least 1 course.</p>
            <p style="font-size: 11px; color: var(--gray-500); margin-top: 6px;">Free sign-up lets you pick <strong>up to 2 courses</strong> — they'll be registered automatically and show up right away on your dashboard under "My Registered Curriculums". Want more? You can unlock additional courses with a one-time payment later from the courses page.</p>
          </div>

          <div class="form-group" style="margin-bottom: 15px;">
            <label>Security Password</label>
            <div class="password-input-wrapper">
              <input type="password" name="password" id="password-field" class="form-control" placeholder="Enter the password (Min 6 characters, letters + numbers)" autocomplete="new-password" required>
              <button type="button" class="password-toggle-btn" data-target="password-field" aria-label="Show password">
                <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-off-icon" viewBox="0 0 24 24"><path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a18.5 18.5 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 7 11 7a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
            <p id="password-strength-hint">Use at least 6 characters with a mix of letters and numbers — not just numbers like "123456".</p>
          </div>

          <div class="form-group" style="margin-bottom: 15px;">
            <label>Confirm Password</label>
            <div class="password-input-wrapper">
              <input type="password" name="confirm_password" id="confirm-password-field" class="form-control" placeholder="Re-enter the same password" autocomplete="new-password" required>
              <button type="button" class="password-toggle-btn" data-target="confirm-password-field" aria-label="Show password">
                <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-off-icon" viewBox="0 0 24 24"><path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a18.5 18.5 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 7 11 7a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
            <p id="confirm-password-hint"></p>
          </div>

          <button type="submit" id="register-submit-btn" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px 0; margin-top: 10px;">
            Complete Student Registration
          </button>
        </form>

        <div class="auth-toggle">
          Already registered? <button onclick="window.location.href='login.php'">Sign in to student portal</button>
        </div>
      </div>

    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const checkboxes = document.querySelectorAll(".course-select-checkbox");
      const countBadge = document.getElementById("course-count-badge");
      const warningMsg = document.getElementById("course-min-warning");
      const registerForm = document.getElementById("register-form");
      const passwordField = document.getElementById("password-field");
      const confirmField = document.getElementById("confirm-password-field");
      const confirmHint = document.getElementById("confirm-password-hint");

      const FREE_MAX_COURSES = 2;
      const MIN_COURSES = 1;

      // Must contain at least one letter and one number, min 6 chars.
      const isPasswordStrong = (pwd) => /^(?=.*[A-Za-z])(?=.*\d).{6,}$/.test(pwd);

      const getSelectedCount = () => document.querySelectorAll(".course-select-checkbox:checked").length;

      // Lock unchecked boxes once the free limit is reached — no payment
      // option here, extra courses are unlocked later on the courses page.
      const updateCheckboxLocking = () => {
        const selectedCount = getSelectedCount();
        checkboxes.forEach((cb) => {
          const row = cb.closest(".course-row");
          if (!cb.checked) {
            const shouldLock = selectedCount >= FREE_MAX_COURSES;
            cb.disabled = shouldLock;
            if (row) row.classList.toggle("locked", shouldLock);
          } else if (row) {
            row.classList.remove("locked");
            cb.disabled = false;
          }
        });
      };

      const updateCourseCount = () => {
        const selectedCount = getSelectedCount();
        countBadge.textContent = `(${selectedCount} / ${FREE_MAX_COURSES} selected)`;

        if (selectedCount >= MIN_COURSES) {
          countBadge.style.color = "var(--success)";
          warningMsg.style.display = "none";
        } else {
          countBadge.style.color = "var(--gray-500)";
        }

        updateCheckboxLocking();
      };

      checkboxes.forEach((cb) => {
        cb.addEventListener("change", () => {
          updateCourseCount();
        });
      });

      updateCourseCount();

      // Live confirm-password feedback
      const checkPasswordMatch = () => {
        if (confirmField.value === "") {
          confirmHint.className = "";
          confirmHint.textContent = "";
          return true;
        }
        if (passwordField.value !== confirmField.value) {
          confirmHint.className = "error";
          confirmHint.textContent = "⚠ Passwords do not match.";
          return false;
        }
        confirmHint.className = "ok";
        confirmHint.textContent = "✓ Passwords match.";
        return true;
      };

      passwordField.addEventListener("input", checkPasswordMatch);
      confirmField.addEventListener("input", checkPasswordMatch);

      // Eye-icon toggle for password visibility
      document.querySelectorAll(".password-toggle-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
          const target = document.getElementById(btn.dataset.target);
          if (!target) return;
          const isVisible = target.type === "text";
          target.type = isVisible ? "password" : "text";
          btn.classList.toggle("is-visible", !isVisible);
          btn.setAttribute("aria-label", isVisible ? "Show password" : "Hide password");
        });
      });

      if (registerForm) {
        registerForm.addEventListener("submit", (e) => {
          const selectedCount = getSelectedCount();

          if (selectedCount < MIN_COURSES) {
            e.preventDefault();
            warningMsg.textContent = "⚠ Please select at least 1 course.";
            warningMsg.style.display = "block";
            warningMsg.scrollIntoView({ behavior: "smooth", block: "center" });
            return;
          }

          if (selectedCount > FREE_MAX_COURSES) {
            e.preventDefault();
            warningMsg.textContent = "⚠ You can only select up to 2 free courses at sign-up.";
            warningMsg.style.display = "block";
            warningMsg.scrollIntoView({ behavior: "smooth", block: "center" });
            return;
          }

          if (!isPasswordStrong(passwordField.value)) {
            e.preventDefault();
            passwordField.classList.add("input-shake");
            passwordField.scrollIntoView({ behavior: "smooth", block: "center" });
            setTimeout(() => passwordField.classList.remove("input-shake"), 500);
            return;
          }

          if (!checkPasswordMatch()) {
            e.preventDefault();
            confirmField.classList.add("input-shake");
            confirmField.scrollIntoView({ behavior: "smooth", block: "center" });
            setTimeout(() => confirmField.classList.remove("input-shake"), 500);
          }
        });
      }
    });
  </script>

  <?php endif; ?>

</body>
</html>