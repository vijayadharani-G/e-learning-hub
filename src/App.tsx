import React, { useState } from "react";
import { Navbar } from "./components/Navbar";
import { CourseCard } from "./components/CourseCard";
import { CoursePlayer } from "./components/CoursePlayer";
import { Dashboard } from "./components/Dashboard";
import { COURSES, Course } from "./data/courses";
import { 
  BookOpen, 
  Clock, 
  Users, 
  Star, 
  GraduationCap, 
  CheckCircle2, 
  Code, 
  Sparkles, 
  Send, 
  Phone, 
  Mail, 
  MapPin, 
  ArrowRight,
  User,
  Search,
  Filter,
  ArrowUpRight
} from "lucide-react";

// Asset Imports
// @ts-ignore
import heroBanner from "./assets/images/hero_banner_1782913089688.jpg";
// @ts-ignore
import aboutTeam from "./assets/images/about_team_1782913105633.jpg";
// @ts-ignore
import contactUs from "./assets/images/contact_us_1782913117905.jpg";
// @ts-ignore
import loginDecor from "./assets/images/login_decor_1782913133754.jpg";

interface UserSession {
  username: string;
  email: string;
  registeredCourses: string[];
}

export default function App() {
  const [currentTab, setCurrentTab] = useState<string>("home");
  const [selectedCourseId, setSelectedCourseId] = useState<string | null>(null);
  
  // Simulated User Session State
  const [user, setUser] = useState<UserSession | null>(null);

  // Search and Category filters for courses page
  const [searchQuery, setSearchQuery] = useState<string>("");
  const [selectedCategory, setSelectedCategory] = useState<string>("All");

  // Authentication Forms State
  const [loginUsername, setLoginUsername] = useState("");
  const [loginPassword, setLoginPassword] = useState("");
  const [authError, setAuthError] = useState("");

  const [regUsername, setRegUsername] = useState("");
  const [regEmail, setRegEmail] = useState("");
  const [regPassword, setRegPassword] = useState("");
  const [regSelectedCourse, setRegSelectedCourse] = useState(COURSES[0].id);

  // Contact Page Form State
  const [contactName, setContactName] = useState("");
  const [contactEmail, setContactEmail] = useState("");
  const [contactMessage, setContactMessage] = useState("");
  const [contactSuccess, setContactSuccess] = useState(false);

  // Selected Course Detail Modal View (when not logged in)
  const [previewCourse, setPreviewCourse] = useState<Course | null>(null);

  // Handle Select Course
  const handleSelectCourse = (courseId: string) => {
    const course = COURSES.find(c => c.id === courseId);
    if (!course) return;

    if (user && user.registeredCourses.includes(courseId)) {
      setSelectedCourseId(courseId);
      setCurrentTab("player");
    } else {
      // Show detail preview modal
      setPreviewCourse(course);
    }
  };

  // Quick enroll from preview card
  const handleEnrollFromPreview = (courseId: string) => {
    setPreviewCourse(null);
    if (!user) {
      setRegSelectedCourse(courseId);
      setCurrentTab("register");
    } else {
      // Add to registered list
      setUser({
        ...user,
        registeredCourses: [...user.registeredCourses, courseId]
      });
      setSelectedCourseId(courseId);
      setCurrentTab("player");
    }
  };

  // Log in Simulation
  const handleLoginSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!loginUsername || !loginPassword) {
      setAuthError("Please fill in all credentials.");
      return;
    }
    // Simulate valid login
    setUser({
      username: loginUsername,
      email: `${loginUsername.toLowerCase()}@elearn.com`,
      registeredCourses: ["html-css", "javascript"] // pre-enroll in some default courses
    });
    setLoginUsername("");
    setLoginPassword("");
    setAuthError("");
    setCurrentTab("dashboard");
  };

  // Register Simulation
  const handleRegisterSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!regUsername || !regEmail || !regPassword) {
      setAuthError("All fields are mandatory.");
      return;
    }
    // Simulate successful registration
    setUser({
      username: regUsername,
      email: regEmail,
      registeredCourses: [regSelectedCourse]
    });
    setRegUsername("");
    setRegEmail("");
    setRegPassword("");
    setAuthError("");
    
    // Immediately open player for registered course
    setSelectedCourseId(regSelectedCourse);
    setCurrentTab("player");
  };

  // Logout
  const handleLogout = () => {
    setUser(null);
    setSelectedCourseId(null);
    setCurrentTab("home");
  };

  // Contact Submit
  const handleContactSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!contactName || !contactEmail || !contactMessage) return;
    setContactSuccess(true);
    setTimeout(() => {
      setContactName("");
      setContactEmail("");
      setContactMessage("");
      setContactSuccess(false);
    }, 4000);
  };

  // Categories list
  const categories = ["All", "Web Development", "Software Engineering", "Database", "Design"];

  // Filter Courses
  const filteredCourses = COURSES.filter((course) => {
    const matchesSearch = course.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
                          course.description.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCategory = selectedCategory === "All" || course.category === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  return (
    <div className="bg-white min-h-screen flex flex-col font-sans text-gray-800" id="applet-root">
      {/* Navigation */}
      <Navbar 
        currentTab={currentTab} 
        setCurrentTab={setCurrentTab} 
        user={user} 
        onLogout={handleLogout} 
      />

      {/* Main Sections */}
      <main className="flex-grow">
        
        {/* ==================== HOME TAB ==================== */}
        {currentTab === "home" && (
          <div id="home-section" className="animate-fade-in">
            {/* Hero Section */}
            <section className="relative bg-white pt-16 pb-20 overflow-hidden">
              <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div className="space-y-6">
                  <span className="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 px-4 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider">
                    <Sparkles className="h-3.5 w-3.5" />
                    <span>Next-Gen Academic Gateway</span>
                  </span>
                  
                  <h1 className="font-sans font-extrabold text-4xl sm:text-5xl lg:text-6xl text-gray-900 tracking-tight leading-[1.1]">
                    Master Modern Technology <span className="text-indigo-600">Step-by-Step</span>
                  </h1>
                  
                  <p className="font-sans text-base text-gray-600 max-w-lg leading-relaxed">
                    Access over 15+ university-tier curriculum courses including HTML, CSS, JavaScript, PHP, Python, and SQL. Interactive playbacks, direct sandbox playgrounds, and verified credentials await.
                  </p>

                  <div className="flex flex-wrap gap-4 pt-2">
                    <button
                      onClick={() => setCurrentTab("courses")}
                      className="bg-indigo-600 hover:bg-indigo-700 text-white font-sans font-semibold px-8 py-4 rounded-2xl shadow-lg hover:shadow-indigo-100 transition-all cursor-pointer flex items-center gap-2 focus:outline-none"
                    >
                      <span>Explore 15+ Courses</span>
                      <ArrowRight className="h-5 w-5" />
                    </button>
                    <button
                      onClick={() => setCurrentTab(user ? "dashboard" : "login")}
                      className="border border-gray-200 hover:border-gray-300 text-gray-800 hover:bg-gray-50 font-sans font-semibold px-8 py-4 rounded-2xl transition-all cursor-pointer focus:outline-none"
                    >
                      {user ? "My Learning Portal" : "Student Login"}
                    </button>
                  </div>
                </div>

                <div className="relative flex justify-center">
                  <div className="relative w-full max-w-lg">
                    {/* Shadow effects */}
                    <div className="absolute -inset-1 bg-indigo-600/10 rounded-3xl blur-xl opacity-30" />
                    <img
                      src={heroBanner}
                      alt="E-Learning Portal Banner"
                      className="relative rounded-3xl shadow-2xl border border-gray-100 object-cover w-full aspect-[4/3]"
                      referrerPolicy="no-referrer"
                    />
                  </div>
                </div>
              </div>
            </section>

            {/* Statistics Banner */}
            <section className="bg-gray-50 border-y border-gray-100 py-10">
              <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                  <div>
                    <span className="text-3xl sm:text-4xl font-sans font-extrabold text-indigo-600 block">15+</span>
                    <span className="text-xs font-mono text-gray-400 uppercase tracking-widest block mt-1">Syllabi Pathways</span>
                  </div>
                  <div>
                    <span className="text-3xl sm:text-4xl font-sans font-extrabold text-indigo-600 block">25k+</span>
                    <span className="text-xs font-mono text-gray-400 uppercase tracking-widest block mt-1">Global Learners</span>
                  </div>
                  <div>
                    <span className="text-3xl sm:text-4xl font-sans font-extrabold text-indigo-600 block">180+</span>
                    <span className="text-xs font-mono text-gray-400 uppercase tracking-widest block mt-1">Study Hours</span>
                  </div>
                  <div>
                    <span className="text-3xl sm:text-4xl font-sans font-extrabold text-indigo-600 block">99.8%</span>
                    <span className="text-xs font-mono text-gray-400 uppercase tracking-widest block mt-1">Course Rating</span>
                  </div>
                </div>
              </div>
            </section>

            {/* Core Features */}
            <section className="py-20 bg-white">
              <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="text-center max-w-3xl mx-auto mb-16">
                  <span className="text-xs font-mono font-bold uppercase tracking-widest text-indigo-600">Engineered For Students</span>
                  <h2 className="font-sans font-bold text-3xl sm:text-4xl text-gray-900 mt-2">
                    Dynamic Learning Features Designed For Excellence
                  </h2>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                  {/* Feature 1 */}
                  <div className="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all">
                    <div className="bg-indigo-50 text-indigo-600 p-4 rounded-xl inline-block mb-6">
                      <Code className="h-6 w-6" />
                    </div>
                    <h3 className="font-sans font-bold text-lg text-gray-900 mb-3">Interactive Code Labs</h3>
                    <p className="text-sm font-sans text-gray-600 leading-relaxed">
                      Practice programming skills directly in the browser. Edit sample HTML/CSS codes and run them instantly with zero setup time.
                    </p>
                  </div>
                  {/* Feature 2 */}
                  <div className="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all">
                    <div className="bg-emerald-50 text-emerald-600 p-4 rounded-xl inline-block mb-6">
                      <CheckCircle2 className="h-6 w-6" />
                    </div>
                    <h3 className="font-sans font-bold text-lg text-gray-900 mb-3">Structured Video Classes</h3>
                    <p className="text-sm font-sans text-gray-600 leading-relaxed">
                      Access step-by-step video lessons detailing backend, frontend, design paradigms, and security alignments.
                    </p>
                  </div>
                  {/* Feature 3 */}
                  <div className="bg-white p-8 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all">
                    <div className="bg-purple-50 text-purple-600 p-4 rounded-xl inline-block mb-6">
                      <GraduationCap className="h-6 w-6" />
                    </div>
                    <h3 className="font-sans font-bold text-lg text-gray-900 mb-3">Accredited Certifications</h3>
                    <p className="text-sm font-sans text-gray-600 leading-relaxed">
                      Verify your progress! Earn credentials for completing course requirements, perfect to boost your LinkedIn or resume.
                    </p>
                  </div>
                </div>
              </div>
            </section>

            {/* CTA section with About Team Image */}
            <section className="py-16 bg-gray-50 border-t border-gray-100">
              <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                  <img
                    src={aboutTeam}
                    alt="Students Discussing Courses"
                    className="rounded-3xl shadow-lg border border-gray-200 object-cover aspect-[4/3] w-full"
                    referrerPolicy="no-referrer"
                  />
                </div>
                <div className="space-y-6">
                  <span className="text-xs font-mono font-bold text-indigo-600 uppercase tracking-wider block">OUR MISSION</span>
                  <h2 className="font-sans font-extrabold text-3xl text-gray-900 tracking-tight leading-snug">
                    Learn Without Limits, Connect Directly with Industry Syllabi
                  </h2>
                  <p className="font-sans text-sm text-gray-600 leading-relaxed">
                    We believe in providing clean, practical educational materials. That is why our curriculum focuses heavily on hands-on experiences, code practices, and verified syllabus modules.
                  </p>
                  <button
                    onClick={() => setCurrentTab("about")}
                    className="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-sans font-semibold px-6 py-3 rounded-xl shadow-md cursor-pointer focus:outline-none"
                  >
                    <span>Read Our Story</span>
                    <ArrowRight className="h-4 w-4" />
                  </button>
                </div>
              </div>
            </section>
          </div>
        )}

        {/* ==================== COURSES TAB ==================== */}
        {currentTab === "courses" && (
          <div id="courses-section" className="animate-fade-in max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            
            {/* Header Title */}
            <div className="text-center max-w-3xl mx-auto mb-10">
              <span className="text-xs font-mono font-bold uppercase tracking-widest text-indigo-600">Course Catalog</span>
              <h1 className="font-sans font-extrabold text-3xl sm:text-4xl text-gray-900 mt-2">
                Browse our 15+ Advanced Programs
              </h1>
              <p className="font-sans text-sm text-gray-500 mt-2">
                All lectures, code scripts, and exercises are fully unlocked for preview. Register to save your progress!
              </p>
            </div>

            {/* Filter and Search Bar */}
            <div className="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
              {/* Category selector */}
              <div className="flex flex-wrap gap-2 w-full md:w-auto">
                {categories.map((cat) => (
                  <button
                    key={cat}
                    onClick={() => setSelectedCategory(cat)}
                    className={`px-4 py-2 rounded-xl text-xs font-sans font-semibold transition-all cursor-pointer focus:outline-none ${
                      selectedCategory === cat
                        ? "bg-indigo-600 text-white shadow-md shadow-indigo-100"
                        : "bg-gray-50 text-gray-600 hover:bg-gray-100"
                    }`}
                  >
                    {cat}
                  </button>
                ))}
              </div>

              {/* Search input */}
              <div className="relative w-full md:w-80">
                <Search className="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                <input
                  type="text"
                  placeholder="Search program titles..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full bg-gray-50 border border-gray-200 rounded-xl pl-9 pr-4 py-2 text-sm font-sans focus:outline-none focus:border-indigo-500"
                />
              </div>
            </div>

            {/* Course Grid */}
            {filteredCourses.length === 0 ? (
              <div className="bg-white border border-gray-100 rounded-2xl p-16 text-center max-w-md mx-auto shadow-sm">
                <p className="font-sans font-semibold text-gray-900 mb-1">No programs found</p>
                <p className="text-xs font-sans text-gray-500">Try checking spelling or adjusting category filters.</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="courses-catalog-grid">
                {filteredCourses.map((course) => (
                  <CourseCard
                    key={course.id}
                    course={course}
                    onSelect={handleSelectCourse}
                    isRegistered={user ? user.registeredCourses.includes(course.id) : false}
                  />
                ))}
              </div>
            )}
          </div>
        )}

        {/* ==================== ABOUT TAB ==================== */}
        {currentTab === "about" && (
          <div id="about-section" className="animate-fade-in max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-16">
              {/* Left Column: text */}
              <div className="space-y-6">
                <span className="text-xs font-mono font-bold text-indigo-600 uppercase tracking-widest block">ABOUT THE PORTAL</span>
                <h1 className="font-sans font-extrabold text-3xl sm:text-4xl text-gray-900 tracking-tight leading-snug">
                  Accredited Digital Pedagogical Platform
                </h1>
                <p className="font-sans text-sm text-gray-600 leading-relaxed">
                  Founded by a collective of computer science professors and industry practitioners, E-Learning Hub delivers comprehensive curricula tailored for self-motivated learning. Our programs encompass key computer technologies from fundamental HTML structures to complex data analysis, machine models, and backend paradigms.
                </p>
                <div className="space-y-4 pt-2">
                  <div className="flex gap-3">
                    <div className="mt-0.5 bg-indigo-50 text-indigo-600 p-1.5 rounded-lg shrink-0">
                      <CheckCircle2 className="h-5 w-5" />
                    </div>
                    <div>
                      <h4 className="font-sans font-bold text-sm text-gray-900">Industry-Vetted Curriculum</h4>
                      <p className="text-xs font-sans text-gray-500">Every syllabus is built in collaboration with enterprise technology architects.</p>
                    </div>
                  </div>
                  <div className="flex gap-3">
                    <div className="mt-0.5 bg-indigo-50 text-indigo-600 p-1.5 rounded-lg shrink-0">
                      <CheckCircle2 className="h-5 w-5" />
                    </div>
                    <div>
                      <h4 className="font-sans font-bold text-sm text-gray-900">Integrated Sandbox Environments</h4>
                      <p className="text-xs font-sans text-gray-500">Immediate code verification without setting up secondary IDE systems.</p>
                    </div>
                  </div>
                </div>
              </div>

              {/* Right Column: image */}
              <div>
                <img
                  src={aboutTeam}
                  alt="Team Discussing Academic Goals"
                  className="rounded-3xl shadow-lg border border-gray-200 object-cover aspect-[4/3] w-full"
                  referrerPolicy="no-referrer"
                />
              </div>
            </div>

            {/* Our Faculty Panel */}
            <div className="border-t border-gray-100 pt-16">
              <div className="text-center max-w-2xl mx-auto mb-12">
                <span className="text-xs font-mono font-bold text-indigo-600 uppercase tracking-widest">PEDAGOGICAL LEADERSHIP</span>
                <h2 className="font-sans font-bold text-2xl text-gray-900 mt-1">Our Esteemed Instructors</h2>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                {/* Faculty 1 */}
                <div className="bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-sm">
                  <div className="h-20 w-20 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-sans font-extrabold text-2xl mx-auto mb-4">
                    DG
                  </div>
                  <h3 className="font-sans font-bold text-base text-gray-900">Dr. Govindharajan</h3>
                  <span className="text-xs font-mono text-gray-400 block mt-0.5">Professor in Web Architecture</span>
                  <p className="text-xs font-sans text-gray-500 mt-3 leading-relaxed">
                    With over 18 years in full-stack engineering research, specializing in highly scalable PHP and relational data pipelines.
                  </p>
                </div>
                {/* Faculty 2 */}
                <div className="bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-sm">
                  <div className="h-20 w-20 rounded-full bg-teal-100 flex items-center justify-center text-teal-700 font-sans font-extrabold text-2xl mx-auto mb-4">
                    SJ
                  </div>
                  <h3 className="font-sans font-bold text-base text-gray-900">Sara Johnson</h3>
                  <span className="text-xs font-mono text-gray-400 block mt-0.5">Lead Instructor of JS Foundation</span>
                  <p className="text-xs font-sans text-gray-500 mt-3 leading-relaxed">
                    An advocate for functional ES6, stateful client frameworks, and responsive mobile-first visual patterns.
                  </p>
                </div>
                {/* Faculty 3 */}
                <div className="bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-sm">
                  <div className="h-20 w-20 rounded-full bg-purple-100 flex items-center justify-center text-purple-700 font-sans font-extrabold text-2xl mx-auto mb-4">
                    VS
                  </div>
                  <h3 className="font-sans font-bold text-base text-gray-900">Dr. Victor Stone</h3>
                  <span className="text-xs font-mono text-gray-400 block mt-0.5">Researcher in Artificial Intelligence</span>
                  <p className="text-xs font-sans text-gray-500 mt-3 leading-relaxed">
                    Specialized in neural networks, regression classifications, and robust data-focused automation platforms.
                  </p>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* ==================== CONTACT TAB ==================== */}
        {currentTab === "contact" && (
          <div id="contact-section" className="animate-fade-in max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
              {/* Info Column */}
              <div className="lg:col-span-5 space-y-8">
                <div>
                  <span className="text-xs font-mono font-bold text-indigo-600 uppercase tracking-widest block">GET IN TOUCH</span>
                  <h1 className="font-sans font-extrabold text-3xl text-gray-900 mt-1 tracking-tight leading-tight">
                    Contact Our Help Desk
                  </h1>
                  <p className="font-sans text-sm text-gray-600 mt-3 leading-relaxed">
                    Have questions about enrollment eligibility, course syllabi, XAMPP packages, or student credentials? Drop us a line and our academic advisors will respond within 24 hours.
                  </p>
                </div>

                <div className="space-y-4">
                  <div className="flex items-center gap-4 text-sm text-gray-700 font-sans">
                    <div className="bg-indigo-50 text-indigo-600 p-2.5 rounded-lg shrink-0">
                      <Phone className="h-5 w-5" />
                    </div>
                    <div>
                      <span className="font-semibold block text-xs uppercase font-mono tracking-wider text-gray-400">Institutional Phone</span>
                      <span className="font-medium text-gray-900">+91 98765 43210</span>
                    </div>
                  </div>

                  <div className="flex items-center gap-4 text-sm text-gray-700 font-sans">
                    <div className="bg-indigo-50 text-indigo-600 p-2.5 rounded-lg shrink-0">
                      <Mail className="h-5 w-5" />
                    </div>
                    <div>
                      <span className="font-semibold block text-xs uppercase font-mono tracking-wider text-gray-400">Advisor Email</span>
                      <span className="font-medium text-gray-900">support@elearninghub.com</span>
                    </div>
                  </div>

                  <div className="flex items-center gap-4 text-sm text-gray-700 font-sans">
                    <div className="bg-indigo-50 text-indigo-600 p-2.5 rounded-lg shrink-0">
                      <MapPin className="h-5 w-5" />
                    </div>
                    <div>
                      <span className="font-semibold block text-xs uppercase font-mono tracking-wider text-gray-400">Main Building Location</span>
                      <span className="font-medium text-gray-900">Block C, Tech Corridor, Academic City</span>
                    </div>
                  </div>
                </div>

                <div className="border-t border-gray-100 pt-6">
                  <img
                    src={contactUs}
                    alt="Contact 3D Graphics"
                    className="rounded-2xl border border-gray-100 shadow-inner max-h-48 object-cover w-full"
                    referrerPolicy="no-referrer"
                  />
                </div>
              </div>

              {/* Form Column */}
              <div className="lg:col-span-7 bg-white p-8 md:p-10 rounded-2xl border border-gray-100 shadow-lg">
                <h3 className="font-sans font-bold text-xl text-gray-900 mb-6">Send an Inquiry</h3>
                
                {contactSuccess ? (
                  <div className="bg-emerald-50 border border-emerald-100 rounded-xl p-6 text-center animate-pulse">
                    <CheckCircle2 className="h-8 w-8 text-emerald-600 mx-auto mb-3" />
                    <h4 className="font-sans font-bold text-sm text-emerald-900">Inquiry Sent Successfully</h4>
                    <p className="text-xs font-sans text-emerald-700 mt-1">
                      Thank you! Our academic counselors will reach back to you shortly.
                    </p>
                  </div>
                ) : (
                  <form onSubmit={handleContactSubmit} className="space-y-4" id="contact-form-node">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label className="block text-xs font-sans font-semibold text-gray-700 mb-1">Your Name</label>
                        <input
                          type="text"
                          required
                          value={contactName}
                          onChange={(e) => setContactName(e.target.value)}
                          placeholder="Your full name"
                          className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-sans focus:outline-none focus:border-indigo-500"
                        />
                      </div>
                      <div>
                        <label className="block text-xs font-sans font-semibold text-gray-700 mb-1">Email Address</label>
                        <input
                          type="email"
                          required
                          value={contactEmail}
                          onChange={(e) => setContactEmail(e.target.value)}
                          placeholder="your.email@domain.com"
                          className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-sans focus:outline-none focus:border-indigo-500"
                        />
                      </div>
                    </div>

                    <div>
                      <label className="block text-xs font-sans font-semibold text-gray-700 mb-1">Inquiry Details</label>
                      <textarea
                        required
                        rows={5}
                        value={contactMessage}
                        onChange={(e) => setContactMessage(e.target.value)}
                        placeholder="Type details regarding classes or technical syllabus configurations..."
                        className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-sans focus:outline-none focus:border-indigo-500 resize-none"
                      />
                    </div>

                    <button
                      type="submit"
                      className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-sans font-semibold py-3.5 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer focus:outline-none"
                    >
                      <Send className="h-4 w-4" />
                      <span>Send Advisory Request</span>
                    </button>
                  </form>
                )}
              </div>
            </div>
          </div>
        )}

        {/* ==================== LOGIN TAB ==================== */}
        {currentTab === "login" && (
          <div id="login-section" className="animate-fade-in max-w-4xl mx-auto px-4 py-16">
            <div className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-2xl grid grid-cols-1 md:grid-cols-2">
              {/* Left Side: Image */}
              <div className="relative bg-indigo-950 text-white p-8 md:p-12 flex flex-col justify-between overflow-hidden">
                <div className="absolute inset-0 opacity-20">
                  <img
                    src={loginDecor}
                    alt="Login aesthetic background"
                    className="w-full h-full object-cover"
                    referrerPolicy="no-referrer"
                  />
                </div>
                <div className="relative z-10 flex items-center gap-2">
                  <GraduationCap className="h-6 w-6 text-indigo-400" />
                  <span className="font-sans font-bold text-sm tracking-widest uppercase">E-Learning Hub</span>
                </div>
                <div className="relative z-10 mt-20">
                  <h2 className="font-sans font-extrabold text-2xl tracking-tight leading-snug">
                    Academic Single-Sign-On
                  </h2>
                  <p className="text-xs font-sans text-indigo-200 mt-2 leading-relaxed opacity-90">
                    Step inside your student portal to instantly access your customized course files, responsive sandbox layouts, and certified lecture tracks.
                  </p>
                </div>
                <div className="relative z-10 text-[10px] font-mono text-indigo-400 uppercase tracking-widest mt-12">
                  SECURE PORTAL INTERFACE
                </div>
              </div>

              {/* Right Side: Form */}
              <div className="p-8 md:p-12 flex flex-col justify-center">
                <div className="mb-6">
                  <h3 className="font-sans font-extrabold text-xl text-gray-900">Welcome Back</h3>
                  <p className="text-xs font-sans text-gray-500 mt-1">Sign in to resume study progress.</p>
                </div>

                {authError && (
                  <div className="bg-red-50 border border-red-100 text-red-700 text-xs font-semibold p-3.5 rounded-xl mb-4">
                    {authError}
                  </div>
                )}

                <form onSubmit={handleLoginSubmit} className="space-y-4" id="login-form">
                  <div>
                    <label className="block text-[11px] font-sans font-bold text-gray-600 uppercase tracking-wider mb-1">Username</label>
                    <input
                      type="text"
                      required
                      value={loginUsername}
                      onChange={(e) => { setLoginUsername(e.target.value); setAuthError(""); }}
                      placeholder="e.g. StudentDharani"
                      className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-sans focus:outline-none focus:border-indigo-500"
                    />
                  </div>

                  <div>
                    <label className="block text-[11px] font-sans font-bold text-gray-600 uppercase tracking-wider mb-1">Password</label>
                    <input
                      type="password"
                      required
                      value={loginPassword}
                      onChange={(e) => { setLoginPassword(e.target.value); setAuthError(""); }}
                      placeholder="••••••••"
                      className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-sans focus:outline-none focus:border-indigo-500"
                    />
                  </div>

                  <button
                    type="submit"
                    className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-sans font-semibold py-3 rounded-xl shadow-md transition-all cursor-pointer focus:outline-none"
                  >
                    Authorize Portal Entry
                  </button>
                </form>

                <p className="text-xs font-sans text-gray-500 text-center mt-6">
                  New student?{" "}
                  <button
                    onClick={() => setCurrentTab("register")}
                    className="text-indigo-600 hover:underline font-semibold focus:outline-none cursor-pointer"
                  >
                    Create student account here
                  </button>
                </p>
              </div>
            </div>
          </div>
        )}

        {/* ==================== REGISTER TAB ==================== */}
        {currentTab === "register" && (
          <div id="register-section" className="animate-fade-in max-w-4xl mx-auto px-4 py-16">
            <div className="bg-white border border-gray-100 rounded-3xl overflow-hidden shadow-2xl grid grid-cols-1 md:grid-cols-2">
              {/* Left Side: Aesthetic Decor */}
              <div className="relative bg-indigo-950 text-white p-8 md:p-12 flex flex-col justify-between overflow-hidden">
                <div className="absolute inset-0 opacity-20">
                  <img
                    src={loginDecor}
                    alt="Registration design layout"
                    className="w-full h-full object-cover"
                    referrerPolicy="no-referrer"
                  />
                </div>
                <div className="relative z-10 flex items-center gap-2">
                  <GraduationCap className="h-6 w-6 text-indigo-400" />
                  <span className="font-sans font-bold text-sm tracking-widest uppercase">E-Learning Hub</span>
                </div>
                <div className="relative z-10 mt-20">
                  <h2 className="font-sans font-extrabold text-2xl tracking-tight leading-snug">
                    Academic Registration
                  </h2>
                  <p className="text-xs font-sans text-indigo-200 mt-2 leading-relaxed opacity-90">
                    Get fully registered today! Students can view all course modules, syllabi, and practice guides before completing registration. Upon registration, unlock immediate video content, coding workspace panels, and advisor supports.
                  </p>
                </div>
                <div className="relative z-10 text-[10px] font-mono text-indigo-400 uppercase tracking-widest mt-12">
                  STUDENT RECRUITMENT ACTIVE
                </div>
              </div>

              {/* Right Side: Form */}
              <div className="p-8 md:p-12 flex flex-col justify-center">
                <div className="mb-6">
                  <h3 className="font-sans font-extrabold text-xl text-gray-900">Student Sign-Up</h3>
                  <p className="text-xs font-sans text-gray-500 mt-1">Gain instant access to active sandboxes.</p>
                </div>

                {authError && (
                  <div className="bg-red-50 border border-red-100 text-red-700 text-xs font-semibold p-3.5 rounded-xl mb-4">
                    {authError}
                  </div>
                )}

                <form onSubmit={handleRegisterSubmit} className="space-y-4" id="register-form">
                  <div>
                    <label className="block text-[11px] font-sans font-bold text-gray-600 uppercase tracking-wider mb-1">Desired Username</label>
                    <input
                      type="text"
                      required
                      value={regUsername}
                      onChange={(e) => { setRegUsername(e.target.value); setAuthError(""); }}
                      placeholder="e.g. LearnerDharani"
                      className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-sans focus:outline-none focus:border-indigo-500"
                    />
                  </div>

                  <div>
                    <label className="block text-[11px] font-sans font-bold text-gray-600 uppercase tracking-wider mb-1">Email Address</label>
                    <input
                      type="email"
                      required
                      value={regEmail}
                      onChange={(e) => { setRegEmail(e.target.value); setAuthError(""); }}
                      placeholder="e.g. student@domain.com"
                      className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-sans focus:outline-none focus:border-indigo-500"
                    />
                  </div>

                  <div>
                    <label className="block text-[11px] font-sans font-bold text-gray-600 uppercase tracking-wider mb-1">Select First Program to Enroll</label>
                    <select
                      value={regSelectedCourse}
                      onChange={(e) => setRegSelectedCourse(e.target.value)}
                      className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-sans focus:outline-none focus:border-indigo-500"
                    >
                      {COURSES.map((course) => (
                        <option key={course.id} value={course.id}>
                          {course.title} ({course.category})
                        </option>
                      ))}
                    </select>
                  </div>

                  <div>
                    <label className="block text-[11px] font-sans font-bold text-gray-600 uppercase tracking-wider mb-1">Security Password</label>
                    <input
                      type="password"
                      required
                      value={regPassword}
                      onChange={(e) => { setRegPassword(e.target.value); setAuthError(""); }}
                      placeholder="••••••••"
                      className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-sans focus:outline-none focus:border-indigo-500"
                    />
                  </div>

                  <button
                    type="submit"
                    className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-sans font-semibold py-3 rounded-xl shadow-md transition-all cursor-pointer focus:outline-none"
                  >
                    Complete Student Registration
                  </button>
                </form>

                <p className="text-xs font-sans text-gray-500 text-center mt-6">
                  Already registered?{" "}
                  <button
                    onClick={() => setCurrentTab("login")}
                    className="text-indigo-600 hover:underline font-semibold focus:outline-none cursor-pointer"
                  >
                    Sign in to student portal
                  </button>
                </p>
              </div>
            </div>
          </div>
        )}

        {/* ==================== STUDENT PORTAL / DASHBOARD TAB ==================== */}
        {currentTab === "dashboard" && user && (
          <Dashboard 
            user={user} 
            onSelectCourse={handleSelectCourse} 
            onNavigateToCourses={() => setCurrentTab("courses")} 
          />
        )}

        {/* ==================== ACTIVE COURSE PLAYER TAB ==================== */}
        {currentTab === "player" && selectedCourseId && (
          <CoursePlayer 
            course={COURSES.find(c => c.id === selectedCourseId)!} 
            onBack={() => setCurrentTab("dashboard")} 
          />
        )}

      </main>

      {/* Course Detail Modal (Before Enrollment) */}
      {previewCourse && (
        <div className="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 animate-fade-in" id="course-preview-modal">
          <div className="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-100 shadow-2xl p-6 relative">
            
            {/* Header info */}
            <div className="flex justify-between items-start mb-4">
              <div>
                <span className="text-xs font-mono font-bold text-indigo-600 uppercase tracking-wider">
                  {previewCourse.category}
                </span>
                <h3 className="font-sans font-extrabold text-xl text-gray-900 mt-1">
                  {previewCourse.title}
                </h3>
              </div>
              <button
                onClick={() => setPreviewCourse(null)}
                className="text-gray-400 hover:text-gray-600 p-1 bg-gray-50 rounded-lg transition-all focus:outline-none cursor-pointer"
              >
                ✕
              </button>
            </div>

            {/* Course image inside modal */}
            <div className="aspect-video w-full rounded-xl overflow-hidden mb-4 border border-gray-50">
              <img
                src={previewCourse.image}
                alt={previewCourse.title}
                className="w-full h-full object-cover"
                referrerPolicy="no-referrer"
              />
            </div>

            {/* General descriptions */}
            <p className="text-sm font-sans text-gray-600 mb-5 leading-relaxed">
              {previewCourse.description}
            </p>

            {/* Objective list */}
            <div className="bg-gray-50 p-4 rounded-xl border border-gray-100 mb-6">
              <span className="text-[10px] font-mono font-bold uppercase tracking-wider text-gray-400 block mb-2">COURSE TARGET OBJECTIVES</span>
              <div className="space-y-1.5">
                {previewCourse.explanation.objectives.slice(0, 3).map((obj, i) => (
                  <div key={i} className="flex items-start gap-2 text-xs text-gray-700 font-sans">
                    <CheckCircle2 className="h-4 w-4 text-emerald-500 shrink-0 mt-0.5" />
                    <span>{obj}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Action buttons inside preview modal */}
            <div className="flex gap-4 items-center">
              <button
                onClick={() => setPreviewCourse(null)}
                className="flex-1 border border-gray-200 hover:bg-gray-50 text-gray-700 font-sans font-medium py-3 rounded-xl text-sm cursor-pointer focus:outline-none"
              >
                Back to List
              </button>
              <button
                onClick={() => handleEnrollFromPreview(previewCourse.id)}
                className="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-sans font-semibold py-3 rounded-xl text-sm shadow-md transition-all cursor-pointer focus:outline-none flex items-center justify-center gap-1"
              >
                <span>Register to Start Learning</span>
                <ArrowRight className="h-4 w-4" />
              </button>
            </div>

          </div>
        </div>
      )}

      {/* Footer */}
      <footer className="bg-gray-900 text-gray-400 font-sans py-12 border-t border-gray-800" id="main-footer">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
          
          {/* Logo and Tagline */}
          <div className="md:col-span-2 space-y-4">
            <div className="flex items-center gap-3">
              <div className="bg-indigo-600 text-white p-2 rounded-xl">
                <GraduationCap className="h-5 w-5" />
              </div>
              <span className="font-sans font-bold text-lg text-white">
                E-Learning Hub
              </span>
            </div>
            <p className="text-xs text-gray-500 max-w-sm leading-relaxed">
              Shaping tech minds worldwide with 15+ comprehensive programs. Access dynamic video materials, code compilers, and verified university syllabus architectures cleanly.
            </p>
          </div>

          {/* Core pages shortcut */}
          <div>
            <h4 className="font-mono text-xs font-bold text-white uppercase tracking-widest mb-4">Navigations</h4>
            <ul className="space-y-2 text-xs">
              <li>
                <button onClick={() => setCurrentTab("home")} className="hover:text-white transition-colors cursor-pointer">
                  Main Homepage
                </button>
              </li>
              <li>
                <button onClick={() => setCurrentTab("courses")} className="hover:text-white transition-colors cursor-pointer">
                  All 15+ Courses
                </button>
              </li>
              <li>
                <button onClick={() => setCurrentTab("about")} className="hover:text-white transition-colors cursor-pointer">
                  Our Faculty Story
                </button>
              </li>
              <li>
                <button onClick={() => setCurrentTab("contact")} className="hover:text-white transition-colors cursor-pointer">
                  Technical Contact Support
                </button>
              </li>
            </ul>
          </div>

          {/* Academic disclaimer and download info */}
          <div>
            <h4 className="font-mono text-xs font-bold text-white uppercase tracking-widest mb-4">XAMPP Exporter</h4>
            <p className="text-xs text-gray-500 leading-relaxed mb-4">
              Export this entire platform (featuring PHP backend, SQL schema, and custom layout styling) directly to deploy in local XAMPP environments easily.
            </p>
            <div className="inline-flex items-center gap-1.5 text-xs text-indigo-400 font-medium">
              <span>Ready for PHP & MySQL</span>
            </div>
          </div>

        </div>

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-gray-800 mt-8 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
          <span>
            © 2026 E-Learning Hub Academy. Developed with exact specifications for local XAMPP server runs.
          </span>
          <div className="flex gap-4">
            <span className="text-[10px] font-mono text-gray-600 uppercase tracking-widest">
              ACCREDITED ACADEMY
            </span>
          </div>
        </div>
      </footer>
    </div>
  );
}
