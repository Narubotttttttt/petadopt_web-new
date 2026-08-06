<?php

namespace Database\Seeders;

use App\Models\Pet;
use App\Models\TemperamentTag;
use Illuminate\Database\Seeder;

class PetSeeder extends Seeder
{
    public function run(): void
    {
        $pets = [
            [
                'name'            => 'Buddy',
                'type'            => 'dog',
                'breed'           => 'Golden Retriever',
                'color'           => 'Golden',
                'gender'          => 'male',
                'age'             => '2 yrs',
                'description'     => 'Buddy is a loving Golden Retriever who enjoys outdoor activities and playing with children. He is well-trained and gets along great with other pets.',
                'photo_path'      => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400&q=80',
                'medical_history' => 'Fully vaccinated, microchipped, and dewormed.',
                'status'          => 'available',
                'tags'            => ['Friendly', 'Playful', 'Affectionate'],
            ],
            [
                'name'            => 'Luna',
                'type'            => 'cat',
                'breed'           => 'Persian',
                'color'           => 'White',
                'gender'          => 'female',
                'age'             => '1 yr',
                'description'     => 'Luna is a gentle Persian cat who loves to curl up and be petted. She is perfect for a calm household and apartment living.',
                'photo_path'      => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&q=80',
                'medical_history' => 'Vaccinated and spayed.',
                'status'          => 'available',
                'tags'            => ['Calm', 'Affectionate'],
            ],
            [
                'name'            => 'Max',
                'type'            => 'dog',
                'breed'           => 'Labrador',
                'color'           => 'Black',
                'gender'          => 'male',
                'age'             => '3 yrs',
                'description'     => 'Max is an energetic Labrador who loves to run and play fetch. He needs an active family who enjoys outdoor adventures.',
                'photo_path'      => 'https://images.unsplash.com/photo-1518717758536-85ae29035b6d?w=400&q=80',
                'medical_history' => 'Neutered and fully vaccinated.',
                'status'          => 'available',
                'tags'            => ['Energetic', 'Friendly'],
            ],
            [
                'name'            => 'Mochi',
                'type'            => 'cat',
                'breed'           => 'Shorthair',
                'color'           => 'Calico',
                'gender'          => 'female',
                'age'             => '8 mos',
                'description'     => 'Mochi is a curious and playful young cat. She loves to explore and will keep you entertained with her antics.',
                'photo_path'      => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?w=400&q=80',
                'medical_history' => 'Dewormed and first vaccinations done.',
                'status'          => 'available',
                'tags'            => ['Playful', 'Independent'],
            ],
            [
                'name'            => 'Rocky',
                'type'            => 'dog',
                'breed'           => 'Beagle',
                'color'           => 'Tricolor',
                'gender'          => 'male',
                'age'             => '4 yrs',
                'description'     => 'Rocky is a cheerful Beagle who loves to sniff around and explore. He gets along with everyone.',
                'photo_path'      => 'https://images.unsplash.com/photo-1561037404-61cd46aa615b?w=400&q=80',
                'medical_history' => 'Fully vaccinated.',
                'status'          => 'available',
                'tags'            => ['Friendly', 'Protective'],
            ],
            [
                'name'            => 'Nala',
                'type'            => 'cat',
                'breed'           => 'Siamese',
                'color'           => 'Cream & Brown',
                'gender'          => 'female',
                'age'             => '2 yrs',
                'description'     => 'Nala is a talkative Siamese who loves to be the center of attention and will follow you everywhere.',
                'photo_path'      => 'https://images.unsplash.com/photo-1571566882372-1598d88abd90?w=400&q=80',
                'medical_history' => 'Spayed and vaccinated.',
                'status'          => 'available',
                'tags'            => ['Affectionate', 'Playful'],
            ],
        ];

        foreach ($pets as $petData) {
            $tagNames = $petData['tags'];
            unset($petData['tags']);

            $pet = Pet::firstOrCreate(
                ['name' => $petData['name']],
                $petData
            );

            $tagIds = TemperamentTag::whereIn('name', $tagNames)->pluck('id');
            $pet->temperamentTags()->sync($tagIds);
        }
    }
}
