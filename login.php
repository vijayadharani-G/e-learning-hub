<?php
require_once 'db_connect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // "username" field now accepts either the username OR the email
    // that was used at registration.
    $login_identifier = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $password = trim($_POST['password']);

    if (empty($login_identifier) || empty($password)) {
        $error = "Please fill in all security fields.";
    } else {
        // Query database securely with prepared statements — match on
        // username OR email so the exact credentials used at sign-up work.
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $login_identifier, $login_identifier);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "No account found matching that username or email.";
        } else {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Save session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: courses.php");
                exit;
            } else {
                $error = "Incorrect password entered.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Portal Sign In | E-Learning Hub</title>
  <style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');

:root {
  --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
  --font-mono: 'JetBrains Mono', monospace;
  --primary: #4f46e5;
  --primary-hover: #4338ca;
  --primary-light: #e0e7ff;
  --success: #10b981;
  --success-light: #ecfdf5;
  --dark: #0f172a;
  --gray-900: #1e293b;
  --gray-700: #334155;
  --gray-600: #475569;
  --gray-500: #64748b;
  --gray-300: #cbd5e1;
  --gray-100: #f1f5f9;
  --gray-50: #f8fafc;
  --white: #ffffff;
  --accent-gradient: linear-gradient(135deg, #4f46e5 0%, #ee469a 100%);
  --bg-professional-light: #f5f7fb;
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  --shadow-sm: 0 2px 4px 0 rgba(0, 0, 0, 0.02);
  --shadow-md: 0 10px 20px -5px rgba(79, 70, 229, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.01);
  --shadow-lg: 0 20px 25px -5px rgba(79, 70, 229, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
  --shadow-xl: 0 25px 50px -12px rgba(15, 23, 42, 0.15);
  --transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
}
html { scroll-behavior: smooth; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: var(--font-sans); background-color: var(--bg-professional-light); color: var(--gray-700); line-height: 1.5; -webkit-font-smoothing: antialiased; }
h1, h2, h3, h4 { color: var(--dark); font-weight: 700; line-height: 1.25; }
a { text-decoration: none; color: inherit; transition: var(--transition); }
img { max-width: 100%; display: block; }
button, input, select, textarea { font-family: inherit; font-size: inherit; border: none; outline: none; }
.max-width-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; width: 100%; }
header { position: sticky; top: 0; z-index: 100; background-color: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(79, 70, 229, 0.08); box-shadow: var(--shadow-sm); height: 80px; transition: var(--transition); }
.nav-wrapper { display: flex; justify-content: space-between; align-items: center; height: 100%; }
.logo-wrapper { display: flex; align-items: center; gap: 12px; }
.logo-icon { background: var(--accent-gradient); color: var(--white); padding: 10px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25); }
.logo-icon svg { height: 24px; width: 24px; }
.logo-text h2 { font-size: 1.1rem; font-weight: 800; background: var(--accent-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.logo-text p { font-size: 9px; font-family: var(--font-mono); color: var(--gray-500); text-transform: uppercase; letter-spacing: 2px; }
.nav-links { display: flex; gap: 32px; align-items: center; list-style: none; }
.nav-links a { font-size: 14px; font-weight: 500; color: var(--gray-600); position: relative; padding: 8px 0; }
.nav-links a:hover, .nav-links a.active { color: var(--primary); }
.nav-links a::after { content: ''; position: absolute; bottom: 0; left: 0; width: 0; height: 2px; background: var(--accent-gradient); border-radius: 2px; transition: var(--transition); }
.nav-links a:hover::after, .nav-links a.active::after { width: 100%; }
.nav-actions { display: flex; align-items: center; gap: 16px; }
.btn-secondary { font-size: 14px; font-weight: 500; color: var(--gray-700); background-color: transparent; cursor: pointer; padding: 10px 16px; border-radius: var(--radius-md); }
.btn-secondary:hover { color: var(--primary); background-color: var(--primary-light); }
.btn-primary { background: var(--accent-gradient); color: var(--white); font-size: 14px; font-weight: 600; padding: 12px 24px; border-radius: var(--radius-md); cursor: pointer; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3); transition: var(--transition); display: inline-flex; align-items: center; gap: 8px; }
.btn-primary:hover { box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4); transform: translateY(-2px); }
.auth-container { display: flex; align-items: center; justify-content: center; padding: 80px 0; min-height: calc(100vh - 160px); background-color: var(--gray-50); }
.auth-card { display: grid; grid-template-columns: 1.1fr 1fr; background-color: var(--white); border-radius: var(--radius-xl); border: 1px solid var(--gray-100); box-shadow: var(--shadow-xl); overflow: hidden; max-width: 850px; width: 100%; animation: scaleIn 0.5s cubic-bezier(0.25, 1, 0.5, 1) both; }
.auth-decor { color: var(--white); padding: 48px; display: flex; flex-direction: column; justify-content: space-between; position: relative; background: linear-gradient(135deg, #0f172a, #1e1b4b, #ee469a); }
.auth-decor h2 { color: var(--white); font-size: 1.8rem; margin-bottom: 12px; }
.auth-decor p { font-size: 13px; color: #a5b4fc; line-height: 1.6; }
.auth-form-side { padding: 48px; display: flex; flex-direction: column; justify-content: center; }
.auth-form-side h3 { font-size: 1.5rem; margin-bottom: 6px; }
.auth-form-side p.subtitle { font-size: 13px; color: var(--gray-500); margin-bottom: 24px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--gray-700); margin-bottom: 6px; }
.form-control { width: 100%; background-color: var(--gray-50); border: 1px solid var(--gray-100); border-radius: var(--radius-md); padding: 12px 16px; font-size: 14px; transition: var(--transition); }
.form-control:focus { border-color: var(--primary); background-color: var(--white); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
.alert-danger { background-color: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; padding: 12px; border-radius: var(--radius-md); font-size: 13px; font-weight: 500; margin-bottom: 16px; }
.auth-toggle { text-align: center; font-size: 13px; margin-top: 24px; color: var(--gray-500); }
.auth-toggle button { background: none; border: none; color: var(--primary); font-weight: 600; cursor: pointer; }
.auth-toggle button:hover { text-decoration: underline; }
@keyframes scaleIn { from { opacity: 0; transform: scale(0.92); } to { opacity: 1; transform: scale(1); } }
@media (max-width: 768px) { .nav-links { display: none; } .auth-card { grid-template-columns: 1fr; } .auth-decor { display: none; } }

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
  color: var(--gray-500);
  line-height: 0;
}
.password-toggle-btn:hover {
  color: var(--primary);
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
        <li><a href="contact.php">Contact Us</a></li>
      </ul>

      <div class="nav-actions">
        <a href="register.php" class="btn-primary">Register Now</a>
      </div>
    </div>
  </header>

  <!-- Login Card Container -->
  <div class="auth-container">
    <div class="auth-card">
      
      <!-- Left Side decoration with image -->
      <div class="auth-decor" style="background-image: linear-gradient(180deg, rgba(15,23,42,0.55), rgba(30,27,75,0.75) 60%, rgba(238,70,154,0.55)), url('/e-learning-hub/src/assets/images/login.jpg');background-size: cover; background-position: center;">
        <div>
          <span style="font-family: var(--font-mono); font-size: 10px; border-bottom: 2px solid #6366f1; padding-bottom: 4px; letter-spacing: 2px;">SECURE LOG IN</span>
        </div>
        <div style="margin-top: 100px;">
          <h2>Academic Single-Sign-On</h2>
          <p>Step inside your student portal to instantly access your customized course files, responsive sandbox layouts, and certified lecture tracks.</p>
        </div>
        <div style="font-size: 10px; color: #a5b4fc; font-family: var(--font-mono); margin-top: 50px;">
          XAMPP SECURITY VERIFIED
        </div>
      </div>

      <!-- Right Side form -->
      <div class="auth-form-side">
        <h3>Welcome Back</h3>
        <p class="subtitle">Sign in to resume study progress.</p>

        <?php if (!empty($error)): ?>
          <div class="alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
          <div class="form-group">
            <label>Username or Email</label>
            <input type="text" name="username" class="form-control" placeholder="e.g. StudentDharani or you@domain.com" required>
          </div>

          <div class="form-group">
            <label>Password</label>
            <div class="password-input-wrapper">
              <input type="password" name="password" id="login-password-field" class="form-control" placeholder="•••••••• (same password used at registration)" autocomplete="current-password" required>
              <button type="button" class="password-toggle-btn" data-target="login-password-field" aria-label="Show password">
                <svg class="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                <svg class="eye-off-icon" viewBox="0 0 24 24"><path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a18.5 18.5 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 7 11 7a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
              </button>
            </div>
          </div>

          <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px 0; margin-top: 10px;">
            Authorize Portal Entry
          </button>
        </form>

        <div class="auth-toggle">
          New student? <button onclick="window.location.href='register.php'">Create student account here</button>
        </div>
      </div>

    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
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
    });
  </script>

</body>
</html>