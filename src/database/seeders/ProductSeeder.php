<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $seller1 = User::where('email', 'seller1@example.com')->first();
        $seller2 = User::where('email', 'seller2@example.com')->first();

        $products = [
            [
                'seller_id' => $seller1->id,
                'name' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'image_path' => 'products/watch.jpg',
                'condition' => '良好',
                'categories' => [1, 5],
            ],
            [
                'seller_id' => $seller1->id,
                'name' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'image_path' => 'products/hdd.jpg',
                'condition' => '目立った傷や汚れなし',
                'categories' => [2],
            ],
            [
                'seller_id' => $seller1->id,
                'name' => '玉ねぎ3束',
                'price' => 300,
                'brand' => 'なし',
                'description' => '新鮮な玉ねぎ3束のセット',
                'image_path' => 'products/onion_bundle.jpg',
                'condition' => 'やや傷や汚れあり',
                'categories' => [10],
            ],
            [
                'seller_id' => $seller1->id,
                'name' => '革靴',
                'price' => 4000,
                'brand' => '',
                'description' => 'クラシックなデザインの革靴',
                'image_path' => 'products/leather_shoes.jpg',
                'condition' => '状態が悪い',
                'categories' => [1, 5],
            ],
            [
                'seller_id' => $seller1->id,
                'name' => 'ノートPC',
                'price' => 45000,
                'brand' => '',
                'description' => '高性能なノートパソコン',
                'image_path' => 'products/laptop.jpg',
                'condition' => '良好',
                'categories' => [2],
            ],
            [
                'seller_id' => $seller2->id,
                'name' => 'マイク',
                'price' => 8000,
                'brand' => 'なし',
                'description' => '高音質のレコーディング用マイク',
                'image_path' => 'products/microphone.jpg',
                'condition' => '目立った傷や汚れなし',
                'categories' => [2],
            ],
            [
                'seller_id' => $seller2->id,
                'name' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => '',
                'description' => 'おしゃれなショルダーバッグ',
                'image_path' => 'products/shoulder_bag.jpg',
                'condition' => 'やや傷や汚れあり',
                'categories' => [1, 4],
            ],
            [
                'seller_id' => $seller2->id,
                'name' => 'タンブラー',
                'price' => 500,
                'brand' => 'なし',
                'description' => '使いやすいタンブラー',
                'image_path' => 'products/tumbler.jpg',
                'condition' => '状態が悪い',
                'categories' => [10],
            ],
            [
                'seller_id' => $seller2->id,
                'name' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'image_path' => 'products/coffee_mill.jpg',
                'condition' => '良好',
                'categories' => [10],
            ],
            [
                'seller_id' => $seller2->id,
                'name' => 'メイクセット',
                'price' => 2500,
                'brand' => '',
                'description' => '便利なメイクアップセット',
                'image_path' => 'products/makeup_set.jpg',
                'condition' => '目立った傷や汚れなし',
                'categories' => [6],
            ],
        ];

        foreach ($products as $productData) {
            $categories = $productData['categories'];
            unset($productData['categories']);

            $product = Product::create($productData);

            $product->categories()->attach($categories);
        }
    }
}
