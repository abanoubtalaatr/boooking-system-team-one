<?php

namespace Database\Seeders;

use App\Models\Favorite;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;

        while ($created < 30) {

            $favorite = Favorite::factory()->make();

            Favorite::firstOrCreate([
                'doctor_id' => $favorite->doctor_id,
                'user_id'   => $favorite->user_id,
            ]);

            $created++;
        }
    }
}