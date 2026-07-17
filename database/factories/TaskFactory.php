<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = \App\Models\Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'category_id' => null,
            'status_id' => TaskStatus::query()->inRandomOrder()->value('id') ?? 1,
            'priority_id' => TaskPriority::query()->inRandomOrder()->value('id') ?? 1,
            'author_id' => User::factory(),
            'assigned_to' => null,
            'parent_id' => null,
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'estimated_hours' => fake()->randomFloat(2, 1, 40),
            'spent_hours' => 0,
            'done_ratio' => fake()->numberBetween(0, 100),
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'due_date' => fake()->dateTimeBetween('now', '+2 months'),
        ];
    }
}
