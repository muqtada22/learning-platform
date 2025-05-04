<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\UserActivity;
use App\Models\UserBadge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'professor') {
            return $this->professorDashboard($user);
        }
        
        return $this->studentDashboard($user);
    }
    
    /**
     * Display the student dashboard.
     */
    private function studentDashboard($user)
    {
        // Get courses the student is enrolled in
        $courses = $user->enrolledCourses()
            ->withCount(['lessons', 'lessons.questions'])
            ->orderBy('last_viewed_at', 'desc')
            ->take(5)
            ->get();
        
        // Get favorite courses
        $favoriteCourses = $user->enrolledCourses()
            ->wherePivot('is_favorite', true)
            ->take(3)
            ->get();
        
        // Get user progress stats
        $progressStats = DB::table('user_progress')
            ->select(
                DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_answers'),
                DB::raw('COUNT(*) as total_answers')
            )
            ->where('user_id', $user->id)
            ->first();
        
        // Get activity data for the last 7 days
        $activityData = UserActivity::where('user_id', $user->id)
            ->orderBy('activity_date', 'desc')
            ->take(7)
            ->get();
        
        // Get badges earned
        $badges = $user->badges()->get();
        
        return view('dashboard.student', compact(
            'user',
            'courses',
            'favoriteCourses',
            'progressStats',
            'activityData',
            'badges'
        ));
    }
    
    /**
     * Display the professor dashboard.
     */
    private function professorDashboard($user)
    {
        // Get courses the professor is teaching
        $courses = $user->taughtCourses()
            ->withCount(['lessons', 'students'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get total students enrolled in professor's courses
        $totalStudents = DB::table('user_courses')
            ->join('courses', 'user_courses.course_id', '=', 'courses.id')
            ->where('courses.professor_id', $user->id)
            ->count(DB::raw('DISTINCT user_courses.user_id'));
        
        // Get question statistics
        $questionStats = DB::table('questions')
            ->join('lessons', 'questions.lesson_id', '=', 'lessons.id')
            ->join('courses', 'lessons.course_id', '=', 'courses.id')
            ->select(DB::raw('COUNT(questions.id) as total_questions'))
            ->where('courses.professor_id', $user->id)
            ->first();
        
        return view('dashboard.professor', compact(
            'user',
            'courses',
            'totalStudents',
            'questionStats'
        ));
    }
    
    /**
     * Record user activity.
     */
    public function recordActivity(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            return response()->json(['error' => 'Only students can record activity.'], 403);
        }
        
        $validated = $request->validate([
            'time_spent_minutes' => 'required|integer|min:1',
        ]);
        
        $today = now()->toDateString();
        
        // Update or create activity record for today
        $activity = UserActivity::firstOrNew([
            'user_id' => $user->id,
            'activity_date' => $today,
        ]);
        
        $activity->time_spent_minutes += $validated['time_spent_minutes'];
        $activity->is_active_day = true;
        
        // Calculate streak
        $yesterday = now()->subDay()->toDateString();
        $yesterdayActivity = UserActivity::where('user_id', $user->id)
            ->where('activity_date', $yesterday)
            ->where('is_active_day', true)
            ->first();
            
        if ($yesterdayActivity) {
            $activity->current_streak = $yesterdayActivity->current_streak + 1;
        } else {
            $activity->current_streak = 1;
        }
        
        $activity->save();
        
        // Award streak badges if applicable
        $this->checkAndAwardStreakBadges($user, $activity->current_streak);
        
        return response()->json([
            'success' => true,
            'current_streak' => $activity->current_streak,
        ]);
    }
    
    /**
     * Check for and award streak badges.
     */
    private function checkAndAwardStreakBadges($user, $currentStreak)
    {
        $streakBadges = [
            3 => 1, // 3-day streak = badge ID 1
            7 => 2, // 7-day streak = badge ID 2
            30 => 3, // 30-day streak = badge ID 3
        ];
        
        foreach ($streakBadges as $streakDays => $badgeId) {
            if ($currentStreak >= $streakDays) {
                // Check if user already has this badge
                $existingBadge = UserBadge::where('user_id', $user->id)
                    ->where('badge_id', $badgeId)
                    ->first();
                    
                if (!$existingBadge) {
                    // Award the badge and XP
                    UserBadge::create([
                        'user_id' => $user->id,
                        'badge_id' => $badgeId,
                        'awarded_at' => now(),
                    ]);
                    
                    // Add XP for the badge
                    $badge = DB::table('badges')->find($badgeId);
                    if ($badge) {
                        $user->xp_points += $badge->xp_reward;
                        $user->save();
                    }
                }
            }
        }
    }
}
