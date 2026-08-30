<?php
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About E-Learning Hub Academy | Faculty & Standards</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .faculty-section {
      padding: 60px 0 100px;
    }
    .faculty-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 32px;
      margin-top: 40px;
    }
    .faculty-card {
      background: white;
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-lg);
      padding: 32px;
      text-align: center;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }
    .faculty-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-2px);
    }
    .faculty-avatar {
      height: 80px;
      width: 80px;
      border-radius: 50%;
      background-color: var(--primary-light);
      color: var(--primary);
      font-size: 1.5rem;
      font-weight: 800;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
    }
    .faculty-card h3 { font-size: 1.1rem; margin-bottom: 4px; }
    .faculty-card span { font-size: 11px; font-family: var(--font-mono); color: var(--gray-400); text-transform: uppercase; }
    .faculty-card p { font-size: 13px; color: var(--gray-600); margin-top: 16px; line-height: 1.6; }

    /* --- New: Achievement stats strip --- */
    .about-stats-strip {
      background: var(--dark);
      background-image: radial-gradient(circle at 20% 20%, rgba(67, 56, 202, 0.35), transparent 45%),
                         radial-gradient(circle at 85% 80%, rgba(14, 165, 233, 0.25), transparent 40%);
      padding: 56px 0;
    }
    .about-stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      text-align: center;
    }
    .about-stat-item h2 {
      color: white;
      font-size: 2.4rem;
      font-weight: 800;
      margin-bottom: 6px;
    }
    .about-stat-item p {
      font-size: 11px;
      font-family: var(--font-mono);
      color: #a5b4fc;
      text-transform: uppercase;
      letter-spacing: 2px;
    }

    /* --- New: Our Journey timeline --- */
    .timeline-section {
      padding: 100px 0;
    }
    .timeline-wrapper {
      position: relative;
      max-width: 800px;
      margin: 50px auto 0;
    }
    .timeline-wrapper::before {
      content: '';
      position: absolute;
      left: 20px;
      top: 6px;
      bottom: 6px;
      width: 2px;
      background: linear-gradient(180deg, var(--primary), var(--secondary));
    }
    .timeline-item {
      position: relative;
      padding-left: 56px;
      margin-bottom: 40px;
    }
    .timeline-item:last-child { margin-bottom: 0; }
    .timeline-dot {
      position: absolute;
      left: 12px;
      top: 4px;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background: var(--accent-gradient);
      border: 3px solid var(--white);
      box-shadow: 0 0 0 2px var(--primary-light);
    }
    .timeline-item span.timeline-year {
      font-size: 11px;
      font-family: var(--font-mono);
      font-weight: 700;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .timeline-item h4 {
      font-size: 1.05rem;
      margin: 4px 0 6px;
    }
    .timeline-item p {
      font-size: 13px;
      color: var(--gray-600);
      line-height: 1.6;
    }

    /* --- New: Core values cards --- */
    .values-section {
      background-color: var(--gray-50);
      padding: 100px 0;
    }
    .values-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      margin-top: 40px;
    }
    .value-card {
      background: white;
      border: 1px solid var(--gray-100);
      border-radius: var(--radius-md);
      padding: 26px 22px;
      text-align: center;
      box-shadow: var(--shadow-sm);
      transition: var(--transition);
    }
    .value-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-md);
      border-color: var(--primary-light);
    }
    .value-card .value-icon {
      width: 46px;
      height: 46px;
      border-radius: 12px;
      background: var(--primary-light);
      color: var(--primary);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 14px;
    }
    .value-card h4 { font-size: 0.95rem; margin-bottom: 8px; }
    .value-card p { font-size: 12px; color: var(--gray-500); line-height: 1.6; }
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
        <li><a href="about.php" class="active">About</a></li>
        <li><a href="contact.php">Contact Us</a></li>
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

  <!-- Split introduction layout -->
  <div class="max-width-container split-layout" style="padding-top: 60px;">
    <div class="split-left">
      <span style="font-size: 12px; font-family: var(--font-mono); color: var(--primary); font-weight: 700; text-transform: uppercase; letter-spacing: 2px;">ABOUT THE PORTAL</span>
      <h1 style="margin-top: 8px;">Accredited Digital Pedagogical Platform</h1>
      <p>Founded by a collective of computer science professors and industry practitioners, E-Learning Hub delivers comprehensive curricula tailored for self-motivated learning. Our programs encompass key computer technologies from fundamental HTML structures to complex data analysis, machine models, and backend paradigms.</p>
      
      <div style="display:flex; flex-direction:column; gap:16px; margin-top:30px;">
        <div style="display:flex; gap:12px;">
          <div style="background-color: var(--primary-light); color: var(--primary); padding:8px; border-radius:8px; height:fit-content; display:flex;">
            <svg fill="currentColor" viewBox="0 0 24 24" style="height:18px; width:18px;"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
          </div>
          <div>
            <h4 style="font-size:14px; margin-bottom:2px;">Industry-Vetted Curriculum</h4>
            <p style="font-size:12px; color: var(--gray-500); margin:0;">Every syllabus is built in collaboration with enterprise technology architects.</p>
          </div>
        </div>

        <div style="display:flex; gap:12px;">
          <div style="background-color: var(--primary-light); color: var(--primary); padding:8px; border-radius:8px; height:fit-content; display:flex;">
            <svg fill="currentColor" viewBox="0 0 24 24" style="height:18px; width:18px;"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
          </div>
          <div>
            <h4 style="font-size:14px; margin-bottom:2px;">Integrated Sandbox Environments</h4>
            <p style="font-size:12px; color: var(--gray-500); margin:0;">Immediate code verification without setting up secondary IDE systems.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="split-right">
      <img src="/e-learning-hub/src/assets/images/about.jpg">
    </div>
  </div>

  <!-- NEW: Achievement stats strip -->
  <section class="about-stats-strip">
    <div class="max-width-container about-stats-grid">
      <div class="about-stat-item">
        <h2>8+</h2>
        <p>Years Established</p>
      </div>
      <div class="about-stat-item">
        <h2>25k+</h2>
        <p>Students Graduated</p>
      </div>
      <div class="about-stat-item">
        <h2>15+</h2>
        <p>Curriculum Pathways</p>
      </div>
      <div class="about-stat-item">
        <h2>96%</h2>
        <p>Satisfaction Rate</p>
      </div>
    </div>
  </section>

  <!-- NEW: Our Journey timeline -->
  <section class="max-width-container timeline-section">
    <div class="section-header" style="text-align: center;">
      <span>OUR JOURNEY</span>
      <h2>Milestones That Shaped the Academy</h2>
    </div>

    <div class="timeline-wrapper">
      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <span class="timeline-year">2018</span>
        <h4>Academy Founded</h4>
        <p>Started as a small collective of professors offering free HTML/CSS workshops to local students.</p>
      </div>

      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <span class="timeline-year">2020</span>
        <h4>Launched Interactive Code Labs</h4>
        <p>Introduced in-browser sandbox environments, removing the need for local IDE setup for beginners.</p>
      </div>

      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <span class="timeline-year">2022</span>
        <h4>Crossed 10,000 Learners</h4>
        <p>Expanded the catalog to 10+ programs spanning web development, databases, and software engineering.</p>
      </div>

      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <span class="timeline-year">2024</span>
        <h4>Industry Accreditation</h4>
        <p>Partnered with enterprise technology architects to validate and modernize every syllabus offered.</p>
      </div>

      <div class="timeline-item">
        <div class="timeline-dot"></div>
        <span class="timeline-year">2026</span>
        <h4>15+ Programs, 25k+ Graduates</h4>
        <p>Today the academy runs a full student portal with live dashboards, sandboxes, and certification tracking.</p>
      </div>
    </div>
  </section>

  <!-- NEW: Core values section -->
  <section class="values-section">
    <div class="max-width-container">
      <div class="section-header" style="text-align: center;">
        <span>WHAT DRIVES US</span>
        <h2>Our Core Values</h2>
      </div>

      <div class="values-grid">
        <div class="value-card">
          <div class="value-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="height:22px;width:22px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s4.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          </div>
          <h4>Practical Learning</h4>
          <p>Every lesson connects directly to real-world coding scenarios, not just theory.</p>
        </div>

        <div class="value-card">
          <div class="value-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="height:22px;width:22px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a4 4 0 100-8 4 4 0 000 8zM9.5 20.5l1-4M14.5 20.5l-1-4M4 12H2m20 0h-2M6.34 6.34L4.93 4.93m14.14 1.41l1.41-1.41"/></svg>
          </div>
          <h4>Transparency</h4>
          <p>Clear syllabi, honest progress tracking, and no hidden requirements for certification.</p>
        </div>

        <div class="value-card">
          <div class="value-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="height:22px;width:22px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6 0a4 4 0 10-4-4"/></svg>
          </div>
          <h4>Community First</h4>
          <p>A collaborative learning environment where students and faculty grow together.</p>
        </div>

        <div class="value-card">
          <div class="value-icon">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="height:22px;width:22px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          </div>
          <h4>Continuous Growth</h4>
          <p>Curriculum reviewed every semester to reflect the latest industry standards and tools.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Faculty Panel -->
  <section class="max-width-container faculty-section" style="border-top: 1px solid var(--gray-100); padding-top:60px;">
    <div class="section-header" style="text-align: center; margin-bottom:40px;">
      <span>PEDAGOGICAL LEADERSHIP</span>
      <h2>Our Esteemed Instructors</h2>
    </div>

    <div class="faculty-grid">
      <div class="faculty-card">
        <div class="faculty-avatar">DG</div>
        <h3>Dr. Govindharajan</h3>
        <span>Professor in Web Architecture</span>
        <p>With over 18 years in full-stack engineering research, specializing in highly scalable PHP and relational data pipelines.</p>
      </div>

      <div class="faculty-card">
        <div class="faculty-avatar" style="background-color: #e6fffa; color: #319795;">SJ</div>
        <h3>Sara Johnson</h3>
        <span>Lead Instructor of JS Foundation</span>
        <p>An advocate for functional ES6, stateful client frameworks, and responsive mobile-first visual patterns.</p>
      </div>

      <div class="faculty-card">
        <div class="faculty-avatar" style="background-color: #faf5ff; color: #805ad5;">VS</div>
        <h3>Dr. Victor Stone</h3>
        <span>Researcher in Artificial Intelligence</span>
        <p>Specialized in neural networks, regression classifications, and robust data-focused automation platforms.</p>
      </div>
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