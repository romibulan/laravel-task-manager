<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::pluck('id')->each(function ($user_id) {
            Task::factory()
                ->state(['created_by' => $user_id])
                ->count(10)
                ->create();
        });
    }
}
