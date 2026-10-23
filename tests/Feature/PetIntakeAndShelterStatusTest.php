<?php

namespace Tests\Feature;

use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PetIntakeAndShelterStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_add_rescued_pet_with_in_shelter_status_and_intake_details(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->post(route('pets.store'), [
            'name' => 'Brownie',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'size' => 'medium',
            'type' => 'dog',
            'status' => 'in_shelter',
            'rescue_date' => '2026-10-23',
            'rescue_location' => 'Poblacion Market',
            'description' => 'Gentle rescue dog undergoing shelter recovery.',
            'photo' => UploadedFile::fake()->create('rescue.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect(route('pets.index'));

        $pet = Pet::where('name', 'Brownie')->first();
        $this->assertNotNull($pet);
        $this->assertSame('in_shelter', $pet->status);
        $this->assertSame('2026-10-23', $pet->rescue_date->format('Y-m-d'));
        $this->assertSame('Poblacion Market', $pet->rescue_location);
    }

    public function test_pets_index_filters_by_status_tabs_and_computes_counts(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        Pet::create([
            'name' => 'Shelter Dog',
            'breed' => 'Aspin',
            'color' => 'Black',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'in_shelter',
        ]);

        Pet::create([
            'name' => 'Ready Dog',
            'breed' => 'Poodle',
            'color' => 'White',
            'gender' => 'female',
            'type' => 'dog',
            'status' => 'available',
        ]);

        // 1. All Pets Tab
        $allResponse = $this->actingAs($staff)->get(route('pets.index', ['status' => 'all']));
        $allResponse->assertOk();
        $allResponse->assertSee('Shelter Dog');
        $allResponse->assertSee('Ready Dog');

        // 2. In Shelter Tab
        $inShelterResponse = $this->actingAs($staff)->get(route('pets.index', ['status' => 'in_shelter']));
        $inShelterResponse->assertOk();
        $inShelterResponse->assertSee('Shelter Dog');
        $inShelterResponse->assertDontSee('Ready Dog');

        // 3. Ready for Adoption Tab
        $availableResponse = $this->actingAs($staff)->get(route('pets.index', ['status' => 'available']));
        $availableResponse->assertOk();
        $availableResponse->assertSee('Ready Dog');
        $availableResponse->assertDontSee('Shelter Dog');
    }

    public function test_staff_can_promote_in_shelter_pet_to_ready_for_adoption(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Healing Cat',
            'breed' => 'Puspin',
            'color' => 'Orange Tabby',
            'gender' => 'female',
            'type' => 'cat',
            'status' => 'in_shelter',
        ]);

        $this->assertSame('in_shelter', $pet->status);

        $response = $this->actingAs($staff)->post(route('pets.mark-available', $pet));
        $response->assertSessionHas('success');

        $pet->refresh();
        $this->assertSame('available', $pet->status);
    }

    public function test_public_adopter_cannot_see_in_shelter_pets_on_pets_page(): void
    {
        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email_verified_at' => now(),
        ]);

        Pet::create([
            'name' => 'Secret Rescued Dog',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'in_shelter',
        ]);

        Pet::create([
            'name' => 'Public Available Dog',
            'breed' => 'Golden Retriever',
            'color' => 'Gold',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $response = $this->actingAs($adopter)->get(route('pets.index'));
        $response->assertOk();
        $response->assertSee('Public Available Dog');
        $response->assertDontSee('Secret Rescued Dog');
    }
}
