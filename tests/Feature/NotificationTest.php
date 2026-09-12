<?php

namespace Tests\Feature;

use App\Models\AdoptionApplication;
use App\Models\Pet;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_fetch_notifications(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Max',
            'type' => 'dog',
            'status' => 'available',
        ]);

        AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'John Doe',
            'applicant_email' => 'john@example.com',
            'status' => 'pending',
        ]);

        AdminNotificationService::clearCache();

        $response = $this->actingAs($staff)->get(route('admin.notifications.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notifications',
            'unread_count',
            'counts' => ['all', 'checkins', 'overdue', 'requests']
        ]);
        $this->assertGreaterThanOrEqual(1, $response->json('unread_count'));
    }

    public function test_staff_can_mark_all_notifications_as_read(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Bella',
            'type' => 'dog',
            'status' => 'available',
        ]);

        AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Alice Smith',
            'applicant_email' => 'alice@example.com',
            'status' => 'pending',
        ]);

        AdminNotificationService::clearCache();

        // Check before
        $initial = $this->actingAs($staff)->get(route('admin.notifications.index'));
        $this->assertGreaterThanOrEqual(1, $initial->json('unread_count'));

        // Mark all read
        $markResponse = $this->actingAs($staff)->post(route('admin.notifications.markAllRead'));
        $markResponse->assertStatus(200);
        $markResponse->assertJson(['success' => true, 'unread_count' => 0]);

        // Verify unread count is 0
        $after = $this->actingAs($staff)->get(route('admin.notifications.index'));
        $after->assertStatus(200);
        $this->assertEquals(0, $after->json('unread_count'));
    }
}
