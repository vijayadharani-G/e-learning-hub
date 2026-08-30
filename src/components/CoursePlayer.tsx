import React, { useState } from "react";
import { BookOpen, Video, Code, CheckCircle2, ChevronRight, Play, RotateCcw, Award } from "lucide-react";
import { Course } from "../data/courses";

interface CoursePlayerProps {
  course: Course;
  onBack: () => void;
}

export function CoursePlayer({ course, onBack }: CoursePlayerProps) {
  const [activeTab, setActiveTab] = useState<"overview" | "syllabus" | "code">("overview");
  const [activeWeekIndex, setActiveWeekIndex] = useState<number>(0);
  
  // Interactive Sandbox Code State
  const [sandboxCode, setSandboxCode] = useState<string>(
    course.explanation.codeExample || "<!-- Type HTML/CSS code here to test -->\n<h1>Hello Academy!</h1>"
  );
  const [runResultUrl, setRunResultUrl] = useState<string>("");
  const [isRunning, setIsRunning] = useState<boolean>(false);

  const handleRunCode = () => {
    setIsRunning(true);
    // Create a data blob with HTML type and set it to iframe src
    setTimeout(() => {
      const blob = new Blob([sandboxCode], { type: "text/html" });
      const url = URL.createObjectURL(blob);
      setRunResultUrl(url);
      setIsRunning(false);
    }, 400);
  };

  const handleResetCode = () => {
    setSandboxCode(course.explanation.codeExample || "");
    setRunResultUrl("");
  };

  return (
    <div className="bg-gray-50 min-h-screen py-8" id="course-player-container">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Back and Breadcrumb */}
        <div className="flex items-center justify-between mb-6">
          <button
            onClick={onBack}
            className="flex items-center gap-2 text-indigo-600 hover:text-indigo-800 font-sans text-sm font-medium focus:outline-none cursor-pointer"
            id="player-back-btn"
          >
            <ChevronRight className="h-4 w-4 rotate-180" />
            <span>Back to Portal</span>
          </button>
          
          <div className="flex items-center gap-2 text-xs font-mono text-gray-500">
            <span>COURSES</span>
            <span>/</span>
            <span className="text-gray-900 font-medium">{course.title.toUpperCase()}</span>
          </div>
        </div>

        {/* Video Player & Chapters Split Layout */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          {/* Left Column: Video screen & Lecture Meta */}
          <div className="lg:col-span-2 flex flex-col">
            {/* Video Box */}
            <div className="bg-black aspect-video rounded-2xl overflow-hidden shadow-lg border border-gray-950 relative group">
              <iframe
                src={course.videoUrl}
                title={course.title}
                className="w-full h-full border-none"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowFullScreen
                referrerPolicy="no-referrer"
                id="player-video-iframe"
              ></iframe>
            </div>

            {/* Lecture title */}
            <div className="mt-5 mb-3">
              <span className="text-xs font-mono font-bold uppercase tracking-widest text-indigo-600">
                ACTIVE LECTURE • WEEK {activeWeekIndex + 1}
              </span>
              <h1 className="font-sans font-bold text-2xl text-gray-900 mt-1 leading-tight">
                {course.explanation.syllabus[activeWeekIndex]?.topic || course.title}
              </h1>
              <p className="text-sm font-sans text-gray-500 mt-1">
                Course: <span className="font-medium text-gray-800">{course.title}</span> • Instructor: <span className="font-medium text-gray-800">{course.instructor}</span>
              </p>
            </div>
          </div>

          {/* Right Column: Dynamic Chapters Outline */}
          <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col h-full">
            <div className="flex items-center gap-2 mb-4 pb-4 border-b border-gray-50">
              <BookOpen className="h-5 w-5 text-indigo-600" />
              <h3 className="font-sans font-bold text-base text-gray-900">
                Course Syllabus
              </h3>
            </div>

            <div className="space-y-3 overflow-y-auto flex-grow max-h-[300px] pr-1">
              {course.explanation.syllabus.map((item, index) => {
                const isActive = activeWeekIndex === index;
                return (
                  <button
                    key={index}
                    onClick={() => setActiveWeekIndex(index)}
                    className={`w-full text-left p-3.5 rounded-xl border transition-all flex items-start gap-3 focus:outline-none cursor-pointer ${
                      isActive
                        ? "bg-indigo-50/70 border-indigo-200 text-indigo-900"
                        : "bg-white border-gray-100 hover:border-gray-200 text-gray-700"
                    }`}
                    id={`syllabus-item-${index}`}
                  >
                    <div className={`mt-0.5 p-1 rounded-md ${isActive ? "bg-indigo-600 text-white" : "bg-gray-100 text-gray-400"}`}>
                      <Video className="h-3.5 w-3.5" />
                    </div>
                    <div className="flex-grow">
                      <span className="text-[10px] font-mono font-bold tracking-wider opacity-70 block">
                        {item.week.toUpperCase()}
                      </span>
                      <span className="text-xs font-sans font-semibold block mt-0.5 line-clamp-1">
                        {item.topic}
                      </span>
                    </div>
                  </button>
                );
              })}
            </div>

            <div className="mt-6 pt-4 border-t border-gray-100 bg-gray-50 -mx-6 -mb-6 p-6 rounded-b-2xl">
              <div className="flex items-center gap-3">
                <div className="bg-indigo-100 text-indigo-700 p-2 rounded-lg">
                  <Award className="h-5 w-5" />
                </div>
                <div>
                  <span className="text-[10px] font-mono font-bold tracking-widest text-indigo-700 uppercase">COURSE TARGET</span>
                  <p className="text-xs font-sans text-gray-600 font-medium">Earn professional accredited certificate on completion</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* Tab Selection */}
        <div className="bg-white border-b border-gray-100 rounded-t-2xl p-2 flex items-center gap-2 shadow-sm">
          {(["overview", "syllabus", "code"] as const).map((tab) => {
            const labels = {
              overview: "Overview & Objectives",
              syllabus: "Syllabus Details",
              code: "Interactive Code Lab"
            };
            const icons = {
              overview: BookOpen,
              syllabus: Video,
              code: Code
            };
            const Icon = icons[tab];
            const isActive = activeTab === tab;
            return (
              <button
                key={tab}
                onClick={() => setActiveTab(tab)}
                className={`flex items-center gap-2 px-5 py-3 rounded-xl font-sans text-sm font-medium transition-all cursor-pointer focus:outline-none ${
                  isActive
                    ? "bg-indigo-600 text-white shadow-md shadow-indigo-100"
                    : "text-gray-600 hover:bg-gray-50 hover:text-indigo-600"
                }`}
                id={`player-tab-${tab}`}
              >
                <Icon className="h-4 w-4" />
                <span>{labels[tab]}</span>
              </button>
            );
          })}
        </div>

        {/* Tab Body Contents */}
        <div className="bg-white border border-t-0 border-gray-100 rounded-b-2xl p-8 shadow-sm mb-12">
          {activeTab === "overview" && (
            <div className="space-y-6" id="tab-content-overview">
              <div>
                <h3 className="font-sans font-bold text-lg text-gray-900 mb-2">
                  Course Overview
                </h3>
                <p className="text-gray-600 font-sans text-sm leading-relaxed max-w-4xl">
                  {course.explanation.overview}
                </p>
              </div>

              <div>
                <h3 className="font-sans font-bold text-lg text-gray-900 mb-3">
                  What you will master
                </h3>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-3 max-w-4xl">
                  {course.explanation.objectives.map((obj, i) => (
                    <div key={i} className="flex items-start gap-2 text-sm text-gray-700 font-sans">
                      <CheckCircle2 className="h-5 w-5 text-emerald-500 mt-0.5 shrink-0" />
                      <span>{obj}</span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {activeTab === "syllabus" && (
            <div className="space-y-4" id="tab-content-syllabus">
              <h3 className="font-sans font-bold text-lg text-gray-900 mb-4">
                Detailed Weekly Curriculum
              </h3>
              <div className="space-y-4 max-w-4xl">
                {course.explanation.syllabus.map((item, i) => (
                  <div
                    key={i}
                    onClick={() => setActiveWeekIndex(i)}
                    className={`p-4 rounded-xl border cursor-pointer transition-all ${
                      activeWeekIndex === i
                        ? "bg-indigo-50/50 border-indigo-200"
                        : "bg-white border-gray-100 hover:border-gray-200"
                    }`}
                  >
                    <div className="flex items-center justify-between mb-1">
                      <span className="text-xs font-mono font-bold text-indigo-600 uppercase">
                        {item.week}
                      </span>
                      {activeWeekIndex === i && (
                        <span className="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase font-mono">
                          Viewing
                        </span>
                      )}
                    </div>
                    <h4 className="font-sans font-bold text-sm text-gray-900">{item.topic}</h4>
                    <p className="text-xs font-sans text-gray-500 mt-1">{item.details}</p>
                  </div>
                ))}
              </div>
            </div>
          )}

          {activeTab === "code" && (
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8" id="tab-content-code">
              {/* Code input field */}
              <div className="flex flex-col">
                <div className="flex items-center justify-between mb-3 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                  <div className="flex items-center gap-2">
                    <span className="h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                    <span className="text-xs font-mono font-semibold text-gray-700 uppercase">Live Code Sandbox</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <button
                      onClick={handleResetCode}
                      className="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-white rounded-md transition-all cursor-pointer focus:outline-none"
                      title="Reset Sandbox"
                    >
                      <RotateCcw className="h-4 w-4" />
                    </button>
                    <button
                      onClick={handleRunCode}
                      disabled={isRunning}
                      className="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium font-sans shadow-md flex items-center gap-1.5 cursor-pointer disabled:opacity-50 focus:outline-none"
                    >
                      <Play className="h-3 w-3 fill-current" />
                      <span>{isRunning ? "Compiling..." : "Run Code"}</span>
                    </button>
                  </div>
                </div>

                <div className="relative border border-gray-200 rounded-xl overflow-hidden font-mono text-sm bg-gray-950 text-gray-100 p-4">
                  <textarea
                    value={sandboxCode}
                    onChange={(e) => setSandboxCode(e.target.value)}
                    rows={12}
                    className="w-full bg-transparent border-none text-gray-100 focus:ring-0 outline-none resize-none font-mono text-sm leading-relaxed"
                    spellCheck="false"
                    id="sandbox-textarea"
                  />
                </div>
                <p className="text-[11px] font-sans text-gray-500 mt-2">
                  💡 Edit the HTML above. Standard CSS inside &lt;style&gt; tags will also compile dynamically.
                </p>
              </div>

              {/* Code output render preview */}
              <div className="flex flex-col">
                <div className="mb-3 bg-gray-50 p-2.5 rounded-xl border border-gray-100 flex items-center gap-2">
                  <span className="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                  <span className="text-xs font-mono font-semibold text-gray-700 uppercase">Live Browser Result</span>
                </div>

                <div className="border border-gray-200 rounded-xl overflow-hidden h-[290px] bg-white relative flex flex-col shadow-inner">
                  {runResultUrl ? (
                    <iframe
                      src={runResultUrl}
                      title="Sandbox Output"
                      className="w-full flex-grow border-none bg-white p-4"
                      referrerPolicy="no-referrer"
                      id="sandbox-output-iframe"
                    />
                  ) : (
                    <div className="m-auto text-center p-8">
                      <div className="bg-indigo-50 text-indigo-600 p-3 rounded-full inline-block mb-3">
                        <Code className="h-6 w-6" />
                      </div>
                      <h4 className="font-sans font-bold text-sm text-gray-900">Sandbox Empty</h4>
                      <p className="text-xs font-sans text-gray-500 mt-1 max-w-[280px]">
                        Click the &quot;Run Code&quot; button above to render your styled markup in this preview pane!
                      </p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
