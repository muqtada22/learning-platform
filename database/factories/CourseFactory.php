<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'professor_id' => User::where('role', 'professor')->inRandomOrder()->first()->id ?? User::factory()->professor()->create()->id,
            'title' => fake()->sentence(rand(3, 6)),
            'description' => fake()->paragraphs(rand(2, 5), true),
            'total_hours' => fake()->numberBetween(10, 120),
            'uploaded_file_path' => null,
        ];
    }
}
