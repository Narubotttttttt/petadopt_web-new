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
            'temperament_tags' => [\App\Models\TemperamentTag::firstOrCreate(['name' => 'Under Behavioral Observation'])->id],
            'description' => 'Gentle rescue dog undergoing shelter recovery.',
            'photo' => UploadedFile::fake()->create('rescue.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect(route('pets.index'));

        $pet = Pet::where('name', 'Brownie')->first();
        $this->assertNotNull($pet);
        $this->assertSame('in_shelter', $pet->status);
        $this->assertSame('2026-10-23', $pet->rescue_date->format('Y-m-d'));
        $this->assertSame('Poblacion Market', $pet->rescue_location);
        $this->assertTrue($pet->temperamentTags->contains('name', 'Under Behavioral Observation'));
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

        // 1. Default Directory View (Defaults to In Shelter, All Pets tab removed)
        $defaultResponse = $this->actingAs($staff)->get(route('pets.index'));
        $defaultResponse->assertOk();
        $defaultResponse->assertSee('Shelter Dog');
        $defaultResponse->assertDontSee('Ready Dog');
        $defaultResponse->assertDontSee('All Pets');

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

    public function test_pets_index_renders_registration_modal_options_for_staff(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('pets.index'));
        $response->assertOk();
        $response->assertSee('Select Registration Type');
        $response->assertSee('Newly Rescued Pet');
        $response->assertSee('Ready for Adoption');
        $response->assertSee(route('pets.create', ['status' => 'in_shelter']));
        $response->assertSee(route('pets.create', ['status' => 'available']));
    }

    public function test_create_pet_screen_preselects_in_shelter_mode(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('pets.create', ['status' => 'in_shelter']));
        $response->assertOk();
        $response->assertSee('Shelter Pet Intake Registration');
        $response->assertSee('Streamlined 2-step intake triage');
        $response->assertSee("status: 'in_shelter'", false);
        $response->assertSee('type="hidden" name="status"', false);
        $response->assertDontSee('Pet Status / Intake Stage');
    }

    public function test_create_pet_screen_preselects_available_mode(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('pets.create', ['status' => 'available']));
        $response->assertOk();
        $response->assertSee('Ready for Adoption Registration');
        $response->assertSee('Full 3-step listing profile');
        $response->assertSee("status: 'available'", false);
        $response->assertSee('type="hidden" name="status"', false);
        $response->assertDontSee('Pet Status / Intake Stage');
    }

    public function test_edit_pet_screen_displays_status_dropdown(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Editable Pet',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'in_shelter',
        ]);

        $response = $this->actingAs($staff)->get(route('pets.edit', $pet));
        $response->assertOk();
        $response->assertSee('<select name="status"', false);
        $response->assertSee('isWizard: true', false);
        $response->assertSee('General Details');
        $response->assertSee('Next: Intake Health & Photo', false);
        $response->assertSee('Save & Update', false);
        $response->assertSee('Update Pet Profile');
    }

    public function test_pet_model_computes_days_in_shelter_and_displays_on_views(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $recentPet = Pet::create([
            'name' => 'New Arrival',
            'breed' => 'Aspin',
            'color' => 'White',
            'gender' => 'female',
            'type' => 'dog',
            'status' => 'in_shelter',
            'created_at' => now(),
        ]);

        $shelterPet = Pet::create([
            'name' => 'Long Stay Dog',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'in_shelter',
            'rescue_date' => now()->subDays(12)->toDateString(),
            'rescue_location' => 'Highway Overpass',
            'created_at' => now()->subDays(12),
        ]);

        $adoptedPet = Pet::create([
            'name' => 'Happy Dog',
            'breed' => 'Golden Retriever',
            'color' => 'Golden',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'adopted',
            'created_at' => now()->subDays(30),
        ]);

        // Model attribute assertions
        $this->assertSame(0, $recentPet->days_in_shelter);
        $this->assertSame('Registered today', $recentPet->days_in_shelter_formatted);

        $this->assertSame(12, $shelterPet->days_in_shelter);
        $this->assertSame('12 days in shelter', $shelterPet->days_in_shelter_formatted);

        $this->assertSame('Adopted', $adoptedPet->days_in_shelter_formatted);

        // JSON Serialization / API assertion
        $arrayData = $shelterPet->toArray();
        $this->assertArrayHasKey('days_in_shelter', $arrayData);
        $this->assertArrayHasKey('days_in_shelter_formatted', $arrayData);
        $this->assertSame(12, $arrayData['days_in_shelter']);
        $this->assertSame('12 days in shelter', $arrayData['days_in_shelter_formatted']);

        // Directory view assertion
        $indexResponse = $this->actingAs($staff)->get(route('pets.index', ['status' => 'in_shelter']));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Stay Duration');
        $indexResponse->assertSee('12 days in shelter');
        $indexResponse->assertSee('Registered today');

        // Show view assertion
        $showResponse = $this->actingAs($staff)->get(route('pets.show', $shelterPet));
        $showResponse->assertOk();
        $showResponse->assertSee('Shelter Stay');
        $showResponse->assertSee('12 days in shelter');
    }

    public function test_recommendation_engine_never_recommends_in_shelter_pets(): void
    {
        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email_verified_at' => now(),
        ]);

        $tag = \App\Models\TemperamentTag::firstOrCreate(['name' => 'Playful']);

        $inShelterPet = Pet::create([
            'name' => 'Shelter Only Pet',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'in_shelter',
        ]);
        $inShelterPet->temperamentTags()->attach($tag);

        $availablePet = Pet::create([
            'name' => 'Available Adopter Pet',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);
        $availablePet->temperamentTags()->attach($tag);

        $response = $this->actingAs($adopter, 'sanctum')->postJson('/api/recommendations/match', [
            'preferred_species'    => 'dog',
            'living_environment'   => 'house_fenced',
            'activity_level'       => 'high',
            'desired_temperaments' => ['Playful'],
        ]);

        $response->assertOk();
        $recs = $response->json('recommendations');
        $this->assertNotEmpty($recs);

        $recommendedPetIds = collect($recs)->pluck('id')->all();
        $this->assertContains($availablePet->id, $recommendedPetIds);
        $this->assertNotContains($inShelterPet->id, $recommendedPetIds);

        foreach ($recs as $rec) {
            $this->assertSame('available', strtolower($rec['status']));
        }
    }

    public function test_adoption_application_cannot_be_submitted_for_in_shelter_pet(): void
    {
        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email_verified_at' => now(),
        ]);

        $inShelterPet = Pet::create([
            'name' => 'Intake Triage Dog',
            'breed' => 'Aspin',
            'color' => 'Black',
            'gender' => 'female',
            'type' => 'dog',
            'status' => 'in_shelter',
        ]);

        $response = $this->actingAs($adopter, 'sanctum')->postJson('/api/adoption-applications', [
            'pet_id'    => $inShelterPet->id,
            'full_name' => 'Adopter Test',
            'phone'     => '09123456789',
            'address'   => 'Cagayan de Oro City',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'This pet is currently in shelter care or not ready for adoption.',
        ]);
    }
}
