import React from "react";
import { Award, BookOpen, Clock, Code, GraduationCap, ArrowRight, CheckCircle2 } from "lucide-react";
import { Course, COURSES } from "../data/courses";

interface DashboardProps {
  user: { username: string; email: string; registeredCourses: string[] };
  onSelectCourse: (courseId: string) => void;
  onNavigateToCourses: () => void;
}

export function Dashboard({ user, onSelectCourse, onNavigateToCourses }: DashboardProps) {
  // Filter registered courses
  const enrolledCourses = COURSES.filter((c) => user.registeredCourses.includes(c.id));
  
  // Recommend other courses that the user hasn't registered for yet
  const recommendedCourses = COURSES.filter((c) => !user.registeredCourses.includes(c.id)).slice(0, 3);

  // Dynamic statistics
  const totalWeeks = enrolledCourses.reduce((acc, curr) => acc + parseInt(curr.duration), 0);
  const sandboxInteractions = enrolledCourses.length * 3 + 2;

  return (
    <div className="bg-gray-50 min-h-screen py-10" id="student-portal-dashboard">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header Hero Banner inside dashboard */}
        <div className="bg-gradient-to-r from-indigo-900 to-indigo-800 rounded-2xl p-8 md:p-12 text-white shadow-lg mb-10 relative overflow-hidden">
          <div className="absolute right-0 bottom-0 top-0 w-1/3 opacity-10 hidden md:block">
            <GraduationCap className="w-full h-full object-contain" />
          </div>
          
          <div className="relative z-10 max-w-2xl">
            <span className="bg-indigo-500/20 border border-indigo-400/30 text-indigo-200 text-xs font-mono font-bold uppercase tracking-widest px-3 py-1 rounded-full">
              Student Dashboard Portal
            </span>
            <h1 className="font-sans font-extrabold text-3xl md:text-4xl mt-3 mb-2 tracking-tight">
              Welcome Back, {user.username}!
            </h1>
            <p className="text-indigo-200 font-sans text-sm md:text-base leading-relaxed opacity-90">
              Your self-paced academic growth track is fully active. Build software, practice sandbox queries, and progress towards earning your professional certifications!
            </p>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10" id="dashboard-stats-grid">
          {/* Card 1 */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div className="p-4 rounded-xl bg-indigo-50 text-indigo-600">
              <BookOpen className="h-6 w-6" />
            </div>
            <div>
              <span className="text-xs font-mono text-gray-400 uppercase tracking-wider block">My Courses</span>
              <span className="text-2xl font-sans font-extrabold text-gray-900 block mt-0.5">
                {enrolledCourses.length}
              </span>
            </div>
          </div>

          {/* Card 2 */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div className="p-4 rounded-xl bg-emerald-50 text-emerald-600">
              <Clock className="h-6 w-6" />
            </div>
            <div>
              <span className="text-xs font-mono text-gray-400 uppercase tracking-wider block">Est. Study Hours</span>
              <span className="text-2xl font-sans font-extrabold text-gray-900 block mt-0.5">
                {totalWeeks * 4 || 0} Hrs
              </span>
            </div>
          </div>

          {/* Card 3 */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div className="p-4 rounded-xl bg-purple-50 text-purple-600">
              <Code className="h-6 w-6" />
            </div>
            <div>
              <span className="text-xs font-mono text-gray-400 uppercase tracking-wider block">Sandbox Runs</span>
              <span className="text-2xl font-sans font-extrabold text-gray-900 block mt-0.5">
                {sandboxInteractions}
              </span>
            </div>
          </div>

          {/* Card 4 */}
          <div className="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4">
            <div className="p-4 rounded-xl bg-amber-50 text-amber-600">
              <Award className="h-6 w-6" />
            </div>
            <div>
              <span className="text-xs font-mono text-gray-400 uppercase tracking-wider block">Certificates Pending</span>
              <span className="text-2xl font-sans font-extrabold text-gray-900 block mt-0.5">
                {enrolledCourses.length > 0 ? enrolledCourses.length : 0}
              </span>
            </div>
          </div>
        </div>

        {/* Enrolled Courses list */}
        <div className="mb-12">
          <h2 className="font-sans font-bold text-2xl text-gray-900 mb-6">
            My Enrolled Curriculums
          </h2>

          {enrolledCourses.length === 0 ? (
            <div className="bg-white border border-dashed border-gray-200 rounded-2xl p-10 text-center max-w-lg mx-auto shadow-sm">
              <div className="bg-indigo-50 p-4 rounded-full text-indigo-600 inline-block mb-4">
                <BookOpen className="h-8 w-8" />
              </div>
              <h3 className="font-sans font-bold text-lg text-gray-900 mb-2">
                No active courses registered
              </h3>
              <p className="text-sm font-sans text-gray-500 mb-6 max-w-sm mx-auto leading-relaxed">
                Unlock learning pathways! Register for courses to access video lectures, course syllables, and live sandbox practice tools.
              </p>
              <button
                onClick={onNavigateToCourses}
                className="bg-indigo-600 hover:bg-indigo-700 text-white font-sans text-sm font-semibold px-6 py-3 rounded-xl shadow-md transition-all cursor-pointer focus:outline-none"
              >
                Browse 15+ Courses
              </button>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="dashboard-enrolled-grid">
              {enrolledCourses.map((course) => (
                <div
                  key={course.id}
                  className="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg hover:border-indigo-100 transition-all p-6 flex flex-col group"
                >
                  <div className="flex items-center gap-3 mb-4">
                    <img
                      src={course.image}
                      alt={course.title}
                      className="w-12 h-12 object-cover rounded-lg shrink-0"
                      referrerPolicy="no-referrer"
                    />
                    <div>
                      <span className="text-[10px] font-mono font-bold tracking-wider text-indigo-600 block uppercase">
                        {course.category}
                      </span>
                      <h3 className="font-sans font-bold text-sm text-gray-900 line-clamp-1 group-hover:text-indigo-600 transition-colors">
                        {course.title}
                      </h3>
                    </div>
                  </div>

                  <p className="text-xs font-sans text-gray-500 mb-4 line-clamp-2">
                    {course.description}
                  </p>

                  <div className="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                    <span className="text-[10px] font-mono font-bold text-gray-400">
                      WEEKLY: {course.duration.toUpperCase()}
                    </span>

                    <button
                      onClick={() => onSelectCourse(course.id)}
                      className="bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white px-4 py-2 rounded-xl text-xs font-semibold font-sans transition-all flex items-center gap-1 cursor-pointer focus:outline-none"
                    >
                      <span>Resume Portal</span>
                      <ArrowRight className="h-3.5 w-3.5" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Recommendations Section */}
        {recommendedCourses.length > 0 && (
          <div className="mb-8">
            <h2 className="font-sans font-bold text-xl text-gray-900 mb-6">
              Expand Your Learning Horizon
            </h2>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6" id="dashboard-recommendations-grid">
              {recommendedCourses.map((course) => (
                <div
                  key={course.id}
                  className="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-start gap-4 hover:shadow-md transition-all group"
                >
                  <img
                    src={course.image}
                    alt={course.title}
                    className="w-16 h-16 object-cover rounded-xl shrink-0 border border-gray-100"
                    referrerPolicy="no-referrer"
                  />
                  <div className="flex-grow">
                    <span className="text-[9px] font-mono font-bold text-indigo-500 block uppercase">
                      RECOMMENDED
                    </span>
                    <h4 className="font-sans font-bold text-xs text-gray-900 line-clamp-1 mt-0.5">
                      {course.title}
                    </h4>
                    <p className="text-[11px] font-sans text-gray-400 line-clamp-1 mt-0.5">
                      Instructor: {course.instructor}
                    </p>
                    <button
                      onClick={() => onSelectCourse(course.id)}
                      className="text-xs text-indigo-600 hover:text-indigo-800 font-sans font-semibold mt-2 inline-flex items-center gap-1 cursor-pointer focus:outline-none"
                    >
                      <span>Quick Enroll</span>
                      <ArrowRight className="h-3 w-3 group-hover:translate-x-0.5 transition-transform" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
