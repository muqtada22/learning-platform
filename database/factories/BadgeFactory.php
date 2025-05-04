<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Badge>
 */
class BadgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $achievements = [
            'Fast Learner',
            'Knowledge Seeker',
            'Quiz Master',
            'Perfect Score',
            'Streak Champion',
            'Dedicated Student',
            'Early Bird',
            'Course Completer',
            'Top Performer',
            'Learning Enthusiast'
        ];
        
        return [
            'name' => fake()->randomElement($achievements),
            'description' => fake()->sentence(rand(10, 20)),
            'xp_reward' => fake()->randomElement([10, 20, 50, 100, 200, 500]),
        ];
    }
}
