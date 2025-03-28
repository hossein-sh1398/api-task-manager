<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->delete();
        Task::query()->delete();

        User::factory()->has(Task::factory()->count(50), 'tasks')->create([
            'name' => 'Hossein',
            'email' => 'hossein@gmail.com',
            'password' => bcrypt('Aa$123123123'),
        ]);
    }
}
