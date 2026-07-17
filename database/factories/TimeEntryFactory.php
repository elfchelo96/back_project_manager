<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeEntryFactory extends Factory
{
    protected $model = \App\Models\TimeEntry::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'hours' => fake()->randomFloat(2, 0.5, 8),
            'comments' => fake()->optional()->sentence(),
            'spent_on' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
