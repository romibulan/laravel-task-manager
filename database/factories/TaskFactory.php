<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\TaskStatus;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('en_US');
        return [
            'title' => $faker->sentence(),
            'description' => $faker->paragraph(),
            'status' => $faker->randomElement([TaskStatus::Pending, TaskStatus::Completed, TaskStatus::InProgress]),
            'due_date' => $faker->dateTimeBetween('now', '+1 month'),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
