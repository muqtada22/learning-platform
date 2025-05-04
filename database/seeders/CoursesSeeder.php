<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all professors
        $professors = User::where('role', 'professor')->get();
        
        // Create sample courses with specific names
        $courseTopics = [
            'Introduction to Computer Science',
            'Web Development Fundamentals',
            'Data Structures and Algorithms',
            'Machine Learning Basics',
            'Advanced Mathematics',
            'Physics 101',
            'Chemistry for Beginners',
            'Biology and Life Sciences',
            'Software Engineering Principles',
            'Database Design and Management',
            'Mobile Application Development',
            'Network Security',
            'Artificial Intelligence',
            'Human-Computer Interaction',
            'Operating Systems'
        ];
        
        // Create 1-3 courses for each professor
        foreach ($professors as $professor) {
            $numCourses = rand(1, 3);
            
            for ($i = 0; $i < $numCourses && !empty($courseTopics); $i++) {
                $topicIndex = array_rand($courseTopics);
                $topicName = $courseTopics[$topicIndex];
                unset($courseTopics[$topicIndex]);
                $courseTopics = array_values($courseTopics);
                
                Course::create([
                    'professor_id' => $professor->id,
                    'title' => $topicName,
                    'description' => fake()->paragraphs(rand(2, 4), true),
                    'total_hours' => rand(10, 60),
                ]);
            }
        }
        
        // Create additional random courses if needed
        $remainingCoursesToCreate = 15 - Course::count();
        if ($remainingCoursesToCreate > 0) {
            Course::factory()->count($remainingCoursesToCreate)->create();
        }
    }
}
