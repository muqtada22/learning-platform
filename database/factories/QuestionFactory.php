<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::inRandomOrder()->first()->id ?? Lesson::factory()->create()->id,
            'question_text' => fake()->sentence(rand(5, 10)) . '?',
            'question_type' => fake()->randomElement(['multiple_choice', 'true_false', 'fill_in_blank', 'matching']),
        ];
    }
    
    /**
     * Configure the question to be a multiple choice type.
     */
    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'multiple_choice',
        ]);
    }
    
    /**
     * Configure the question to be a true or false type.
     */
    public function trueFalse(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'true_false',
            'question_text' => 'True or False: ' . fake()->sentence(),
        ]);
    }
    
    /**
     * Configure the question to be a fill in the blank type.
     */
    public function fillInBlank(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'fill_in_blank',
        ]);
    }
    
    /**
     * Configure the question to be a matching type.
     */
    public function matching(): static
    {
        return $this->state(fn (array $attributes) => [
            'question_type' => 'matching',
        ]);
    }
}
