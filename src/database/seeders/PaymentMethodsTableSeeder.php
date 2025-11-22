<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('payment_methods')->insert([
            [
                'id'   => 1,
                'name' => 'コンビニ払い',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'   => 2,
                'name' => 'カード支払い',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
