import React from "react";
import { Star, Users, Clock, ArrowRight, Award } from "lucide-react";
import { Course } from "../data/courses";

interface CourseCardProps {
  key?: string;
  course: Course;
  onSelect: (courseId: string) => void;
  isRegistered: boolean;
  onRegisterQuick?: (courseId: string) => void;
}

export function CourseCard({ course, onSelect, isRegistered, onRegisterQuick }: CourseCardProps) {
  // Level color indicators
  const levelColors = {
    Beginner: "bg-green-50 text-green-700 border-green-100",
    Intermediate: "bg-blue-50 text-blue-700 border-blue-100",
    Advanced: "bg-purple-50 text-purple-700 border-purple-100",
  };

  return (
    <div
      className="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-indigo-100 transition-all duration-300 flex flex-col overflow-hidden h-full group"
      id={`course-card-${course.id}`}
    >
      {/* Course Image */}
      <div className="relative h-48 w-full overflow-hidden bg-gray-100">
        <img
          src={course.image}
          alt={course.title}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          referrerPolicy="no-referrer"
          id={`course-card-img-${course.id}`}
        />
        {/* Category Chip */}
        <span className="absolute top-4 left-4 bg-white/95 backdrop-blur-sm text-gray-900 font-sans text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm border border-gray-100">
          {course.category}
        </span>
        {/* Enrolled Status Overlay */}
        {isRegistered && (
          <span className="absolute top-4 right-4 bg-indigo-600 text-white font-sans text-[10px] uppercase font-bold tracking-wider px-2.5 py-1 rounded-md shadow-md flex items-center gap-1">
            <Award className="h-3 w-3" />
            <span>Enrolled</span>
          </span>
        )}
      </div>

      {/* Course Body */}
      <div className="p-6 flex flex-col flex-grow">
        {/* Ratings and Stats */}
        <div className="flex items-center gap-3 mb-3">
          <div className="flex items-center gap-1 text-amber-500 bg-amber-50 px-2 py-0.5 rounded-md text-xs font-semibold">
            <Star className="h-3.5 w-3.5 fill-current" />
            <span>{course.rating.toFixed(1)}</span>
          </div>
          <div className="flex items-center gap-1 text-gray-500 text-xs">
            <Users className="h-3.5 w-3.5" />
            <span>{course.studentsEnrolled.toLocaleString()} students</span>
          </div>
        </div>

        {/* Title */}
        <h3 className="font-sans font-bold text-lg text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors line-clamp-1">
          {course.title}
        </h3>

        {/* Instructor */}
        <p className="text-xs font-sans text-gray-500 mb-3">
          Instructed by <span className="font-medium text-gray-700">{course.instructor}</span>
        </p>

        {/* Description */}
        <p className="text-sm font-sans text-gray-600 mb-5 line-clamp-2 leading-relaxed">
          {course.description}
        </p>

        {/* Meta Info footer */}
        <div className="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
          <div className="flex items-center gap-4">
            <div className="flex items-center gap-1 text-gray-500 text-xs font-medium">
              <Clock className="h-3.5 w-3.5" />
              <span>{course.duration}</span>
            </div>
            <span className={`text-[10px] font-sans font-bold uppercase border px-2.5 py-0.5 rounded-full ${levelColors[course.level]}`}>
              {course.level}
            </span>
          </div>

          <button
            onClick={() => onSelect(course.id)}
            className="text-indigo-600 hover:text-indigo-700 font-sans text-sm font-semibold flex items-center gap-1 cursor-pointer focus:outline-none"
            id={`course-card-btn-${course.id}`}
          >
            <span>Learn More</span>
            <ArrowRight className="h-4 w-4 group-hover:translate-x-1 transition-transform" />
          </button>
        </div>
      </div>
    </div>
  );
}
