<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PetManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_create_pet_with_selected_medical_history_and_description(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/pets', [
            'name' => 'Buddy',
            'breed' => 'Labrador',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'age' => '2 years',
            'medical_history' => ['Vaccinated', 'Spayed/Neutered'],
            'temperament' => 'Friendly and playful',
            'description' => 'A joyful companion who loves walks and cuddles.',
            'photo' => UploadedFile::fake()->create('pet.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect('/pets');

        $pet = Pet::latest()->first();
        $this->assertNotNull($pet);
        $this->assertSame('Vaccinated, Spayed/Neutered', $pet->medical_history);
        $this->assertSame('A joyful companion who loves walks and cuddles.', $pet->description);
    }
}
