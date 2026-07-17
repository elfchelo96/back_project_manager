<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = \App\Models\Project::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'identifier' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->paragraph(),
            'status' => 'active',
            'start_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+6 months'),
            'owner_id' => User::factory(),
        ];
    }
}
