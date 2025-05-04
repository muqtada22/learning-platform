<?php

namespace Database\Seeders;

use App\Models\AnswerOption;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all lessons
        $lessons = Lesson::all();
        
        if ($lessons->isEmpty()) {
            return; // No lessons to add questions to
        }
        
        // Create 2-5 questions for each lesson
        foreach ($lessons as $lesson) {
            $numQuestions = rand(2, 5);
            
            for ($i = 0; $i < $numQuestions; $i++) {
                // Determine question type
                $questionType = $this->getRandomQuestionType();
                
                // Create question
                $question = Question::create([
                    'lesson_id' => $lesson->id,
                    'question_text' => $this->generateQuestionText($lesson->title, $questionType),
                    'question_type' => $questionType,
                ]);
                
                // Create answer options based on question type
                $this->createAnswerOptions($question, $questionType);
            }
        }
    }
    
    /**
     * Generate a random question type.
     */
    private function getRandomQuestionType(): string
    {
        $types = [
            'multiple_choice' => 60, // 60% chance
            'true_false' => 30,      // 30% chance
            'fill_in_blank' => 5,    // 5% chance
            'matching' => 5,         // 5% chance
        ];
        
        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($types as $type => $probability) {
            $cumulative += $probability;
            if ($rand <= $cumulative) {
                return $type;
            }
        }
        
        return 'multiple_choice'; // Default fallback
    }
    
    /**
     * Generate appropriate question text based on lesson title and question type.
     */
    private function generateQuestionText(string $lessonTitle, string $questionType): string
    {
        $lessonKeywords = explode(' ', $lessonTitle);
        $keyword = $lessonKeywords[array_rand($lessonKeywords)];
        
        $questionTemplates = [
            'multiple_choice' => [
                "What is the best definition of {$keyword}?",
                "Which of the following is true about {$keyword}?",
                "What is the main purpose of {$keyword}?",
                "How does {$keyword} relate to " . fake()->word() . "?",
                "Which concept is most closely associated with {$keyword}?",
            ],
            'true_false' => [
                "True or False: {$keyword} is " . fake()->sentence(),
                "True or False: The primary function of {$keyword} is to " . fake()->sentence(),
                "True or False: {$keyword} was first developed by " . fake()->name(),
                "True or False: {$keyword} is used primarily in " . fake()->word() . " applications",
            ],
            'fill_in_blank' => [
                "The process of {$keyword} involves _______.",
                "{$keyword} is defined as _______.",
                "The main component of {$keyword} is _______.",
                "{$keyword} was developed to solve the problem of _______.",
            ],
            'matching' => [
                "Match the following {$keyword} concepts with their definitions:",
                "Match these {$keyword} terms with their examples:",
                "Connect each {$keyword} technique with its appropriate use case:",
                "Pair each {$keyword} component with its function:",
            ],
        ];
        
        $templates = $questionTemplates[$questionType];
        return $templates[array_rand($templates)];
    }
    
    /**
     * Create answer options for a question based on its type.
     */
    private function createAnswerOptions(Question $question, string $questionType): void
    {
        switch ($questionType) {
            case 'multiple_choice':
                $this->createMultipleChoiceOptions($question);
                break;
            case 'true_false':
                $this->createTrueFalseOptions($question);
                break;
            case 'fill_in_blank':
                $this->createFillInBlankOptions($question);
                break;
            case 'matching':
                $this->createMatchingOptions($question);
                break;
        }
    }
    
    /**
     * Create options for multiple choice questions.
     */
    private function createMultipleChoiceOptions(Question $question): void
    {
        $numOptions = rand(3, 5); // 3-5 options for multiple choice
        $correctOption = rand(0, $numOptions - 1); // Randomly select the correct answer
        
        for ($i = 0; $i < $numOptions; $i++) {
            AnswerOption::create([
                'question_id' => $question->id,
                'option_text' => fake()->sentence(),
                'is_correct' => ($i == $correctOption),
            ]);
        }
    }
    
    /**
     * Create options for true/false questions.
     */
    private function createTrueFalseOptions(Question $question): void
    {
        $isTrue = fake()->boolean();
        
        AnswerOption::create([
            'question_id' => $question->id,
            'option_text' => 'True',
            'is_correct' => $isTrue,
        ]);
        
        AnswerOption::create([
            'question_id' => $question->id,
            'option_text' => 'False',
            'is_correct' => !$isTrue,
        ]);
    }
    
    /**
     * Create options for fill in the blank questions.
     */
    private function createFillInBlankOptions(Question $question): void
    {
        // Create 1 correct answer and 2-3 incorrect ones
        AnswerOption::create([
            'question_id' => $question->id,
            'option_text' => fake()->sentence(3),
            'is_correct' => true,
        ]);
        
        $numIncorrect = rand(2, 3);
        
        for ($i = 0; $i < $numIncorrect; $i++) {
            AnswerOption::create([
                'question_id' => $question->id,
                'option_text' => fake()->sentence(3),
                'is_correct' => false,
            ]);
        }
    }
    
    /**
     * Create options for matching questions.
     */
    private function createMatchingOptions(Question $question): void
    {
        // For matching questions, we'll create 4 terms and 4 definitions
        // In a real application, these would be displayed in columns for matching
        // Here we just simplify by creating the answer options
        $matchingPairs = 4;
        
        for ($i = 0; $i < $matchingPairs; $i++) {
            $term = fake()->word();
            $definition = fake()->sentence();
            
            AnswerOption::create([
                'question_id' => $question->id,
                'option_text' => "{$term} - {$definition}",
                'is_correct' => true, // All are "correct" in the sense they are valid matches
            ]);
        }
    }
}
