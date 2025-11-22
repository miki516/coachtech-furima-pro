<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'buyer_id' => User::factory(),
            'product_id' => Product::factory(),
            'payment_method_id' => 1,
            'shipping_address' => sprintf(
                "〒%s %s%s %s",
                $this->faker->postcode(),
                $this->faker->state(),
                $this->faker->city(),
                $this->faker->streetAddress()
            ),
            'total_price' => $this->faker->numberBetween(1000, 10000),
        ];
    }
}
