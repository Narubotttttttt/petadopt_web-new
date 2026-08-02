<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TemperamentTag;

class TemperamentTagSeeder extends Seeder
{
    public function run()
    {
        $tags = [
            'Friendly',
            'Calm',
            'Energetic',
            'Shy',
            'Playful',
            'Independent',
            'Affectionate',
            'Protective',
        ];

        foreach ($tags as $tag) {
            TemperamentTag::firstOrCreate(['name' => $tag]);
        }
    }
}