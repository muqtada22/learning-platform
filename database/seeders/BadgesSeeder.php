<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BadgesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            [
                'name' => '3-Day Streak',
                'description' => 'Completed learning activities for 3 consecutive days.',
                'xp_reward' => 30,
            ],
            [
                'name' => '7-Day Streak',
                'description' => 'Completed learning activities for 7 consecutive days.',
                'xp_reward' => 70,
            ],
            [
                'name' => '30-Day Streak',
                'description' => 'Completed learning activities for 30 consecutive days.',
                'xp_reward' => 300,
            ],
            [
                'name' => 'First Course Completed',
                'description' => 'Successfully completed your first course.',
                'xp_reward' => 100,
            ],
            [
                'name' => 'Quiz Master',
                'description' => 'Answer 100 questions correctly.',
                'xp_reward' => 150,
            ],
            [
                'name' => 'Perfect Score',
                'description' => 'Got all questions correct in a lesson.',
                'xp_reward' => 50,
            ],
            [
                'name' => 'Knowledge Seeker',
                'description' => 'Enrolled in 5 different courses.',
                'xp_reward' => 75,
            ],
            [
                'name' => 'Dedicated Learner',
                'description' => 'Spent more than 10 hours learning on the platform.',
                'xp_reward' => 200,
            ],
        ];
        
        foreach ($badges as $badge) {
            Badge::create($badge);
        }
        
        // Create additional random badges
        Badge::factory()->count(5)->create();
    }
}
