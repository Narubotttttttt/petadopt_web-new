<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pet;
use App\Models\PetHealthUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdopterMonthlyReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_adopters_or_reports(): void
    {
        $this->get('/adopters')->assertRedirect('/login');
        $this->get('/adopters/reports')->assertRedirect('/login');
    }

    public function test_staff_can_view_adopter_profiles_without_vaccine_overdue(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/adopters');

        $response->assertStatus(200);
        $response->assertSeeText('Adopter Profiles');
        $response->assertSeeText('Monthly Pet Updates');
        $response->assertDontSeeText('Vaccines Overdue');
    }

    public function test_staff_can_view_monthly_pet_updates_report_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/adopters/reports');

        $response->assertStatus(200);
        $response->assertSeeText('Monthly Pet Updates Report');
        $response->assertSeeText('Total Updates');
        $response->assertSeeText('Healthy & Active');
        $response->assertSeeText('Needs Attention');
    }

    public function test_monthly_pet_updates_supports_filters(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/adopters/reports?status=healthy&species=dog');

        $response->assertStatus(200);
    }

    public function test_monthly_pet_updates_displays_adopter_name_first_and_pet_info(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'name' => 'Maria Santos',
            'email' => 'maria.santos@example.com',
            'email_verified_at' => now(),
        ]);

        \App\Models\AdoptersProfile::create([
            'user_id' => $adopterUser->id,
            'adopter_code' => 'ADP-9901',
            'full_name' => 'Maria Santos',
            'email' => 'maria.santos@example.com',
            'phone' => '09123456789',
            'status' => 'active',
        ]);

        $pet = \App\Models\Pet::create([
            'name' => 'Barnaby',
            'type' => 'dog',
            'breed' => 'Golden Retriever',
            'status' => 'adopted',
            'age' => 2,
            'gender' => 'male',
            'size' => 'large',
            'added_by_user_id' => $admin->id,
        ]);

        \App\Models\PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/test.jpg',
            'health_status' => 'healthy',
            'weight' => 24.5,
            'notes' => 'Barnaby is doing wonderful and active!',
            'check_in_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($admin)->get('/adopters/reports');

        $response->assertStatus(200);
        $response->assertSeeText('Maria Santos');
        $response->assertSeeText('ADP-9901');
        $response->assertSeeText('Adopted Pet:');
        $response->assertSeeText('Barnaby');
        $response->assertSeeText('Barnaby is doing wonderful and active!');
    }

    public function test_monthly_pet_updates_consolidates_multiple_pets_for_same_adopter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'name' => 'Mayang Santos',
            'email' => 'mayang@example.com',
            'email_verified_at' => now(),
        ]);

        \App\Models\AdoptersProfile::create([
            'user_id' => $adopterUser->id,
            'adopter_code' => 'ADP-0006',
            'full_name' => 'Mayang Santos',
            'email' => 'mayang@example.com',
            'phone' => '09334545411',
            'status' => 'active',
        ]);

        $pet1 = \App\Models\Pet::create([
            'name' => 'King',
            'type' => 'dog',
            'breed' => 'Aspin',
            'status' => 'adopted',
            'age' => 3,
            'gender' => 'male',
            'size' => 'medium',
            'added_by_user_id' => $admin->id,
        ]);

        $pet2 = \App\Models\Pet::create([
            'name' => 'Claude',
            'type' => 'cat',
            'breed' => 'Puspin',
            'status' => 'adopted',
            'age' => 1,
            'gender' => 'male',
            'size' => 'small',
            'added_by_user_id' => $admin->id,
        ]);

        \App\Models\PetHealthUpdate::create([
            'pet_id' => $pet1->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/king.jpg',
            'health_status' => 'healthy',
            'weight' => 15.0,
            'notes' => 'King is healthy and active.',
            'check_in_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        \App\Models\PetHealthUpdate::create([
            'pet_id' => $pet2->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/claude.jpg',
            'health_status' => 'healthy',
            'weight' => 4.2,
            'notes' => 'Claude is doing well.',
            'check_in_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($admin)->get('/adopters/reports?tab=monthly_updates');

        $response->assertStatus(200);
        $response->assertSeeText('Mayang Santos');
        $response->assertSeeText('ADP-0006');
        $response->assertSeeText('2 Pets');
        $response->assertSeeText('Pet:');
        $response->assertSeeText('King');
        $response->assertSeeText('Claude');
    }

    public function test_vaccination_proofs_consolidates_multiple_pets_for_same_adopter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'name' => 'Mayang Santos',
            'email' => 'mayang.vet@example.com',
            'email_verified_at' => now(),
        ]);

        \App\Models\AdoptersProfile::create([
            'user_id' => $adopterUser->id,
            'adopter_code' => 'ADP-0007',
            'full_name' => 'Mayang Santos',
            'email' => 'mayang.vet@example.com',
            'phone' => '09334545412',
            'status' => 'active',
        ]);

        $pet1 = \App\Models\Pet::create([
            'name' => 'King',
            'type' => 'dog',
            'breed' => 'Aspin',
            'status' => 'adopted',
            'age' => 3,
            'gender' => 'male',
            'size' => 'medium',
            'added_by_user_id' => $admin->id,
        ]);

        $pet2 = \App\Models\Pet::create([
            'name' => 'Claude',
            'type' => 'cat',
            'breed' => 'Puspin',
            'status' => 'adopted',
            'age' => 1,
            'gender' => 'male',
            'size' => 'small',
            'added_by_user_id' => $admin->id,
        ]);

        \App\Models\MedicalLog::create([
            'pet_id' => $pet1->id,
            'created_by' => $adopterUser->id,
            'category' => 'vaccination',
            'vaccine_name' => 'Rabies Vaccine',
            'clinic_name' => 'PetVet Clinic',
            'administered_by' => 'Dr. Santos',
            'date' => now()->toDateString(),
            'is_external' => true,
            'verification_status' => 'pending',
            'proof_photo_path' => 'vet-proofs/king_rabies.jpg',
        ]);

        \App\Models\MedicalLog::create([
            'pet_id' => $pet2->id,
            'created_by' => $adopterUser->id,
            'category' => 'vaccination',
            'vaccine_name' => 'FVRCP Vaccine',
            'clinic_name' => 'PetVet Clinic',
            'administered_by' => 'Dr. Santos',
            'date' => now()->toDateString(),
            'is_external' => true,
            'verification_status' => 'pending',
            'proof_photo_path' => 'vet-proofs/claude_fvrcp.jpg',
        ]);

        $response = $this->actingAs($admin)->get('/adopters/reports?tab=vet_proofs');

        $response->assertStatus(200);
        $response->assertSeeText('Mayang Santos');
        $response->assertSeeText('ADP-0007');
        $response->assertSeeText('2 Pets');
        $response->assertSeeText('Pet:');
        $response->assertSeeText('King');
        $response->assertSeeText('Claude');
    }

    public function test_monthly_pet_updates_excludes_deceased_pets(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'name' => 'Carla Reyes',
            'email' => 'carla@example.com',
            'email_verified_at' => now(),
        ]);

        \App\Models\AdoptersProfile::create([
            'user_id' => $adopterUser->id,
            'adopter_code' => 'ADP-0008',
            'full_name' => 'Carla Reyes',
            'email' => 'carla@example.com',
            'phone' => '09170001122',
            'status' => 'active',
        ]);

        $pet = \App\Models\Pet::create([
            'name' => 'Rusty',
            'type' => 'dog',
            'breed' => 'Shih Tzu',
            'status' => 'deceased',
            'age' => 5,
            'gender' => 'male',
            'size' => 'small',
            'added_by_user_id' => $admin->id,
        ]);

        \App\Models\PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/rusty.jpg',
            'health_status' => 'deceased',
            'notes' => 'Rusty peacefully crossed the rainbow bridge after an extended illness.',
            'check_in_date' => now()->toDateString(),
            'status' => 'memorial_recorded',
            'review_decision' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/adopters/reports?tab=monthly_updates');

        $response->assertStatus(200);
        $response->assertDontSeeText('Rusty peacefully crossed the rainbow bridge');
    }

    public function test_staff_can_view_pet_dedications_tab_and_memorials(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'name' => 'Danilo Ramos',
            'email' => 'danilo@example.com',
            'email_verified_at' => now(),
        ]);

        \App\Models\AdoptersProfile::create([
            'user_id' => $adopterUser->id,
            'adopter_code' => 'ADP-0009',
            'full_name' => 'Danilo Ramos',
            'email' => 'danilo@example.com',
            'phone' => '09180003344',
            'status' => 'active',
        ]);

        $pet = \App\Models\Pet::create([
            'name' => 'Oreo',
            'type' => 'cat',
            'breed' => 'Tuxedo Cat',
            'status' => 'deceased',
            'age' => 4,
            'gender' => 'male',
            'size' => 'small',
            'added_by_user_id' => $admin->id,
        ]);

        \App\Models\PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/oreo.jpg',
            'health_status' => 'deceased',
            'notes' => 'Oreo was a cherished member of our family. Passed away naturally in his sleep.',
            'check_in_date' => now()->toDateString(),
            'status' => 'memorial_recorded',
            'review_decision' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get('/adopters/reports?tab=pet_dedications');

        $response->assertStatus(200);
        $response->assertSeeText('Pet Dedications & Memorials');
        $response->assertSeeText('Oreo');
        $response->assertSeeText('Danilo Ramos');
        $response->assertSeeText('ADP-0009');
        $response->assertSeeText('Pending Review');
        $response->assertSeeText('Oreo was a cherished member of our family');
    }

    public function test_staff_can_review_deceased_report_without_remarks(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'name' => 'Danilo Ramos',
            'email' => 'danilo2@example.com',
            'email_verified_at' => now(),
        ]);

        $pet = \App\Models\Pet::create([
            'name' => 'Milo',
            'type' => 'dog',
            'breed' => 'Aspin',
            'status' => 'deceased',
            'age' => 3,
            'gender' => 'male',
            'size' => 'medium',
            'added_by_user_id' => $admin->id,
        ]);

        $report = \App\Models\PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/milo.jpg',
            'health_status' => 'deceased',
            'notes' => 'Passed away peacefully.',
            'check_in_date' => now()->toDateString(),
            'status' => 'memorial_recorded',
            'review_decision' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch("/adopters/reports/{$report->id}/review", [
            'decision' => 'cleared_accidental',
            'staff_remarks' => '',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pet_health_updates', [
            'id' => $report->id,
            'review_decision' => 'cleared_accidental',
            'staff_remarks' => null,
            'status' => 'memorial_cleared',
        ]);
    }

    public function test_staff_can_review_deceased_report_with_neglect_and_restrict_adopter(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'email_verified_at' => now(),
        ]);

        $profile = \App\Models\AdoptersProfile::create([
            'user_id' => $adopterUser->id,
            'adopter_code' => 'ADP-0010',
            'full_name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'phone' => '09191234567',
            'status' => 'active',
        ]);

        $pet = \App\Models\Pet::create([
            'name' => 'Buster',
            'type' => 'dog',
            'breed' => 'Shih Tzu',
            'status' => 'deceased',
            'age' => 2,
            'gender' => 'male',
            'size' => 'small',
            'added_by_user_id' => $admin->id,
        ]);

        $report = \App\Models\PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/buster.jpg',
            'health_status' => 'deceased',
            'notes' => 'Left unattended outside.',
            'check_in_date' => now()->toDateString(),
            'status' => 'memorial_recorded',
            'review_decision' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch("/adopters/reports/{$report->id}/review", [
            'decision' => 'neglect_confirmed',
            'staff_remarks' => 'Pet was left without shelter.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pet_health_updates', [
            'id' => $report->id,
            'review_decision' => 'neglect_confirmed',
            'staff_remarks' => 'Pet was left without shelter.',
            'status' => 'memorial_neglect',
        ]);

        $profile->refresh();
        $this->assertEquals('restricted', $profile->status);
        $this->assertStringContainsString('Administrative Investigation', $profile->admin_notes);
        $this->assertStringContainsString('Pet was left without shelter.', $profile->admin_notes);
    }

    public function test_staff_can_review_deceased_report_with_staff_remarks(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'King',
            'type' => 'dog',
            'breed' => 'Aspin',
            'status' => 'deceased',
            'age' => 2,
            'gender' => 'male',
            'size' => 'medium',
            'added_by_user_id' => $admin->id,
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'email_verified_at' => now(),
        ]);

        $report = PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/king.jpg',
            'health_status' => 'deceased',
            'notes' => '[PET LIFE EVENT: DECEASED REPORT]\nDate of Passing: 2026-11-20\nCause Category: Severe Illness\nReason & Circumstances: namatay sa parvo virus',
            'check_in_date' => now()->toDateString(),
            'status' => 'memorial_recorded',
            'review_decision' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch("/adopters/reports/{$report->id}/review", [
            'decision' => 'cleared_accidental',
            'staff_remarks' => 'Cross-verified with attending vet Dr. Valdez. Proper care was provided.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('cleared_accidental', $report->review_decision);
        $this->assertEquals('Cross-verified with attending vet Dr. Valdez. Proper care was provided.', $report->staff_remarks);
    }

    public function test_staff_can_place_deceased_report_on_hold_for_onsite_investigation(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'name' => 'Adopter Test',
            'email' => 'adopter.inquiry@example.com',
            'email_verified_at' => now(),
        ]);

        $profile = \App\Models\AdoptersProfile::create([
            'user_id' => $adopterUser->id,
            'adopter_code' => 'ADP-0099',
            'full_name' => 'Adopter Test',
            'email' => 'adopter.inquiry@example.com',
            'phone' => '09171234567',
            'status' => 'active',
        ]);

        $pet = Pet::create([
            'name' => 'Rocky',
            'type' => 'dog',
            'breed' => 'Beagle',
            'status' => 'deceased',
            'age' => 4,
            'gender' => 'male',
            'size' => 'medium',
            'added_by_user_id' => $admin->id,
        ]);

        $report = PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/rocky.jpg',
            'health_status' => 'deceased',
            'notes' => 'Pet passed away under uncertain circumstances.',
            'check_in_date' => now()->toDateString(),
            'status' => 'memorial_recorded',
            'review_decision' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch("/adopters/reports/{$report->id}/review", [
            'decision' => 'investigation_pending',
            'staff_remarks' => 'Shelter officer scheduled home visit for Nov 25.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('investigation_pending', $report->review_decision);
        $this->assertEquals('memorial_investigating', $report->status);
        $this->assertEquals('Shelter officer scheduled home visit for Nov 25.', $report->staff_remarks);

        $profile->refresh();
        // Adopter is NOT blacklisted/restricted; remains active
        $this->assertEquals('active', $profile->status);
        $this->assertStringContainsString('On-Site Verification Requested', $profile->admin_notes);
        $this->assertStringContainsString('Rocky', $profile->admin_notes);

        // Check that dedication filter investigating includes this report
        $filterResponse = $this->actingAs($admin)->get('/adopters/reports?tab=pet_dedications&dedication_filter=investigating');
        $filterResponse->assertStatus(200);
        $filterResponse->assertSeeText('Rocky');
        $filterResponse->assertSeeText('On-Site Visit Requested');
        $filterResponse->assertSeeText('Visit Pending');
    }
}
