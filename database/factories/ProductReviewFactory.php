<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductReviewFactory extends Factory
{
    protected $model = ProductReview::class;

    public function definition(): array
    {
        $comment = $this->faker->sentences($this->faker->numberBetween(1, 2), true);

        return [
            'product_id' => Product::factory(),
            'reviewer_name' => $this->faker->name(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $comment,
            'body' => $comment,
        ];
    }
}
