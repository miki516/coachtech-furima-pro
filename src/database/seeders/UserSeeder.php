<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 出品なしユーザー
        User::create([
            'name'  => '購入専用ユーザー',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code'   => '123-4567',
            'address'       => '東京都テスト区1-2-3',
            'building'      => '購入ビル',
            'profile_image' => 'profiles/user_buyer.png',
        ]);

        // 出品者1（CO01〜CO05）
        User::create([
            'name' => '出品者A',
            'email' => 'seller1@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code'   => '111-1111',
            'address'       => '東京都渋谷区1-1-1',
            'building'      => 'Aビル',
            'profile_image' => 'profiles/user_seller_a.png',
        ]);

        // 出品者2（CO06〜CO10）
        User::create([
            'name' => '出品者B',
            'email' => 'seller2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'postal_code'   => '222-2222',
            'address'       => '東京都品川区2-2-2',
            'building'      => 'Bビル',
            'profile_image' => 'profiles/user_seller_b.png',
        ]);
    }
}
