<?php

namespace Tests\Feature;

use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaponSchedulingTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function createAdopterUser(): User
    {
        return User::factory()->create([
            'role' => 'adopter',
            'email_verified_at' => now(),
        ]);
    }

    private function createPet(array $overrides = []): Pet
    {
        return Pet::create(array_merge([
            'name' => 'Rocky',
            'type' => 'dog',
            'breed' => 'Mixed',
            'status' => 'adopted',
            'is_neutered' => false,
        ], $overrides));
    }

    private function makeApplication(array $overrides = []): AdoptionApplication
    {
        return AdoptionApplication::create(array_merge([
            'applicant_name' => 'Juan Dela Cruz',
            'applicant_email' => 'juan@example.com',
            'applicant_phone' => '09171234567',
            'status' => 'approved',
            'documents_verified_at' => now(),
            'kapon_status' => 'awaiting_schedule',
        ], $overrides));
    }

    public function test_guest_cannot_update_kapon_schedule(): void
    {
        $pet = $this->createPet();
        $application = $this->makeApplication(['pet_id' => $pet->id]);

        $response = $this->patch("/adoption-applications/{$application->id}/kapon", [
            'kapon_action' => 'schedule',
            'kapon_scheduled_date' => now()->addDays(5)->toDateTimeString(),
        ]);

        $response->assertRedirect('/login');
    }

    public function test_staff_can_schedule_kapon_session(): void
    {
        $admin = $this->createAdminUser();
        $pet = $this->createPet(['is_neutered' => false]);
        $adopter = $this->createAdopterUser();

        $application = $this->makeApplication([
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'applicant_email' => $adopter->email,
            'kapon_status' => 'awaiting_schedule',
        ]);

        $scheduledDate = now()->addDays(7)->format('Y-m-d H:i:s');
        $venue = 'CAWS Main Clinic';
        $instructions = 'Fast companion 8 hours prior to procedure.';

        $response = $this->actingAs($admin)->patch("/adoption-applications/{$application->id}/kapon", [
            'kapon_action' => 'schedule',
            'kapon_scheduled_date' => $scheduledDate,
            'kapon_venue' => $venue,
            'kapon_instructions' => $instructions,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $application->refresh();
        $this->assertEquals('scheduled', $application->kapon_status);
        $this->assertEquals($venue, $application->kapon_venue);
        $this->assertEquals($instructions, $application->kapon_instructions);
    }

    public function test_staff_can_mark_kapon_completed_and_updates_pet_and_medical_log(): void
    {
        $admin = $this->createAdminUser();
        $pet = $this->createPet(['is_neutered' => false]);
        $adopter = $this->createAdopterUser();

        $application = $this->makeApplication([
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'applicant_email' => $adopter->email,
            'kapon_status' => 'scheduled',
            'kapon_scheduled_date' => now()->subDay(),
        ]);

        $completedDate = now()->toDateTimeString();
        $vetName = 'Dr. Maria Santos, DVM';

        $response = $this->actingAs($admin)->patch("/adoption-applications/{$application->id}/kapon", [
            'kapon_action' => 'complete',
            'kapon_completed_at' => $completedDate,
            'kapon_attending_vet' => $vetName,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $application->refresh();
        $pet->refresh();

        $this->assertEquals('completed', $application->kapon_status);
        $this->assertEquals($vetName, $application->kapon_attending_vet);
        $this->assertTrue((bool) $pet->is_neutered);

        // Verify surgical MedicalLog was created for pet medical passport
        $this->assertDatabaseHas('medical_logs', [
            'pet_id' => $pet->id,
            'category' => 'surgery',
            'vaccine_name' => 'Spay / Neuter (Kapon)',
            'administered_by' => $vetName,
            'verification_status' => 'verified',
        ]);
    }

    public function test_staff_can_mark_kapon_not_required(): void
    {
        $admin = $this->createAdminUser();
        $pet = $this->createPet(['is_neutered' => false]);

        $application = $this->makeApplication([
            'pet_id' => $pet->id,
            'kapon_status' => 'awaiting_schedule',
        ]);

        $response = $this->actingAs($admin)->patch("/adoption-applications/{$application->id}/kapon", [
            'kapon_action' => 'not_required',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $application->refresh();
        $pet->refresh();

        $this->assertEquals('not_required', $application->kapon_status);
        $this->assertTrue((bool) $pet->is_neutered);
    }

    public function test_api_my_applications_returns_kapon_fields_to_adopter(): void
    {
        $adopter = $this->createAdopterUser();
        $pet = $this->createPet([
            'name' => 'Luna',
            'is_neutered' => false,
        ]);

        $scheduledDate = now()->addDays(4);

        $this->makeApplication([
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'applicant_name' => $adopter->name,
            'applicant_email' => $adopter->email,
            'status' => 'approved',
            'documents_verified_at' => now(),
            'kapon_status' => 'scheduled',
            'kapon_scheduled_date' => $scheduledDate,
            'kapon_venue' => 'CAWS Shelter Clinic',
            'kapon_instructions' => 'Strictly fast 8-10 hours prior to surgery.',
        ]);

        $response = $this->actingAs($adopter)->getJson('/api/my-applications');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'petName',
                    'kapon_status',
                    'kapon_scheduled_date',
                    'kapon_venue',
                    'kapon_instructions',
                    'is_neutered',
                ],
            ],
        ]);

        $data = $response->json('data.0');
        $this->assertEquals('scheduled', $data['kapon_status']);
        $this->assertEquals('CAWS Shelter Clinic', $data['kapon_venue']);
        $this->assertStringContainsString('Strictly fast', $data['kapon_instructions']);
    }
}
