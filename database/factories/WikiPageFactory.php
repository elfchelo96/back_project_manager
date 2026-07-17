<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WikiPageFactory extends Factory
{
    protected $model = \App\Models\WikiPage::class;

    public function definition(): array
    {
        $userId = User::factory();

        return [
            'project_id' => Project::factory(),
            'title' => fake()->unique()->sentence(3),
            'content' => fake()->paragraphs(3, true),
            'created_by' => $userId,
            'updated_by' => $userId,
        ];
    }
}
