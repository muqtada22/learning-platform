<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LessonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all courses
        $courses = Course::all();
        
        if ($courses->isEmpty()) {
            return; // No courses to add lessons to
        }
        
        // Create 3-8 lessons for each course
        foreach ($courses as $course) {
            $numLessons = rand(3, 8);
            
            // Generate lesson titles based on the course subject
            $lessonTitles = $this->generateLessonTitles($course->title, $numLessons);
            
            // Create lessons with these titles
            for ($i = 0; $i < $numLessons; $i++) {
                Lesson::create([
                    'course_id' => $course->id,
                    'title' => $lessonTitles[$i],
                    'content' => fake()->paragraphs(rand(5, 10), true),
                ]);
            }
        }
    }
    
    /**
     * Generate appropriate lesson titles based on course subject.
     */
    private function generateLessonTitles(string $courseTitle, int $count): array
    {
        $titles = [];
        
        // Common lesson structure
        $commonPrefixes = [
            'Introduction to ', 
            'Understanding ', 
            'Fundamentals of ', 
            'Advanced ', 
            'Working with ', 
            'Exploring ', 
            'Mastering ', 
            'Practical ',
            'Overview of ',
            'Key Concepts in '
        ];
        
        // Specialized prefixes for specific course types
        $specific = [
            'Computer Science' => [
                'Introduction to Programming',
                'Data Types and Variables',
                'Loops and Control Structures',
                'Functions and Methods',
                'Object-Oriented Programming',
                'Data Structures Overview',
                'Algorithms and Complexity',
                'Software Development Lifecycle'
            ],
            'Web Development' => [
                'HTML Basics',
                'CSS Styling',
                'JavaScript Fundamentals',
                'DOM Manipulation',
                'Frontend Frameworks',
                'Backend Development',
                'API Design',
                'Database Integration'
            ],
            'Data Structures' => [
                'Arrays and Lists',
                'Stacks and Queues',
                'Trees and Graphs',
                'Hash Tables',
                'Sorting Algorithms',
                'Searching Algorithms',
                'Algorithm Complexity',
                'Problem Solving Techniques'
            ],
            'Machine Learning' => [
                'Supervised Learning',
                'Unsupervised Learning',
                'Neural Networks',
                'Deep Learning',
                'Natural Language Processing',
                'Computer Vision',
                'Reinforcement Learning',
                'Model Evaluation'
            ],
            'Mathematics' => [
                'Linear Algebra',
                'Calculus Fundamentals',
                'Statistics and Probability',
                'Number Theory',
                'Differential Equations',
                'Discrete Mathematics',
                'Mathematical Proofs',
                'Applied Mathematics'
            ],
            'Physics' => [
                'Mechanics',
                'Thermodynamics',
                'Electricity and Magnetism',
                'Waves and Optics',
                'Modern Physics',
                'Quantum Mechanics',
                'Relativity',
                'Astrophysics'
            ]
        ];
        
        // Check if the course title contains any keywords we have specific lessons for
        $matchFound = false;
        foreach ($specific as $keyword => $specificTitles) {
            if (str_contains(strtolower($courseTitle), strtolower($keyword))) {
                // Use specific titles for this course type
                $titles = array_slice($specificTitles, 0, $count);
                $matchFound = true;
                break;
            }
        }
        
        // If no match found, generate generic titles
        if (!$matchFound) {
            for ($i = 0; $i < $count; $i++) {
                $prefix = $commonPrefixes[array_rand($commonPrefixes)];
                $subject = fake()->words(rand(2, 4), true);
                $titles[] = "Lesson " . ($i + 1) . ": " . $prefix . $subject;
            }
        }
        
        // If we don't have enough titles, add generic ones
        while (count($titles) < $count) {
            $titles[] = "Lesson " . (count($titles) + 1) . ": " . fake()->sentence(rand(3, 6));
        }
        
        return $titles;
    }
}
