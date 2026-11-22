<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(6), '.');

        return [
            'category_id' => Category::factory(),
            'title' => $title,
            'summary' => fake()->text(200),
            'body' => implode("\n\n", fake()->paragraphs(5)),
            'permalink' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'status' => Article::STATUS_ACTIVE,
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => Article::STATUS_INACTIVE,
        ]);
    }
}
