<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Currency;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Currency::truncate();

        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 200; $i++) {
            Currency::create([
                'currency' => $faker->randomElement(['EUR', 'USD', 'GBP']),
                'amount' => $faker->randomFloat(2, 3.0, 7.0),
                'date' => $faker->dateTimeBetween('1900-01-01', date('Y-m-d'))->format("Y-m-d"),
            ]);
        }
    }
}
