<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name'        => $this->faker->word,
            'price'       => $this->faker->numberBetween(1000, 10000),
            'brand'       => $this->faker->company,
            'description' => $this->faker->sentence,
            'image_path'  => 'products/sample.jpg',
            'condition'   => '良好',
            'status'      => 'selling',
            'seller_id'   => User::factory(),
        ];
    }
}
