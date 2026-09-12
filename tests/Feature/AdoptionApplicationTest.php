<?php

namespace Tests\Feature;

use App\Models\AdoptionApplication;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdoptionApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_adoption_application_page(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Cooper',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Mark Cruz',
            'applicant_email' => 'mark@example.com',
            'applicant_phone' => '09123456780',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staff)->get(route('adoption-applications.show', $app));

        $response->assertStatus(200);
        $response->assertSee('Adoption Decision');
        $response->assertSee('Approve');
        $response->assertSee('Reject');
    }

    public function test_staff_can_approve_adoption_application(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Buddy',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Sarah Connor',
            'applicant_email' => 'sarah@example.com',
            'applicant_phone' => '09987654321',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staff)->patch(route('adoption-applications.update', $app), [
            'status' => 'approved',
            'scheduled_at' => now()->addDays(3)->format('Y-m-d'),
            'event_location' => 'Centrio Mall CDO',
            'event_notes' => 'Please bring carrier and valid ID.',
            'evaluation_notes' => 'Screened and approved by shelter staff member.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('adoption_applications', [
            'id' => $app->id,
            'status' => 'approved',
            'event_location' => 'Centrio Mall CDO',
        ]);

        $pet->refresh();
        $this->assertEquals('adopted', $pet->status);
    }

    public function test_staff_can_reject_adoption_application(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Daisy',
            'type' => 'cat',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'John Doe',
            'applicant_email' => 'john.doe@example.com',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staff)->patch(route('adoption-applications.update', $app), [
            'status' => 'rejected',
            'rejection_reason' => 'Housing conditions do not permit pets at this time.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('adoption_applications', [
            'id' => $app->id,
            'status' => 'rejected',
            'rejection_reason' => 'Housing conditions do not permit pets at this time.',
        ]);
    }

    public function test_decision_dropdown_rejects_pending_or_under_review(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Rocky',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Jane Doe',
            'applicant_email' => 'jane@example.com',
            'status' => 'pending',
        ]);

        $responsePending = $this->actingAs($staff)->patch(route('adoption-applications.update', $app), [
            'status' => 'pending',
        ]);
        $responsePending->assertSessionHasErrors('status');

        $responseUnderReview = $this->actingAs($staff)->patch(route('adoption-applications.update', $app), [
            'status' => 'under_review',
        ]);
        $responseUnderReview->assertSessionHasErrors('status');
    }

    public function test_staff_rejecting_application_triggers_push_notification_dispatch(): void
    {
        \Illuminate\Support\Facades\Log::spy();

        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Milo',
            'type' => 'cat',
            'status' => 'available',
        ]);

        $applicant = User::factory()->create([
            'email' => 'milo_applicant@example.com',
            'fcm_token' => null,
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Milo Lover',
            'applicant_email' => $applicant->email,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staff)->patch(route('adoption-applications.update', $app), [
            'status' => 'rejected',
            'rejection_reason' => 'Applicant does not meet fenced yard criteria.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('adoption_applications', [
            'id' => $app->id,
            'status' => 'rejected',
            'rejection_reason' => 'Applicant does not meet fenced yard criteria.',
        ]);

        // Verifies that FirebaseNotificationService::sendToUser was invoked for this rejection
        \Illuminate\Support\Facades\Log::shouldHaveReceived('info')
            ->withArgs(fn($message) => str_contains($message, 'FCM:') && str_contains($message, 'milo_applicant@example.com'));
    }

    public function test_staff_rejecting_without_reason_fails_validation(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Oliver',
            'type' => 'cat',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Sam Smith',
            'applicant_email' => 'sam@example.com',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staff)->patch(route('adoption-applications.update', $app), [
            'status' => 'rejected',
            'rejection_reason' => '',
            'evaluation_notes' => '',
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_approving_without_event_location_or_instructions_fails_validation(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Charlie',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Lucy Green',
            'applicant_email' => 'lucy@example.com',
            'status' => 'pending',
        ]);

        // Attempt approve without event location and instructions
        $response = $this->actingAs($staff)->patch(route('adoption-applications.update', $app), [
            'status' => 'approved',
            'event_location' => '',
            'event_notes' => '',
        ]);

        $response->assertSessionHasErrors(['event_location', 'event_notes']);
    }

    public function test_screening_recommendation_is_not_displayed_on_page(): void
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

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Emma Watson',
            'applicant_email' => 'emma@example.com',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staff)->get(route('adoption-applications.show', $app));
        $response->assertStatus(200);
        $response->assertDontSee('Screening Recommendation');
    }

    public function test_staff_cannot_access_staff_management(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('users.index'));

        // Staff middleware redirects non-admins or returns forbidden
        $response->assertForbidden();
    }

    public function test_admin_can_access_staff_management(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertStatus(200);
    }

    public function test_contract_download_is_blocked_if_adopter_has_not_signed(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Kona',
            'type' => 'dog',
            'status' => 'adopted',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Alex Rivera',
            'applicant_email' => 'alex@example.com',
            'status' => 'approved',
            'signature_path' => null,
        ]);

        $response = $this->actingAs($staff)->get(route('adoption-applications.contract', $app));

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'error' => 'The adoption contract must be digitally signed by the adopter before downloading or printing.'
        ]);
    }

    public function test_api_contract_download_url_is_blocked_if_adopter_has_not_signed(): void
    {
        $adopter = User::factory()->create([
            'email' => 'adopter_api@example.com',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Simba',
            'type' => 'cat',
            'status' => 'adopted',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Adopter API',
            'applicant_email' => $adopter->email,
            'status' => 'approved',
            'signature_path' => null,
        ]);

        $response = $this->actingAs($adopter, 'sanctum')->getJson("/api/adoption-applications/{$app->id}/contract");

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'The adoption contract must be digitally signed by the adopter before downloading.',
        ]);
    }

    public function test_show_page_displays_awaiting_signature_when_unsigned(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Luna',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Luna Fan',
            'applicant_email' => 'luna@example.com',
            'status' => 'approved',
            'signature_path' => null,
        ]);

        $response = $this->actingAs($staff)->get(route('adoption-applications.show', $app));

        $response->assertStatus(200);
        $response->assertSee('Awaiting Adopter Signature');
        $response->assertDontSee('Print Contract');

        // Now attach signature
        $app->update(['signature_path' => 'signatures/dummy.png']);

        $responseWithSig = $this->actingAs($staff)->get(route('adoption-applications.show', $app));
        $responseWithSig->assertStatus(200);
        $responseWithSig->assertSee('Print Contract');
    }
}
