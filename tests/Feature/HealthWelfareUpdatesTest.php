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
        $responseAdmin->assertSeeText('Adopter Profiles');

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
}
