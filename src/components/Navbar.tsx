import React from "react";
import { GraduationCap, LogIn, LogOut, BookOpen, User } from "lucide-react";

interface NavbarProps {
  currentTab: string;
  setCurrentTab: (tab: string) => void;
  user: { username: string; email: string } | null;
  onLogout: () => void;
}

export function Navbar({ currentTab, setCurrentTab, user, onLogout }: NavbarProps) {
  const navItems = [
    { id: "home", label: "Home" },
    { id: "courses", label: "Courses" },
    { id: "about", label: "About" },
    { id: "contact", label: "Contact Us" },
  ];

  return (
    <nav className="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm" id="main-nav">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-20 items-center">
          {/* Logo */}
          <button
            onClick={() => setCurrentTab("home")}
            className="flex items-center gap-3 cursor-pointer group focus:outline-none"
            id="nav-logo"
          >
            <div className="bg-indigo-600 text-white p-2.5 rounded-xl shadow-md group-hover:bg-indigo-700 transition-colors">
              <GraduationCap className="h-6 w-6" />
            </div>
            <div className="text-left">
              <span className="font-sans font-bold text-xl tracking-tight text-gray-900 group-hover:text-indigo-600 transition-colors">
                E-Learning Hub
              </span>
              <p className="text-[10px] font-mono text-gray-500 uppercase tracking-widest leading-none">
                Academic Portal
              </p>
            </div>
          </button>

          {/* Nav Links */}
          <div className="hidden md:flex items-center gap-8" id="nav-links">
            {navItems.map((item) => {
              const isActive = currentTab === item.id;
              return (
                <button
                  key={item.id}
                  onClick={() => setCurrentTab(item.id)}
                  className={`relative font-sans text-sm font-medium py-2 transition-all cursor-pointer focus:outline-none ${
                    isActive
                      ? "text-indigo-600"
                      : "text-gray-600 hover:text-indigo-600"
                  }`}
                  id={`nav-item-${item.id}`}
                >
                  {item.label}
                  {isActive && (
                    <span className="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 rounded-full" />
                  )}
                </button>
              );
            })}
          </div>

          {/* Auth Button and Dashboard Actions */}
          <div className="flex items-center gap-4" id="nav-actions">
            {user ? (
              <div className="flex items-center gap-3">
                <button
                  onClick={() => setCurrentTab("dashboard")}
                  className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all focus:outline-none cursor-pointer border ${
                    currentTab === "dashboard"
                      ? "bg-indigo-50 border-indigo-200 text-indigo-700"
                      : "border-gray-200 text-gray-700 hover:bg-gray-50"
                  }`}
                  id="nav-btn-dashboard"
                >
                  <BookOpen className="h-4 w-4" />
                  <span>My Portal</span>
                </button>
                
                <div className="hidden sm:flex items-center gap-2 border-l pl-3 border-gray-200">
                  <div className="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-sans font-semibold text-sm">
                    {user.username.charAt(0).toUpperCase()}
                  </div>
                  <span className="text-xs font-sans font-medium text-gray-700 hidden lg:inline">
                    {user.username}
                  </span>
                </div>

                <button
                  onClick={onLogout}
                  className="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer focus:outline-none"
                  title="Log out"
                  id="nav-btn-logout"
                >
                  <LogOut className="h-5 w-5" />
                </button>
              </div>
            ) : (
              <div className="flex items-center gap-3">
                <button
                  onClick={() => setCurrentTab("login")}
                  className="font-sans text-sm font-medium text-gray-700 hover:text-indigo-600 px-3 py-2 cursor-pointer focus:outline-none"
                  id="nav-btn-login-text"
                >
                  Sign In
                </button>
                <button
                  onClick={() => setCurrentTab("register")}
                  className="bg-indigo-600 hover:bg-indigo-700 text-white font-sans text-sm font-medium px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer focus:outline-none"
                  id="nav-btn-register"
                >
                  <LogIn className="h-4 w-4" />
                  <span>Register</span>
                </button>
              </div>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
}
