<?php

namespace Tests\Feature;

use App\Models\MedicalLog;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalLogTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);
    }

    public function test_medical_logs_create_page_renders_with_pet_selection_data(): void
    {
        $pet = Pet::create([
            'name' => 'Charlie',
            'breed' => 'Golden Retriever',
            'color' => 'Golden',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->staff)->get(route('medical-logs.create'));

        $response->assertStatus(200);
        $response->assertSee('Add Medical Log');
        $response->assertSee('Search by Pet ID or Name');
        $response->assertSee('Charlie');
        $response->assertSee((string) $pet->id);
    }

    public function test_medical_logs_create_with_preselected_pet(): void
    {
        $pet = Pet::create([
            'name' => 'Milo',
            'breed' => 'Beagle',
            'color' => 'Tri-color',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->staff)->get(route('medical-logs.create-for-pet', $pet));

        $response->assertStatus(200);
        $response->assertSee('Milo');
        $response->assertSee((string) $pet->id);
    }

    public function test_store_medical_log_successfully(): void
    {
        $pet = Pet::create([
            'name' => 'Luna',
            'breed' => 'Siamese',
            'color' => 'Cream',
            'gender' => 'female',
            'type' => 'cat',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->staff)->post(route('medical-logs.store'), [
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'vaccination',
            'administered_by' => 'Dr. Reyes',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('medical_logs', [
            'pet_id' => $pet->id,
            'category' => 'vaccination',
            'administered_by' => 'Dr. Reyes',
        ]);
    }

    public function test_store_medical_log_validation_requires_pet_id(): void
    {
        $response = $this->actingAs($this->staff)->post(route('medical-logs.store'), [
            'pet_id' => '',
            'date' => now()->toDateString(),
            'category' => 'vaccination',
        ]);

        $response->assertSessionHasErrors(['pet_id']);
    }

    public function test_edit_medical_log_page_renders_with_preselected_pet(): void
    {
        $pet = Pet::create([
            'name' => 'Cooper',
            'breed' => 'Poodle',
            'color' => 'White',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $log = MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'deworming',
            'administered_by' => 'Dr. Santos',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->get(route('medical-logs.edit', $log));

        $response->assertStatus(200);
        $response->assertSee('Edit Medical Log');
        $response->assertSee('Search by Pet ID or Name');
        $response->assertSee('Cooper');
        $response->assertSee((string) $pet->id);
    }

    public function test_update_medical_log_successfully(): void
    {
        $pet1 = Pet::create([
            'name' => 'Cooper',
            'breed' => 'Poodle',
            'color' => 'White',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $pet2 = Pet::create([
            'name' => 'Rocky',
            'breed' => 'Bulldog',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $log = MedicalLog::create([
            'pet_id' => $pet1->id,
            'date' => now()->toDateString(),
            'category' => 'deworming',
            'administered_by' => 'Dr. Santos',
            'created_by' => $this->staff->id,
        ]);

        $response = $this->actingAs($this->staff)->patch(route('medical-logs.update', $log), [
            'pet_id' => $pet2->id,
            'date' => now()->toDateString(),
            'category' => 'treatment',
            'administered_by' => 'Dr. Garcia',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('medical_logs', [
            'id' => $log->id,
            'pet_id' => $pet2->id,
            'category' => 'treatment',
            'administered_by' => 'Dr. Garcia',
        ]);
    }
}
