export interface Course {
  id: string;
  title: string;
  category: string;
  level: 'Beginner' | 'Intermediate' | 'Advanced';
  duration: string;
  instructor: string;
  rating: number;
  studentsEnrolled: number;
  description: string;
  image: string;
  videoUrl: string;
  explanation: {
    overview: string;
    objectives: string[];
    syllabus: { week: string; topic: string; details: string }[];
    codeExample?: string;
  };
}

export const COURSES: Course[] = [
  {
    id: "html-css",
    title: "HTML5 & CSS3 Masterclass",
    category: "Web Development",
    level: "Beginner",
    duration: "6 Weeks",
    instructor: "Dr. Govindharajan",
    rating: 4.8,
    studentsEnrolled: 1250,
    description: "Learn the absolute foundations of web development. Build responsive, pixel-perfect layouts using HTML5, modern CSS3 Flexbox, Grid, and beautiful transitions.",
    image: "https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/Dp3qSIn74-Y",
    explanation: {
      overview: "This course is designed for absolute beginners who want to step into the world of web engineering. You will learn the semantic structures of HTML5, how to style websites beautifully using modern CSS3, and how to create mobile-first responsive interfaces.",
      objectives: [
        "Master HTML5 semantic elements and document layouts.",
        "Understand CSS Box Model, Flexbox, Grid, and Custom Properties.",
        "Implement fluid responsive design using Media Queries.",
        "Build a multi-page portfolio website from scratch."
      ],
      syllabus: [
        { week: "Week 1", topic: "HTML5 Semantic Tags & Markup", details: "Learn elements like <header>, <section>, <article>, and forms with native validation." },
        { week: "Week 2", topic: "CSS Selectors, Box Model & Typography", details: "Dive deep into padding, margins, borders, typography styling, and absolute vs relative units." },
        { week: "Week 3", topic: "CSS Layouts: Flexbox vs Grid", details: "Unleash the full potential of Flexbox and CSS Grid to align items easily." },
        { week: "Week 4", topic: "Animations & Modern Page Layouts", details: "Create hover micro-interactions, transition animations, and multi-column designs." }
      ],
      codeExample: `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cool Layout</title>
  <style>
    .grid-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      padding: 20px;
    }
    .card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      padding: 15px;
      transition: transform 0.2s;
    }
    .card:hover { transform: translateY(-5px); }
  </style>
</head>
<body>
  <main class="grid-container">
    <div class="card"><h3>Feature One</h3><p>Semantic HTML & CSS Layouts</p></div>
  </main>
</body>
</html>`
    }
  },
  {
    id: "javascript",
    title: "Modern JavaScript (ES6+)",
    category: "Web Development",
    level: "Beginner",
    duration: "8 Weeks",
    instructor: "Sara Johnson",
    rating: 4.9,
    studentsEnrolled: 1850,
    description: "Go from scratch to mastering JavaScript. Understand variables, DOM manipulation, asynchronous programming (Promises & Async/Await), and fetch web APIs.",
    image: "https://images.unsplash.com/photo-1579468118864-1b9ea3c0db4a?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/W6NZfCO5SIk",
    explanation: {
      overview: "JavaScript is the engine of the modern web. In this comprehensive course, you will grasp core computer science structures in JS and shift into web interactivity, event-driven development, and modular architecture.",
      objectives: [
        "Develop algorithmic thinking using conditions, loops, and objects.",
        "Manipulate the Document Object Model (DOM) to build interactive components.",
        "Implement asynchronous patterns with Promises and Async/Await.",
        "Manage application state client-side safely."
      ],
      syllabus: [
        { week: "Week 1", topic: "JS Variables, Data Types & Scopes", details: "Learn let, const, block scopes, dynamic typing, and operations." },
        { week: "Week 2", topic: "Arrays, Objects & ES6+ Methods", details: "Explore .map(), .filter(), destructuring, spread operators, and arrow functions." },
        { week: "Week 3", topic: "DOM Manipulation & Event Loop", details: "Select elements, add event listeners, create dynamic lists, and handle event bubbling." },
        { week: "Week 4", topic: "Asynchronous JS & Fetch API", details: "Make calls to mock REST endpoints, handle response payloads, and catch runtime errors." }
      ],
      codeExample: `// Modern ES6+ JavaScript Example
const fetchUserData = async (userId) => {
  try {
    const response = await fetch(\`https://api.example.com/users/\${userId}\`);
    if (!response.ok) throw new Error("Network status not ok");
    const { id, username, skills } = await response.json();
    
    // Using Map and Template Literals
    const skillList = skills.map(skill => \`<li>\${skill}</li>\`).join('');
    document.getElementById('user-profile').innerHTML = \`
      <div class="profile-card">
        <h4>User: \${username} (#\${id})</h4>
        <ul>\${skillList}</ul>
      </div>
    \`;
  } catch (error) {
    console.error("Failed fetching user:", error);
  }
};`
    }
  },
  {
    id: "responsive-design",
    title: "Responsive Web Design Essentials",
    category: "Design",
    level: "Intermediate",
    duration: "4 Weeks",
    instructor: "David Miller",
    rating: 4.7,
    studentsEnrolled: 980,
    description: "Design websites that look incredible on everything from a massive desktop screen down to mobile displays. Learn advanced fluid design concepts.",
    image: "https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/srvUrASo084",
    explanation: {
      overview: "Responsive design is no longer an option—it is a critical requirement. This course teaches fluid grid patterns, typography resizing, adaptive image serving, and cross-browser responsiveness techniques.",
      objectives: [
        "Adopt a strict mobile-first design strategy.",
        "Apply fluid typography rules using clamp(), vh, vw, and rem.",
        "Deploy CSS Grid container autofills and custom breakpoints.",
        "Optimize vector and raster media for fast page loads."
      ],
      syllabus: [
        { week: "Week 1", topic: "Mobile-First Philosophy & Breakpoints", details: "Write styles that target mobile first, scaling up to standard laptop widths." },
        { week: "Week 2", topic: "Advanced Fluid Layout Engines", details: "Understand CSS min(), max(), clamp(), and calc() for scalable layouts." },
        { week: "Week 3", topic: "Responsive Typography & Multi-Resolution Media", details: "Handle scalable headlines, picture tag setups, and SVG integrations." }
      ],
      codeExample: `.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem;
}

/* Fluid responsive typography */
h1 {
  font-size: clamp(2rem, 4vw + 1rem, 4.5rem);
  line-height: 1.1;
}

/* Container query simulation */
@container card (min-width: 400px) {
  .card-content {
    display: flex;
    gap: 1rem;
  }
}`
    }
  },
  {
    id: "php-beginners",
    title: "PHP Backend Development for Beginners",
    category: "Web Development",
    level: "Beginner",
    duration: "8 Weeks",
    instructor: "Prof. Govindharajan",
    rating: 4.8,
    studentsEnrolled: 1540,
    description: "Unlock server-side scripting! Understand PHP fundamentals, request routing, session management, security checks, and form processing.",
    image: "https://images.unsplash.com/photo-1599507593499-a3f7f7d9a22a?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/2eebptXfEvw",
    explanation: {
      overview: "PHP powers over 75% of the web. This beginner-friendly course teaches backend variables, condition paths, superglobals, stateful user sessions, password hashing, and dynamic server-side page generation.",
      objectives: [
        "Configure PHP environments and execute basic server-side scripts.",
        "Securely process forms with GET, POST, and filter inputs.",
        "Establish secure user login sessions with PHPSESSID and CSRF tokens.",
        "Handle file uploads and process remote API data payloads."
      ],
      syllabus: [
        { week: "Week 1", topic: "PHP Basics, Syntax & Server Flow", details: "How files execute on the server. Echo, variables, types, loops, and conditions." },
        { week: "Week 2", topic: "POST, GET, and Form Sanitation", details: "Securely reading input values from users. Preventing cross-site scripting (XSS)." },
        { week: "Week 3", topic: "User Sessions & State Control", details: "Handling user logged-in state using session_start() and managing cookies safely." },
        { week: "Week 4", topic: "Object-Oriented PHP Basics", details: "Structuring modular backend logic using classes, methods, and namespaces." }
      ],
      codeExample: `<?php
// Secure Form Handling in PHP
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input values
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    
    if ($username && $email) {
        // Save session state
        $_SESSION['user_id'] = 42;
        $_SESSION['username'] = $username;
        
        // Redirect to dashboard page
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid inputs entered!";
    }
}
?>`
    }
  },
  {
    id: "mysql-db",
    title: "MySQL Relational Databases & SQL",
    category: "Database",
    level: "Beginner",
    duration: "6 Weeks",
    instructor: "Alan Parker",
    rating: 4.7,
    studentsEnrolled: 1110,
    description: "Design relational database schemas. Master SQL commands, write JOINs, index columns for performance, and run complex secure database queries.",
    image: "https://images.unsplash.com/photo-1544383835-bda2bc66a55d?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/HXV3zeQKqGY",
    explanation: {
      overview: "Any robust web application needs a solid database. This course takes you into SQL (Structured Query Language), database normalizations, primary/foreign keys, joins, index optimizations, and prepared queries.",
      objectives: [
        "Design schema blueprints with logical normalizations (1NF, 2NF, 3NF).",
        "Perform secure inserts, updates, deletes, and complex reads.",
        "Utilize inner, left, and outer joins to read across multiple tables.",
        "Write prepared statements to prevent SQL Injection attacks."
      ],
      syllabus: [
        { week: "Week 1", topic: "Relational Concepts & CREATE TABLE", details: "Defining columns, data types (VARCHAR, INT, TEXT, TIMESTAMP), and primary keys." },
        { week: "Week 2", topic: "SQL CRUD Operations & Filters", details: "Using SELECT, INSERT, UPDATE, DELETE with WHERE, ORDER BY, and LIMIT clauses." },
        { week: "Week 3", topic: "Relational JOINs & Relationships", details: "Connecting tables using Foreign Keys. Understanding One-to-Many and Many-to-Many configurations." },
        { week: "Week 4", topic: "Aggregations, Grouping & Optimization", details: "Running SUM, AVG, COUNT, and using GROUP BY, HAVING, and INDEX tables." }
      ],
      codeExample: `-- Secure SQL Schema and Prepared Query Pattern
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Selecting user and their course registration history
SELECT u.username, c.title, r.registration_date 
FROM registrations r
INNER JOIN users u ON r.user_id = u.id
INNER JOIN courses c ON r.course_id = c.id
WHERE u.id = ?;`
    }
  },
  {
    id: "react-essentials",
    title: "React.js Component Architecture",
    category: "Web Development",
    level: "Intermediate",
    duration: "8 Weeks",
    instructor: "Emily Chen",
    rating: 4.9,
    studentsEnrolled: 2200,
    description: "Build modern dynamic user interfaces. Learn state managers, hooks (useState, useEffect, useMemo), routing, custom hooks, and context APIs.",
    image: "https://images.unsplash.com/photo-1633356122544-f134324a6cee?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/Ke90Tje7VS0",
    explanation: {
      overview: "React is the standard library for modern single-page applications. This course takes you on a complete hands-on path from virtual DOM rendering up to complex custom hooks, global context engines, and responsive micro-animations.",
      objectives: [
        "Master declarative component writing and props workflows.",
        "Synthesize reactivity with useState, useEffect, and useRef.",
        "Build performant elements by avoiding redundant re-renders.",
        "Manage global state cleanly via the React Context API."
      ],
      syllabus: [
        { week: "Week 1", topic: "React JSX & Modular Components", details: "Writing clean React syntax, importing/exporting components, and setting props." },
        { week: "Week 2", topic: "Reactivity: useState & DOM Events", details: "Handling user inputs, maintaining interactive states, and conditional rendering." },
        { week: "Week 3", topic: "Side Effects & fetching (useEffect)", details: "Fetching external REST APIs, subscribing to changes, and cleaning up timers." },
        { week: "Week 4", topic: "Custom Hooks & Shared Contexts", details: "Extracting repetitive stateful actions into beautiful, modular custom hooks." }
      ],
      codeExample: `import React, { useState, useEffect } from 'react';

export function UserStatus({ userId }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let active = true;
    const fetchUser = async () => {
      const res = await fetch(\`/api/users/\${userId}\`);
      const data = await res.json();
      if (active) {
        setUser(data);
        setLoading(false);
      }
    };
    fetchUser();
    return () => { active = false; };
  }, [userId]);

  if (loading) return <div>Loading...</div>;
  return <div>Welcome back, {user?.name}!</div>;
}`
    }
  },
  {
    id: "python-bootcamp",
    title: "Python Programming Bootcamp",
    category: "Software Engineering",
    level: "Beginner",
    duration: "8 Weeks",
    instructor: "Mark Vance",
    rating: 4.9,
    studentsEnrolled: 2100,
    description: "Start your coding journey with Python. Learn loops, logic paths, object-oriented principles, file structures, and data science foundations.",
    image: "https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/_uQrJ0TkZlc",
    explanation: {
      overview: "Python's simple syntax and powerful versatility make it a leading programming language. In this bootcamp, you will go from elementary control logic up to complex list comprehensions, object systems, and file analyzers.",
      objectives: [
        "Write clear, readable, and pythonic code.",
        "Leverage loops, lists, sets, and dictionaries to process large datasets.",
        "Implement file handling and custom exception structures.",
        "Apply Object-Oriented Programming (OOP) classes and inheritances."
      ],
      syllabus: [
        { week: "Week 1", topic: "Variables, Math Operators & Conditions", details: "Core syntax, print outputs, user input strings, and logical if/elif paths." },
        { week: "Week 2", topic: "Loops & Complex List Operations", details: "For loops, while statements, arrays, dicts, tuples, and slicing patterns." },
        { week: "Week 3", topic: "Modular Functions & Scopes", details: "Writing reusable functions with positional, default, and key-value arguments." },
        { week: "Week 4", topic: "Classes, Objects & Standard Libraries", details: "Blueprint classes, self attributes, instantiations, and importing os/sys modules." }
      ],
      codeExample: `# Simple Python File Analyzer
import os

class FileStats:
    def __init__(self, filepath):
        self.filepath = filepath

    def get_line_count(self):
        if not os.path.exists(self.filepath):
            return 0
        with open(self.filepath, 'r') as file:
            lines = file.readlines()
            return len(lines)

# Usage
stats = FileStats("notes.txt")
print(f"Total lines: {stats.get_line_count()}")`
    }
  },
  {
    id: "node-express",
    title: "Scalable Node.js & Express REST APIs",
    category: "Web Development",
    level: "Intermediate",
    duration: "6 Weeks",
    instructor: "Leo Carter",
    rating: 4.8,
    studentsEnrolled: 1420,
    description: "Build robust, rapid backends. Write REST APIs with Node and Express, configure middlewares, handle authentication, and hook up databases.",
    image: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/Oe421EPjeBE",
    explanation: {
      overview: "JavaScript on the server is incredibly powerful. You will build high-concurrency routing engines, handle secure JWT token auth, integrate database calls, and format clean JSON payloads for client apps.",
      objectives: [
        "Construct server routers with dynamic path params and queries.",
        "Configure core middle-tier plugins for logging, CORS, and body parsing.",
        "Enforce access controls using JWT token verifications.",
        "Perform database read/write actions within route closures."
      ],
      syllabus: [
        { week: "Week 1", topic: "Node.js Architecture & Basic HTTP Server", details: "Understanding single-threaded event loops, streams, and system modules." },
        { week: "Week 2", topic: "Express Routers & Request Payloads", details: "Mounting express, writing GET/POST/PUT/DELETE endpoints, and accessing req.body." },
        { week: "Week 3", topic: "Middlewares: Logs, CORS & Security", details: "Creating custom middleware to intercept, log, and filter inbound connections." },
        { week: "Week 4", topic: "Auth Routes & Password Cryptography", details: "Using bcrypt to hash credentials, generating JWTs, and protecting static folders." }
      ],
      codeExample: `// Express server router with custom JWT middleware
const express = require('express');
const jwt = require('jsonwebtoken');
const app = express();

app.use(express.json());

const checkAuth = (req, res, next) => {
  const token = req.headers['authorization']?.split(' ')[1];
  if (!token) return res.status(401).json({ error: 'Auth missing' });
  
  jwt.verify(token, 'SECRET_KEY', (err, decoded) => {
    if (err) return res.status(403).json({ error: 'Invalid token' });
    req.user = decoded;
    next();
  });
};

app.get('/api/protected', checkAuth, (req, res) => {
  res.json({ message: 'Secure data accessed!', user: req.user });
});`
    }
  },
  {
    id: "ui-ux",
    title: "UI/UX Design Principles & Figma",
    category: "Design",
    level: "Beginner",
    duration: "5 Weeks",
    instructor: "Rachel Moore",
    rating: 4.8,
    studentsEnrolled: 1150,
    description: "Design mockups and prototypes. Master typography scales, structural layout spacing, color matching, and building interactive designs in Figma.",
    image: "https://images.unsplash.com/photo-1561070791-26c113006238?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/FTFaQW1ZWY4",
    explanation: {
      overview: "A great app starts with brilliant design. You will study layout rhythms, high-contrast typography, interactive system feedbacks, and wireframe prototypes using Figma.",
      objectives: [
        "Create cohesive visual mood boards and contrast color themes.",
        "Apply hierarchical typography styles to direct reading flows.",
        "Draft functional wireframes and clickable UI prototypes.",
        "Design responsive user interfaces adapted for multiple viewports."
      ],
      syllabus: [
        { week: "Week 1", topic: "Core Design Elements & Color Psychology", details: "Study contrast, geometric visual balances, semantic color rules, and margins." },
        { week: "Week 2", topic: "Grid Systems & Responsive Frameworks", details: "Structuring elements on 4-column, 8-column, and 12-column layouts." },
        { week: "Week 3", topic: "Wireframing to High-Fidelity in Figma", details: "Figma tools, frames, auto-layout, nested modules, and clickable components." }
      ],
      codeExample: `/* UI Design Theme Values (Aesthetic Guide) */
:root {
  --font-display: "Space Grotesk", sans-serif;
  --font-body: "Inter", sans-serif;

  /* Typography scale */
  --text-xs: 0.75rem;   /* 12px for microdata */
  --text-sm: 0.875rem;  /* 14px for body notes */
  --text-base: 1rem;    /* 16px for general body */
  --text-xl: 1.25rem;   /* 20px for subtiles */
  --text-3xl: 1.875rem; /* 30px for major headings */
  
  /* Strict spacing intervals */
  --space-1: 0.25rem;  /* 4px */
  --space-2: 0.5rem;   /* 8px */
  --space-4: 1rem;     /* 16px */
  --space-6: 1.5rem;   /* 24px */
}`
    }
  },
  {
    id: "dsa-core",
    title: "Data Structures & Algorithms in JS",
    category: "Software Engineering",
    level: "Advanced",
    duration: "10 Weeks",
    instructor: "Dr. Alice Robert",
    rating: 4.9,
    studentsEnrolled: 890,
    description: "Ace your coding interviews! Understand Arrays, Linked Lists, Stacks, Queues, Hash Tables, Trees, Graphs, Sorting algorithms, and Big-O notation.",
    image: "https://images.unsplash.com/photo-1605379399642-870262d3d051?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/8hly31xKli0",
    explanation: {
      overview: "Data structures and algorithms are the bedrock of computer science. This advanced course teaches memory management, runtime efficiency evaluation (Big O), tree transversals, graphs, and search/sort paths.",
      objectives: [
        "Calculate runtime complexities (Big O) of custom algorithms.",
        "Implement core structures (Singly/Doubly Linked Lists, BSTs, Stacks) manually.",
        "Perform graph searches using Breadth-First and Depth-First transversals.",
        "Write highly optimal solutions for algorithmic challenges."
      ],
      syllabus: [
        { week: "Week 1", topic: "Big O Notation & Space-Time Tradeoffs", details: "O(1), O(log n), O(n), O(n^2) evaluations with direct code examples." },
        { week: "Week 2", topic: "Linear Data Structures: Lists & Stacks", details: "Pointer-based Linked Lists vs dynamic contiguous memory Arrays." },
        { week: "Week 3", topic: "Trees: Binary Search Trees (BST)", details: "Inserting node items, removing parent structures, and in-order/pre-order traversals." },
        { week: "Week 4", topic: "Searching, Sorting & Graph Solvers", details: "Merge Sort, Quick Sort, Graphs representation, and BFS/DFS algorithms." }
      ],
      codeExample: `// Binary Search Tree (BST) Node & Insert
class TreeNode {
  constructor(value) {
    this.value = value;
    this.left = null;
    this.right = null;
  }
}

class BinarySearchTree {
  constructor() {
    this.root = null;
  }
  
  insert(val) {
    const newNode = new TreeNode(val);
    if (!this.root) { this.root = newNode; return this; }
    let current = this.root;
    while (true) {
      if (val === current.value) return undefined;
      if (val < current.value) {
        if (!current.left) { current.left = newNode; return this; }
        current = current.left;
      } else {
        if (!current.right) { current.right = newNode; return this; }
        current = current.right;
      }
    }
  }
}`
    }
  },
  {
    id: "cyber-sec",
    title: "Cyber Security & Secure Web Coding",
    category: "Software Engineering",
    level: "Intermediate",
    duration: "6 Weeks",
    instructor: "Nikhil Raj",
    rating: 4.8,
    studentsEnrolled: 1040,
    description: "Learn how to defend web apps from hackers. Prevent SQL injection, XSS, CSRF attacks, configure secure HTTPS headers, and manage sessions safely.",
    image: "https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/6scgP6-m684",
    explanation: {
      overview: "Writing code that works is not enough; your code must be resilient against exploits. In this course, you will study security structures, session hijacking mitigations, cryptographic salting, and secure communication.",
      objectives: [
        "Audit and eliminate SQL Injection vulnerabilities completely.",
        "Deploy Content Security Policies (CSP) to stop XSS scripts.",
        "Incorporate anti-CSRF token verification on state-changing requests.",
        "Implement robust cryptographic standard hashing for all passwords."
      ],
      syllabus: [
        { week: "Week 1", topic: "OWASP Top 10 Web Security Risks", details: "An overview of the most critical vulnerabilities in modern web layers." },
        { week: "Week 2", topic: "SQL Injection & Parameterized Database Queries", details: "How unescaped queries lead to full data leaks, and how to fix them." },
        { week: "Week 3", topic: "Cross-Site Scripting (XSS) & Content Protections", details: "Sanitizing user inputs, escaping text blocks, and setting up strict browser headers." }
      ],
      codeExample: `// Setting secure headers in Express using Helmet
const express = require('express');
const helmet = require('helmet');
const app = express();

// Enable strict CSP, STS, frameguard, and DNS prefetch controls
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      scriptSrc: ["'self'", "https://apis.google.com"],
      styleSrc: ["'self'", "https://fonts.googleapis.com"],
      imgSrc: ["'self'", "data:", "https://images.unsplash.com"],
    },
  },
}));`
    }
  },
  {
    id: "cloud-computing",
    title: "Cloud Computing Fundamentals (AWS & GCP)",
    category: "Software Engineering",
    level: "Intermediate",
    duration: "7 Weeks",
    instructor: "George Lopez",
    rating: 4.7,
    studentsEnrolled: 1120,
    description: "Take your apps to the skies. Learn cloud basics, virtual servers (EC2/Compute Engine), serverless, storage buckets, and deployment automation.",
    image: "https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/2Idv0e88K4I",
    explanation: {
      overview: "Transition from local hosting to highly resilient, global cloud architectures. This course covers cloud service models (IaaS, PaaS, SaaS), networking setups (VPCs, Subnets), security access matrices, and container hosting.",
      objectives: [
        "Differentiate and select between AWS, GCP, and Azure options.",
        "Deploy scalable server instances and route traffic using load balancers.",
        "Configure object buckets for highly accessible asset delivery.",
        "Draft Identity and Access Management (IAM) security policies."
      ],
      syllabus: [
        { week: "Week 1", topic: "Cloud Architecture Foundations", details: "Shared responsibility model, virtualization, geographic regions, and edge endpoints." },
        { week: "Week 2", topic: "Virtual Compute Instances & Hosting", details: "Configuring server memory, operating systems, and firewall access lists." },
        { week: "Week 3", topic: "Cloud Storage & Database Engines", details: "Object storage (S3/Cloud Storage) vs block storage vs managed SQL engines." }
      ],
      codeExample: `# AWS CloudFormation Example: Create secure S3 Bucket
Resources:
  SecureElearningBucket:
    Type: 'AWS::S3::Bucket'
    Properties:
      BucketName: 'edu-craft-assets-bucket-2026'
      PublicAccessBlockConfiguration:
        BlockPublicAcls: true
        BlockPublicPolicy: true
        IgnorePublicAcls: true
        RestrictPublicBuckets: true`
    }
  },
  {
    id: "git-github",
    title: "Git & GitHub Version Control",
    category: "Software Engineering",
    level: "Beginner",
    duration: "3 Weeks",
    instructor: "Clara Oswald",
    rating: 4.8,
    studentsEnrolled: 1590,
    description: "Collaborate like a pro. Learn git commits, branching, merging, resolving conflicts, pull requests, and setting up collaborative project boards.",
    image: "https://images.unsplash.com/photo-1618401471353-b98aedd07871?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/RGOj5yH7evk",
    explanation: {
      overview: "Version control is the lifeblood of software collaboration. This course guides you step-by-step from locally tracking files to raising structured Pull Requests (PRs), reviewing team updates, and rolling back changes.",
      objectives: [
        "Initialize repositories and manage files using Git commands.",
        "Handle standard branch merges and isolate conflicts.",
        "Raise, discuss, and resolve remote Pull Requests on GitHub.",
        "Utilize Git stash, rebase, and cherry-pick for cleaner histories."
      ],
      syllabus: [
        { week: "Week 1", topic: "Local Git Workflows & Staging", details: "git init, add, status, commit, and tracking historical changes." },
        { week: "Week 2", topic: "Branching & Isolated Feature Development", details: "Creating, listing, shifting branches, and performing risk-free merges." },
        { week: "Week 3", topic: "Remote Repositories & PR Collaborations", details: "Cloning, pushing, pulling, resolving merge conflicts, and raising PRs." }
      ],
      codeExample: `# Professional Team Git Commands Checklist
# 1. Create and shift to a secure feature branch
git checkout -b feature/elearning-courses

# 2. Add modified assets and log descriptive commits
git add src/data/courses.ts
git commit -m "feat: add 16 core courses data for applet preview"

# 3. Pull latest main changes to prevent merge conflicts
git checkout main
git pull origin main
git checkout feature/elearning-courses
git merge main

# 4. Push branch to GitHub & open a new Pull Request
git push origin feature/elearning-courses`
    }
  },
  {
    id: "machine-learning",
    title: "Introduction to Machine Learning",
    category: "Software Engineering",
    level: "Advanced",
    duration: "10 Weeks",
    instructor: "Dr. Victor Stone",
    rating: 4.9,
    studentsEnrolled: 920,
    description: "Start your artificial intelligence journey. Understand regression, classification, clustering, neural networks, and how to train models in Python.",
    image: "https://images.unsplash.com/photo-1527474305487-b87b222841cc?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/GwIo3gDZUtQ",
    explanation: {
      overview: "Machine Learning is transforming industries. This comprehensive, advanced course provides high-fidelity explanations of ML theory, model optimization, feature engineering, and neural architectures.",
      objectives: [
        "Clean, normalize, and split data collections for training.",
        "Build and optimize Linear Regression and Decision Tree models.",
        "Train neural classifiers with scikit-learn and TensorFlow.",
        "Evaluate model performance metrics (Accuracy, F1-Score, ROC/AUC)."
      ],
      syllabus: [
        { week: "Week 1", topic: "Data Processing & Pre-processing", details: "Handling missing records, numerical scalers, and encoding categories." },
        { week: "Week 2", topic: "Supervised Learning: Regressions & Trees", details: "Predicting quantities using linear regression, decision trees, and forests." },
        { week: "Week 3", topic: "Unsupervised Learning & Clustering", details: "Grouping raw data into functional segments using K-Means algorithms." }
      ],
      codeExample: `# Python scikit-learn classifier model training
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score

# 1. Load mock data
X = np.random.rand(100, 5) # 100 entries, 5 features each
y = np.random.choice([0, 1], size=100) # Binary classes

# 2. Train-Test division
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# 3. Define and train RandomForest
model = RandomForestClassifier(n_estimators=50, max_depth=5)
model.fit(X_train, y_train)

# 4. Assess metrics
preds = model.predict(X_test)
print(f"Model accuracy: {accuracy_score(y_test, preds) * 100:.2f}%")`
    }
  },
  {
    id: "devops-cicd",
    title: "DevOps & Automating CI/CD Pipelines",
    category: "Software Engineering",
    level: "Advanced",
    duration: "8 Weeks",
    instructor: "Marcus Aurelius",
    rating: 4.8,
    studentsEnrolled: 810,
    description: "Learn how modern software teams build and deploy code continuously. Master Docker, GitHub Actions, and container deployment flows.",
    image: "https://images.unsplash.com/photo-1618401471353-b98aedd07871?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/RGOj5yH7evk",
    explanation: {
      overview: "Continuous Integration and Continuous Deployment (CI/CD) ensures software is tested and shipped reliably. This advanced course teaches containerization with Docker, YAML workflows, and cloud container hosting.",
      objectives: [
        "Write Dockerfiles to pack and isolate web applications.",
        "Draft YAML config scripts for automatic tests on push.",
        "Implement secure rolling cloud updates with zero downtime.",
        "Set up monitoring alerts to capture runtime container crashes."
      ],
      syllabus: [
        { week: "Week 1", topic: "DevOps Philosophy & Environment parity", details: "Unifying developer setups with production environments using containers." },
        { week: "Week 2", topic: "Docker Containerization Essentials", details: "Images, containers, volumes, docker-compose, and custom registries." },
        { week: "Week 3", topic: "GitHub Actions CI/CD YAML Builders", details: "Running tests, compiling build artifacts, and securely deploying to servers." }
      ],
      codeExample: `# Dockerfile for building and serving web applications
# 1. Compilation Stage
FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# 2. Production Stage
FROM nginx:alpine
COPY --from=builder /app/dist /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]`
    }
  },
  {
    id: "digital-marketing",
    title: "Digital Marketing Secrets & SEO",
    category: "Design",
    level: "Beginner",
    duration: "5 Weeks",
    instructor: "Sarah Jenkins",
    rating: 4.7,
    studentsEnrolled: 1280,
    description: "Drive traffic and rank high. Learn search engine optimization (SEO), Google Analytics, social media algorithms, and content strategies.",
    image: "https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80",
    videoUrl: "https://www.youtube.com/embed/nU-IIXDE39U",
    explanation: {
      overview: "Building a great app is only half the battle; people must find it. This course covers keywords search, semantic meta tags, off-page link structures, campaign setup, and visitor tracking frameworks.",
      objectives: [
        "Optimize search visibility using semantic markup and high-value keywords.",
        "Configure custom Google Tag Manager and Analytics events.",
        "Establish email marketing automations that nurture sales leads.",
        "Analyze traffic acquisition patterns to optimize conversion rates."
      ],
      syllabus: [
        { week: "Week 1", topic: "SEO Fundamentals & Semantic Keywords", details: "Understand how crawlers operate and perform detailed keyword searches." },
        { week: "Week 2", topic: "On-Page Metadata & Page Speeds", details: "Writing clean description tags, image alts, and optimizing core web vitals." },
        { week: "Week 3", topic: "Data Analytics & Event Tracking", details: "Mapping clicks, scroll percentages, form completions, and conversion funnels." }
      ],
      codeExample: `<!-- High-performance SEO Header Configuration -->
<head>
  <title>Learn Coding Online | E-Learning Hub Academy</title>
  <meta name="description" content="Master web development, databases, and computer science with 15+ comprehensive courses. Register today and learn from expert university professors!">
  <link rel="canonical" href="https://elearning-hub-academy.com/courses" />
  
  <!-- Open Graph metadata for rich social sharing cards -->
  <meta property="og:title" content="E-Learning Hub - Master Software Engineering" />
  <meta property="og:description" content="Access 15+ industry-vetted programming paths led by experts." />
  <meta property="og:image" content="https://elearning-hub-academy.com/assets/images/hero-marketing.jpg" />
  <meta property="og:type" content="website" />
</head>`
    }
  }
];
