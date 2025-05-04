<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use App\Models\UserCourse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all students and courses
        $students = User::where('role', 'student')->get();
        $courses = Course::all();
        
        if ($courses->isEmpty()) {
            return; // No courses to enroll in
        }
        
        // Enroll each student in 1-5 random courses
        foreach ($students as $student) {
            // Determine how many courses this student will take
            $numCoursesToTake = rand(1, min(5, $courses->count()));
            
            // Get random course IDs
            $randomCourseIds = $courses->pluck('id')->shuffle()->take($numCoursesToTake);
            
            foreach ($randomCourseIds as $courseId) {
                // Randomly set enrollment date within the last 3 months
                $enrollmentDate = Carbon::now()->subDays(rand(0, 90));
                
                // Randomly set if it's a favorite course (20% chance)
                $isFavorite = (rand(1, 100) <= 20);
                
                // Calculate a random last viewed date after enrollment
                $lastViewedAt = $enrollmentDate->copy()->addDays(rand(0, Carbon::now()->diffInDays($enrollmentDate)));
                
                UserCourse::create([
                    'user_id' => $student->id,
                    'course_id' => $courseId,
                    'is_favorite' => $isFavorite,
                    'added_at' => $enrollmentDate,
                    'last_viewed_at' => $lastViewedAt,
                ]);
            }
        }
        
        // Make sure the test student is enrolled in at least 3 courses if they exist
        $testStudent = User::where('email', 'student@example.com')->first();
        if ($testStudent && $courses->count() >= 3) {
            $enrolledCount = UserCourse::where('user_id', $testStudent->id)->count();
            $additionalNeeded = max(0, 3 - $enrolledCount);
            
            if ($additionalNeeded > 0) {
                // Get courses the test student is not enrolled in yet
                $enrolledCourseIds = UserCourse::where('user_id', $testStudent->id)
                    ->pluck('course_id')
                    ->toArray();
                
                $availableCourses = Course::whereNotIn('id', $enrolledCourseIds)
                    ->inRandomOrder()
                    ->take($additionalNeeded)
                    ->get();
                
                foreach ($availableCourses as $course) {
                    UserCourse::create([
                        'user_id' => $testStudent->id,
                        'course_id' => $course->id,
                        'is_favorite' => true,
                        'added_at' => Carbon::now()->subDays(rand(1, 30)),
                        'last_viewed_at' => Carbon::now()->subDays(rand(0, 7)),
                    ]);
                }
            }
        }
    }
}
