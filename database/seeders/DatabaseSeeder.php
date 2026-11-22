<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Article;
use App\Models\Category;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);


        $categories = collect(['Politics', 'Business', 'Technology', 'Sports', 'Entertainment'])
            ->map(fn (string $name) => Category::create(['name' => $name]));

        // 20 active articles spread across categories, plus a few inactive ones.
        Article::factory()
            ->count(20)
            ->recycle($categories)
            ->create();
    }
}
