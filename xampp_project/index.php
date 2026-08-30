<?php
// Include database connection helper
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-Learning Hub Academy | Home</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Extra styles for new Home page sections */

    .steps-section { padding: 48px 0; }
    .steps-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 24px;
      margin-top: 28px;
    }
    .step-card {
      position: relative;
      background: var(--white);
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 24px 22px;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }
    .step-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: var(--primary); }
    .step-number {
      display: inline-flex; align-items: center; justify-content: center;
      width: 38px; height: 38px; border-radius: 50%;
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
      color: white; font-weight: 700; font-size: 15px; margin-bottom: 12px;
    }
    .step-card h3 { font-size: 1rem; margin-bottom: 6px; }
    .step-card p { font-size: 13px; color: var(--gray-500); line-height: 1.5; }

    .testimonials-section { background-color: var(--gray-50); padding: 48px 0; }
    .testimonials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 20px;
      margin-top: 28px;
    }
    .testimonial-card {
      background: var(--white);
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 22px;
      box-shadow: var(--shadow-sm);
    }
    .testimonial-stars { color: #f59e0b; font-size: 14px; margin-bottom: 10px; }
    .testimonial-text { font-size: 13px; color: var(--gray-600); line-height: 1.6; margin-bottom: 16px; }
    .testimonial-profile { display: flex; align-items: center; gap: 12px; }
    .testimonial-avatar {
      width: 40px; height: 40px; border-radius: 50%;
      background: linear-gradient(135deg, #6366f1, #ec4899);
      display: flex; align-items: center; justify-content: center;
      color: white; font-weight: 700; font-size: 14px; flex-shrink: 0;
    }
    .testimonial-name { font-size: 13px; font-weight: 700; color: var(--gray-700); }
    .testimonial-role { font-size: 11px; color: var(--gray-500); }

    .faq-section { padding: 48px 0; }
    .faq-list { max-width: 800px; margin: 28px auto 0; display: flex; flex-direction: column; gap: 12px; }
    .faq-item {
      background: var(--white);
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 16px 20px;
      cursor: pointer;
    }
    .faq-item summary { font-size: 14px; font-weight: 600; color: var(--gray-700); list-style: none; display: flex; justify-content: space-between; align-items: center; }
    .faq-item summary::-webkit-details-marker { display: none; }
    .faq-item summary::after { content: "+"; font-size: 18px; color: var(--primary); }
    .faq-item[open] summary::after { content: "−"; }
    .faq-item p { font-size: 13px; color: var(--gray-500); margin-top: 10px; line-height: 1.5; }

    /* Tighten existing sections for overall page (was too much gap before) */
    .hero-section { padding-top: 40px; padding-bottom: 40px; }
    .features-section { padding-top: 48px; padding-bottom: 48px; }
    .split-layout { padding-top: 48px; padding-bottom: 48px; }

    /* Hero-right panel - image with gradient overlay + caption text */
    .hero-image-card {
      position: relative;
      border-radius: var(--radius-md);
      overflow: hidden;
      min-height: 340px;
      display: flex;
      align-items: flex-end;
      background: linear-gradient(135deg, #6366f1, #8b5cf6);
    }
    .hero-image-card img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      z-index: 0;
    }
    .hero-image-card::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(0deg, rgba(76,29,149,0.85) 0%, rgba(99,102,241,0.35) 60%, rgba(99,102,241,0.15) 100%);
      z-index: 1;
    }
    .hero-image-caption {
      position: relative;
      z-index: 2;
      padding: 22px 24px;
    }
    .hero-image-caption h4 { color: #fff; font-size: 18px; font-weight: 700; margin-bottom: 6px; }
    .hero-image-caption p { color: #e0e7ff; font-size: 13px; margin: 0; line-height: 1.5; }
  </style>
</head>
<body>

  <!-- Header Navigation -->
  <header>
    <div class="max-width-container nav-wrapper">
      <a href="index.php" class="logo-wrapper">
        <div class="logo-icon">
          <!-- Graduation Cap Icon -->
          <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM3.82 9L12 4.54 20.18 9 12 13.46 3.82 9zM12 15.56l-6-3.27v3.25L12 19l6-3.46v-3.25l-6 3.27z"/></svg>
        </div>
        <div class="logo-text">
          <h2>E-Learning Hub</h2>
          <p>Academic Portal</p>
        </div>
      </a>

      <ul class="nav-links">
        <li><a href="index.php" class="active">Home</a></li>
        <li><a href="courses.php">Courses</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact Us</a></li>
      </ul>

      <div class="nav-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="courses.php" class="btn-primary">My Dashboard</a>
          <a href="logout.php" class="btn-secondary" style="color: #ef4444;">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        <?php else: ?>
          <a href="login.php" class="btn-secondary">Sign In</a>
          <a href="register.php" class="btn-primary">Register Now</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="max-width-container hero-section">
    <div class="hero-left">
      <span class="hero-tag">
        ✨ Local XAMPP Server Active
      </span>
      <h1>Master Modern Technology <span>Step-by-Step</span></h1>
      <p>Access over 15+ university-tier curriculum courses including HTML, CSS, JavaScript, PHP, Python, and SQL. Interactive playbacks, direct sandbox playgrounds, and verified credentials await.</p>

      <div class="hero-buttons">
        <a href="courses.php" class="btn-primary">Explore 15+ Courses</a>
        <a href="about.php" class="btn-secondary" style="border: 1px solid #cbd5e1; border-radius: 12px;">Learn More</a>
      </div>
    </div>

    <div class="hero-right">
      <div class="hero-image-card">
          <img src="/e-learning-hub/src/assets/images/home.jpg" alt="Students learning online">
        <div class="hero-image-caption">
          <h4>Learn. Build. Get Certified.</h4>
          <p>Join 25,000+ learners mastering real-world tech skills.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Statistics Counter Strip -->
  <section class="stats-strip">
    <div class="max-width-container stats-grid">
      <div class="stat-item">
        <h2>15+</h2>
        <p>Syllabi Pathways</p>
      </div>
      <div class="stat-item">
        <h2>25k+</h2>
        <p>Global Learners</p>
      </div>
      <div class="stat-item">
        <h2>180+</h2>
        <p>Study Hours</p>
      </div>
      <div class="stat-item">
        <h2>4.9★</h2>
        <p>Course Rating</p>
      </div>
    </div>
  </section>

  <!-- Feature Grid -->
  <section class="max-width-container features-section">
    <div class="section-header">
      <span>Engineered For Students</span>
      <h2>Dynamic Learning Features Designed For Excellence</h2>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">
          <!-- Code Icon -->
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="height:24px;width:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        </div>
        <h3>Interactive Code Labs</h3>
        <p>Practice programming skills directly in the browser. Edit sample HTML/CSS codes and run them instantly with zero setup time.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon" style="color: #10b981;">
          <!-- Video Icon -->
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="height:24px;width:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        </div>
        <h3>Structured Video Classes</h3>
        <p>Access step-by-step video lessons detailing backend, frontend, design paradigms, and security alignments.</p>
      </div>

      <div class="feature-card">
        <div class="feature-icon" style="color: #8b5cf6;">
          <!-- Award Icon -->
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="height:24px;width:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        </div>
        <h3>Accredited Certifications</h3>
        <p>Verify your progress! Earn credentials for completing course requirements, perfect to boost your local job profiles.</p>
      </div>
    </div>
  </section>

  <!-- NEW: How It Works Section -->
  <section class="max-width-container steps-section">
    <div class="section-header">
      <span>Simple Onboarding</span>
      <h2>Get Started in Just 3 Easy Steps</h2>
    </div>

    <div class="steps-grid">
      <div class="step-card">
        <div class="step-number">1</div>
        <h3>Create Your Account</h3>
        <p>Sign up in seconds with your email and choose a secure password to unlock the student portal.</p>
      </div>
      <div class="step-card">
        <div class="step-number">2</div>
        <h3>Pick Your Courses</h3>
        <p>Browse 15+ curriculum pathways and select the programs that match your learning goals.</p>
      </div>
      <div class="step-card">
        <div class="step-number">3</div>
        <h3>Start Learning Instantly</h3>
        <p>Jump into the Course Player with embedded lectures, live sandboxes, and track your progress.</p>
      </div>
    </div>
  </section>

  <!-- NEW: Testimonials Section -->
  <section class="testimonials-section">
    <div class="max-width-container">
      <div class="section-header">
        <span>Student Voices</span>
        <h2>What Our Learners Are Saying</h2>
      </div>

      <div class="testimonials-grid">
        <div class="testimonial-card">
          <div class="testimonial-stars">★★★★★</div>
          <p class="testimonial-text">The course sandbox made learning JavaScript so much easier — I could test code instantly without installing anything.</p>
          <div class="testimonial-profile">
            <div class="testimonial-avatar">D</div>
            <div>
              <div class="testimonial-name">Dharani S.</div>
              <div class="testimonial-role">Web Development Student</div>
            </div>
          </div>
        </div>

        <div class="testimonial-card">
          <div class="testimonial-stars">★★★★★</div>
          <p class="testimonial-text">Clean interface, structured video lessons, and a dashboard that actually tracks what I've registered for. Highly recommend.</p>
          <div class="testimonial-profile">
            <div class="testimonial-avatar">A</div>
            <div>
              <div class="testimonial-name">Arun K.</div>
              <div class="testimonial-role">Software Engineering Student</div>
            </div>
          </div>
        </div>

        <div class="testimonial-card">
          <div class="testimonial-stars">★★★★☆</div>
          <p class="testimonial-text">Great catalog of database and design courses. The certificate at the end gave my resume a nice boost.</p>
          <div class="testimonial-profile">
            <div class="testimonial-avatar">P</div>
            <div>
              <div class="testimonial-name">Priya M.</div>
              <div class="testimonial-role">Database Student</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Mission splits -->
  <section style="background-color: var(--gray-50); border-t: 1px solid var(--gray-100);">
    <div class="max-width-container split-layout">
      <div class="split-right">
        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=600&q=80" alt="Academic studies">
      </div>
      <div class="split-left" style="display:flex; flex-direction:column; gap:16px;">
        <span style="font-size:12px; font-family:var(--font-mono); color:var(--primary); font-weight:700;">OUR MISSION</span>
        <h2>Learn Without Limits, Connect Directly with Industry Syllabi</h2>
        <p>We believe in providing clean, practical educational materials. That is why our curriculum focuses heavily on hands-on experiences, code practices, and verified syllabus modules.</p>
        <a href="about.php" class="btn-primary" style="width:fit-content;">Read Our Story</a>
      </div>
    </div>
  </section>

  <!-- NEW: FAQ Section -->
  <section class="max-width-container faq-section">
    <div class="section-header">
      <span>Need Help?</span>
      <h2>Frequently Asked Questions</h2>
    </div>

    <div class="faq-list">
      <details class="faq-item">
        <summary>Is registration free?</summary>
        <p>Yes, creating a student account and browsing the catalog is completely free on this local demo portal.</p>
      </details>
      <details class="faq-item">
        <summary>How many courses do I need to register for?</summary>
        <p>New students must select exactly 5 courses at sign-up to activate their dashboard and start learning.</p>
      </details>
      <details class="faq-item">
        <summary>Can I access courses after registering?</summary>
        <p>Absolutely — once registered, your enrolled courses appear on your personal dashboard with a Resume Player button.</p>
      </details>
      <details class="faq-item">
        <summary>Do I get a certificate after completion?</summary>
        <p>Yes, accredited certifications are issued once you complete all requirements for a given course pathway.</p>
      </details>
    </div>
  </section>

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
          <li><a href="about.php">Our Faculty Story</a></li>
          <li><a href="contact.php">Technical Contact Support</a></li>
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