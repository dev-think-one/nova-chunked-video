<?php

namespace NovaChunkedVideo\Tests\Fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NovaChunkedVideo\Tests\Fixtures\Models\Article;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{

    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'title'  => $this->faker->unique()->word(),
            'video'  => null,
        ];
    }

}
