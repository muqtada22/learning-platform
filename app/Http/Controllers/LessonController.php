<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    /**
     * Display a listing of the lessons for a course.
     */
    public function index(Course $course)
    {
        $lessons = $course->lessons;
        return view('lessons.index', compact('course', 'lessons'));
    }

    /**
     * Show the form for creating a new lesson.
     */
    public function create(Course $course)
    {
        $user = Auth::user();
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Only the course professor can add lessons.');
        }
        
        return view('lessons.create', compact('course'));
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(Request $request, Course $course)
    {
        $user = Auth::user();
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Only the course professor can add lessons.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);
        
        $lesson = $course->lessons()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);
        
        return redirect()->route('courses.lessons.show', [$course, $lesson])
            ->with('success', 'Lesson created successfully.');
    }

    /**
     * Display the specified lesson.
     */
    public function show(Course $course, Lesson $lesson)
    {
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        $questions = $lesson->questions;
        
        return view('lessons.show', compact('course', 'lesson', 'questions'));
    }

    /**
     * Show the form for editing the specified lesson.
     */
    public function edit(Course $course, Lesson $lesson)
    {
        $user = Auth::user();
        
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.lessons.show', [$course, $lesson])
                ->with('error', 'Only the course professor can edit lessons.');
        }
        
        return view('lessons.edit', compact('course', 'lesson'));
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $user = Auth::user();
        
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.lessons.show', [$course, $lesson])
                ->with('error', 'Only the course professor can edit lessons.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);
        
        $lesson->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);
        
        return redirect()->route('courses.lessons.show', [$course, $lesson])
            ->with('success', 'Lesson updated successfully.');
    }

    /**
     * Remove the specified lesson from storage.
     */
    public function destroy(Course $course, Lesson $lesson)
    {
        $user = Auth::user();
        
        // Ensure the lesson belongs to the course
        if ($lesson->course_id !== $course->id) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Lesson not found in this course.');
        }
        
        // Check if the authenticated user is the professor of the course
        if ($user->role !== 'professor' || $course->professor_id !== $user->id) {
            return redirect()->route('courses.lessons.show', [$course, $lesson])
                ->with('error', 'Only the course professor can delete lessons.');
        }
        
        $lesson->delete();
        
        return redirect()->route('courses.show', $course)
            ->with('success', 'Lesson deleted successfully.');
    }
}
