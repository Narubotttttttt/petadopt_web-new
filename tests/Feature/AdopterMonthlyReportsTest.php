<?php

namespace Tests\Feature;

use App\Models\User;
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
        $response->assertSeeText('Monthly Reports');
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
}
