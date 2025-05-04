<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isProfessor()) {
            $courses = $user->taughtCourses;
        } else {
            $courses = $user->enrolledCourses;
        }
        
        return view('courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     */
    public function create()
    {
        if (!Auth::user()->isProfessor()) {
            return redirect()->route('courses.index')
                ->with('error', 'Only professors can create courses.');
        }
        
        return view('courses.create');
    }

    /**
     * Store a newly created course in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->isProfessor()) {
            return redirect()->route('courses.index')
                ->with('error', 'Only professors can create courses.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_hours' => 'nullable|integer',
            'uploaded_file' => 'nullable|file|max:10240', // 10MB max
        ]);
        
        $filePath = null;
        if ($request->hasFile('uploaded_file')) {
            $filePath = $request->file('uploaded_file')->store('course_files', 'public');
        }
        
        $course = Course::create([
            'professor_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'total_hours' => $validated['total_hours'],
            'uploaded_file_path' => $filePath,
        ]);
        
        return redirect()->route('courses.show', $course)
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        $user = Auth::user();
        $lessons = $course->lessons;
        
        // If student, record view time
        if ($user->isStudent()) {
            UserCourse::updateOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                ['last_viewed_at' => now()]
            );
        }
        
        return view('courses.show', compact('course', 'lessons'));
    }

    /**
     * Show the form for editing the specified course.
     */
    public function edit(Course $course)
    {
        $user = Auth::user();
        
        if (!$user->isProfessor() || $course->professor_id !== $user->id) {
            return redirect()->route('courses.index')
                ->with('error', 'You can only edit your own courses.');
        }
        
        return view('courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     */
    public function update(Request $request, Course $course)
    {
        $user = Auth::user();
        
        if (!$user->isProfessor() || $course->professor_id !== $user->id) {
            return redirect()->route('courses.index')
                ->with('error', 'You can only edit your own courses.');
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_hours' => 'nullable|integer',
            'uploaded_file' => 'nullable|file|max:10240', // 10MB max
        ]);
        
        $filePath = $course->uploaded_file_path;
        if ($request->hasFile('uploaded_file')) {
            // Delete old file if exists
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('uploaded_file')->store('course_files', 'public');
        }
        
        $course->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'total_hours' => $validated['total_hours'],
            'uploaded_file_path' => $filePath,
        ]);
        
        return redirect()->route('courses.show', $course)
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     */
    public function destroy(Course $course)
    {
        $user = Auth::user();
        
        if (!$user->isProfessor() || $course->professor_id !== $user->id) {
            return redirect()->route('courses.index')
                ->with('error', 'You can only delete your own courses.');
        }
        
        // Delete file if exists
        if ($course->uploaded_file_path) {
            Storage::disk('public')->delete($course->uploaded_file_path);
        }
        
        $course->delete();
        
        return redirect()->route('courses.index')
            ->with('success', 'Course deleted successfully.');
    }
    
    /**
     * Enroll the authenticated user in a course.
     */
    public function enroll(Course $course)
    {
        $user = Auth::user();
        
        if ($user->isProfessor()) {
            return redirect()->route('courses.index')
                ->with('error', 'Professors cannot enroll in courses.');
        }
        
        // Check if already enrolled
        if ($user->enrolledCourses()->where('course_id', $course->id)->exists()) {
            return redirect()->route('courses.show', $course)
                ->with('info', 'You are already enrolled in this course.');
        }
        
        // Enroll user
        UserCourse::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'added_at' => now(),
        ]);
        
        return redirect()->route('courses.show', $course)
            ->with('success', 'Successfully enrolled in course.');
    }
    
    /**
     * Toggle favorite status for a course.
     */
    public function toggleFavorite(Course $course)
    {
        $user = Auth::user();
        
        if ($user->isProfessor()) {
            return redirect()->route('courses.index')
                ->with('error', 'Professors cannot favorite courses.');
        }
        
        $userCourse = UserCourse::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
            
        if (!$userCourse) {
            return redirect()->route('courses.index')
                ->with('error', 'You must be enrolled in a course to favorite it.');
        }
        
        $userCourse->update([
            'is_favorite' => !$userCourse->is_favorite,
        ]);
        
        $message = $userCourse->is_favorite ? 'Added to favorites.' : 'Removed from favorites.';
        
        return redirect()->route('courses.show', $course)
            ->with('success', $message);
    }
}
