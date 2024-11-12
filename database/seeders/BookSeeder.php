<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use App\Models\Book;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 10; $i++) {
            Book::create([
                'title' => $faker->sentence(3),
                'author' => $faker->name,
                'isbn' => $faker->isbn13,
                'year_published' => $faker->numberBetween(1990, 2024),
                'quantity' => $faker->numberBetween(1, 10),
                'created_by' => rand(1, 10),
                'category_id' => rand(1, 10), // Assuming 10 categories
            ]);
        }
    }
}
