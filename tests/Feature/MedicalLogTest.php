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

    public function test_pet_medical_passport_endpoint_returns_clinical_passport_data(): void
    {
        $pet = Pet::create([
            'name' => 'Bantay',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'adopted',
        ]);

        $log = MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'vaccination',
            'administered_by' => 'Dr. Reyes',
            'next_due_date' => now()->addMonths(6)->toDateString(),
            'created_by' => $this->staff->id,
        ]);

        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email' => 'adopter@test.com',
        ]);

        \App\Models\AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Juan Dela Cruz',
            'applicant_email' => $adopter->email,
            'applicant_phone' => '09123456789',
            'status' => 'approved',
            'message' => 'Loving home',
        ]);

        $response = $this->actingAs($adopter, 'sanctum')->getJson("/api/pets/{$pet->id}/medical-passport");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'passport' => [
                'pet' => [
                    'id' => $pet->id,
                    'name' => 'Bantay',
                ],
                'clinical_summary' => [
                    'vaccine_status' => 'Up to Date',
                ],
            ],
        ]);
    }

    public function test_adoption_application_show_displays_medical_clearance_card(): void
    {
        $pet = Pet::create([
            'name' => 'Brownie',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'vaccination',
            'administered_by' => 'Dr. Cruz',
            'next_due_date' => now()->addMonths(6)->toDateString(),
            'created_by' => $this->staff->id,
        ]);

        $application = \App\Models\AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Maria Santos',
            'applicant_email' => 'maria@test.com',
            'applicant_phone' => '09987654321',
            'status' => 'under_review',
            'message' => 'Adoption application',
        ]);

        $response = $this->actingAs($this->staff)->get(route('adoption-applications.show', $application));

        $response->assertStatus(200);
        $response->assertSee('Clinical & Vaccination Clearance', false);
        $response->assertSee('Vaccination Status');
        $response->assertSee('Deworming Status');
    }

    public function test_new_vaccination_log_fulfills_and_clears_prior_overdue_due_date(): void
    {
        $pet = Pet::create([
            'name' => 'Max',
            'breed' => 'Shih Tzu',
            'color' => 'White',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        // Prior vaccination with an overdue due date
        $oldLog = MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->subMonths(7)->toDateString(),
            'category' => 'vaccination',
            'administered_by' => 'Shelter Vet',
            'next_due_date' => now()->subMonth()->toDateString(),
            'created_by' => $this->staff->id,
        ]);

        $this->assertEquals(1, MedicalLog::whereNotNull('next_due_date')->whereDate('next_due_date', '<', now()->startOfDay())->count());

        // Now staff records the new booster administered at an outside clinic
        $response = $this->actingAs($this->staff)->post(route('medical-logs.store'), [
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'vaccination',
            'administered_by' => 'Dr. Cruz (ABC Animal Clinic)',
        ]);

        $response->assertRedirect();

        // The old log's next_due_date is now fulfilled and cleared
        $this->assertNull($oldLog->fresh()->next_due_date);

        // There are now 0 overdue records in the system
        $this->assertEquals(0, MedicalLog::whereNotNull('next_due_date')->whereDate('next_due_date', '<', now()->startOfDay())->count());

        // The new log has a future due date (6 months ahead)
        $newLog = MedicalLog::where('pet_id', $pet->id)->latest('id')->first();
        $this->assertNotNull($newLog->next_due_date);
        $this->assertTrue($newLog->next_due_date->isFuture());
    }

    public function test_store_medical_log_with_vaccine_name_detail(): void
    {
        $pet = Pet::create([
            'name' => 'Bella',
            'breed' => 'Shih Tzu',
            'color' => 'White',
            'gender' => 'female',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->staff)->post(route('medical-logs.store'), [
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'vaccination',
            'vaccine_name' => '5-in-1 (DHPP)',
            'administered_by' => 'Dr. Santos',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('medical_logs', [
            'pet_id' => $pet->id,
            'category' => 'vaccination',
            'vaccine_name' => '5-in-1 (DHPP)',
            'administered_by' => 'Dr. Santos',
        ]);
    }

    public function test_send_medical_reminders_command_runs_successfully(): void
    {
        $pet = Pet::create([
            'name' => 'Rocky',
            'breed' => 'German Shepherd',
            'color' => 'Black and Tan',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'adopted',
        ]);

        MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->subMonths(6)->toDateString(),
            'category' => 'vaccination',
            'vaccine_name' => 'Anti-Rabies',
            'administered_by' => 'Shelter Vet',
            'next_due_date' => now()->addDays(3)->toDateString(),
            'created_by' => $this->staff->id,
        ]);

        \App\Models\AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Carlos Dalisay',
            'applicant_email' => 'carlos@example.com',
            'applicant_phone' => '09123456789',
            'status' => 'approved',
            'message' => 'Good home',
        ]);

        $this->artisan('reminders:send')
            ->expectsOutputToContain('Checking medical logs for scheduled reminders...')
            ->assertExitCode(0);
    }

    public function test_store_deworming_log_auto_calculates_3_months_due_date(): void
    {
        $pet = Pet::create([
            'name' => 'Brownie',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $todayStr = now()->toDateString();
        $expectedDueDate = now()->addMonths(3)->toDateString();

        $response = $this->actingAs($this->staff)->post(route('medical-logs.store'), [
            'pet_id' => $pet->id,
            'date' => $todayStr,
            'category' => 'deworming',
            'vaccine_name' => 'Canex Puppy/Dog',
            'administered_by' => 'Dr. Santos',
        ]);

        $response->assertRedirect();

        $log = MedicalLog::where('pet_id', $pet->id)->where('category', 'deworming')->first();
        $this->assertNotNull($log);
        $this->assertEquals($expectedDueDate, $log->next_due_date->format('Y-m-d'));
    }

    public function test_automated_reminder_distinguishes_deworming_dose_from_vaccine_booster(): void
    {
        $pet = Pet::create([
            'name' => 'Bella',
            'type' => 'dog',
            'status' => 'adopted',
        ]);

        // Deworming due in 3 days
        MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->subMonths(3)->addDays(3)->toDateString(),
            'category' => 'deworming',
            'vaccine_name' => 'Canex Puppy/Dog',
            'administered_by' => 'Dr. Santos',
            'next_due_date' => now()->addDays(3)->toDateString(),
            'created_by' => $this->staff->id,
        ]);

        \App\Models\AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Elena Gilbert',
            'applicant_email' => 'elena@example.com',
            'applicant_phone' => '09123456789',
            'status' => 'approved',
            'message' => 'Great home',
        ]);

        $this->artisan('reminders:send')
            ->expectsOutputToContain('Dispatching Deworming notification to elena@example.com')
            ->assertExitCode(0);
    }
}
