<?php

namespace Tests\Feature;

use App\Models\AdoptersProfile;
use App\Models\Pet;
use App\Models\PetHealthUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthWelfareUpdatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_health_welfare_updates(): void
    {
        $this->get('/health-welfare-updates')->assertRedirect('/login');
        $this->patch('/health-welfare-updates/1/review')->assertRedirect('/login');
    }

    public function test_admin_and_staff_can_view_health_welfare_updates_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $responseAdmin = $this->actingAs($admin)->get('/health-welfare-updates');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSeeText('Monthly Pet Updates');
        $responseAdmin->assertSeeText('Vaccination updates');
        $responseAdmin->assertSeeText('Pet Dedications');
        $responseAdmin->assertSeeText('Health & Welfare');

        $responseStaff = $this->actingAs($staff)->get('/health-welfare-updates');
        $responseStaff->assertStatus(200);
        $responseStaff->assertSeeText('Monthly Pet Updates');
    }

    public function test_staff_can_review_deceased_report_via_health_welfare_route(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'name' => 'Carla Diaz',
            'email' => 'carla@example.com',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Bella',
            'type' => 'cat',
            'breed' => 'Puspin',
            'status' => 'deceased',
            'age' => 5,
            'gender' => 'female',
            'size' => 'small',
            'added_by_user_id' => $staff->id,
        ]);

        $report = PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopterUser->id,
            'photo_path' => 'pet-updates/bella.jpg',
            'health_status' => 'deceased',
            'notes' => 'Passed away due to old age.',
            'check_in_date' => now()->toDateString(),
            'status' => 'memorial_recorded',
            'review_decision' => 'pending',
        ]);

        $response = $this->actingAs($staff)->patch("/health-welfare-updates/{$report->id}/review", [
            'decision' => 'cleared_accidental',
            'staff_remarks' => 'Confirmed old age.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pet_health_updates', [
            'id' => $report->id,
            'review_decision' => 'cleared_accidental',
            'staff_remarks' => 'Confirmed old age.',
            'status' => 'memorial_cleared',
        ]);
    }

    public function test_sidebar_contains_health_and_welfare_updates_link(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/adopters');
        $response->assertStatus(200);
        $response->assertSeeText('Health & Welfare');
        $response->assertSee(route('health-welfare-updates.index'));
    }

    public function test_sidebar_displays_red_dot_when_new_health_welfare_update_is_submitted(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Charlie',
            'type' => 'dog',
            'breed' => 'Aspin',
            'status' => 'adopted',
            'age' => 2,
            'gender' => 'male',
            'size' => 'medium',
            'added_by_user_id' => $admin->id,
        ]);

        // First, mark current updates viewed so baseline is established
        \App\Services\AdminNotificationService::markHealthWelfareUpdatesViewed();

        // Baseline: no new updates
        $response = $this->actingAs($admin)->get('/adopters');
        $response->assertDontSee('data-testid="new-health-welfare-indicator"', false);

        // Submitting 1: Routine Monthly Pet Health Update
        PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'photo_path' => 'pet-updates/charlie.jpg',
            'health_status' => 'healthy',
            'notes' => 'Charlie is energetic and eating well.',
            'check_in_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        // Red dot indicator appears on sidebar
        $response = $this->actingAs($admin)->get('/adopters');
        $response->assertSee('data-testid="new-health-welfare-indicator"', false);

        // Visiting Health & Welfare page marks updates as viewed
        $this->actingAs($admin)->get('/health-welfare-updates')->assertStatus(200);

        // Red dot disappears
        $response = $this->actingAs($admin)->get('/adopters');
        $response->assertDontSee('data-testid="new-health-welfare-indicator"', false);
    }

    public function test_health_welfare_mark_viewed_endpoint(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->postJson('/health-welfare-updates/mark-viewed');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_health_welfare_photo_urls_use_proxied_request_host_instead_of_localhost(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Mochi',
            'type' => 'cat',
            'breed' => 'Puspin',
            'status' => 'adopted',
            'age' => 2,
            'gender' => 'female',
            'size' => 'small',
            'added_by_user_id' => $staff->id,
        ]);

        PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'photo_path' => 'health_updates/mochi_checkin.jpg',
            'health_status' => 'healthy',
            'notes' => 'Healthy check-in.',
            'check_in_date' => now()->toDateString(),
            'status' => 'submitted',
        ]);

        PetHealthUpdate::create([
            'pet_id' => $pet->id,
            'user_id' => $adopter->id,
            'photo_path' => 'http://localhost:8000/storage/health_updates/legacy_memorial.jpg',
            'health_status' => 'deceased',
            'notes' => 'Memorial submission.',
            'check_in_date' => now()->toDateString(),
            'status' => 'memorial_recorded',
            'review_decision' => 'pending',
        ]);

        \App\Models\MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'vaccination',
            'vaccine_name' => 'Anti-Rabies',
            'is_external' => true,
            'clinic_name' => 'City Vet',
            'proof_photo_path' => 'medical_proofs/mochi_vax.png',
            'verification_status' => 'pending',
            'created_by' => $adopter->id,
        ]);

        $proxyHeaders = [
            'X-Forwarded-Host' => 'caws-petadoption.vercel.app',
            'X-Forwarded-Proto' => 'https',
        ];

        $monthlyResponse = $this->actingAs($staff)
            ->withHeaders($proxyHeaders)
            ->get('/health-welfare-updates?tab=monthly_updates');
        $monthlyResponse->assertStatus(200);
        $monthlyResponse->assertSee('https://caws-petadoption.vercel.app/storage/health_updates/mochi_checkin.jpg', false);
        $monthlyResponse->assertDontSee('http://localhost:8000/storage/health_updates/mochi_checkin.jpg', false);

        $vetResponse = $this->actingAs($staff)
            ->withHeaders($proxyHeaders)
            ->get('/health-welfare-updates?tab=vet_proofs');
        $vetResponse->assertStatus(200);
        $vetResponse->assertSee('https://caws-petadoption.vercel.app/storage/medical_proofs/mochi_vax.png', false);

        $dedicationResponse = $this->actingAs($staff)
            ->withHeaders($proxyHeaders)
            ->get('/health-welfare-updates?tab=pet_dedications');
        $dedicationResponse->assertStatus(200);
        $dedicationResponse->assertSee('https://caws-petadoption.vercel.app/storage/health_updates/legacy_memorial.jpg', false);
        $dedicationResponse->assertDontSee('http://localhost:8000/storage/health_updates/legacy_memorial.jpg', false);
    }
}
