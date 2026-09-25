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
        $response->assertSee('Awaiting Adopter Pre-Signature');
        $response->assertDontSee('Print Official Contract');

        // Now attach adopter pre-signature
        $app->update(['signature_path' => 'signatures/dummy.png']);

        $responseWithSig = $this->actingAs($staff)->get(route('adoption-applications.show', $app));
        $responseWithSig->assertStatus(200);
        $responseWithSig->assertSee('Preview Draft Contract');

        // Now finalize handover
        $app->update([
            'staff_signature_path' => 'signatures/staff_dummy.png',
            'documents_verified_at' => now(),
            'id_document_verified' => true,
            'barangay_cert_verified' => true,
        ]);

        $responseWithFinalized = $this->actingAs($staff)->get(route('adoption-applications.show', $app));
        $responseWithFinalized->assertStatus(200);
        $responseWithFinalized->assertSee('Print Official Contract');
    }

    public function test_contract_download_is_locked_for_adopter_until_physical_verification_finalized(): void
    {
        $adopterUser = User::factory()->create([
            'role' => 'adopter',
            'email' => 'adopter_pre_sign@example.com',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Bantay',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Adopter Pre Sign',
            'applicant_email' => $adopterUser->email,
            'status' => 'approved',
            'signature_path' => 'signatures/adopter_sig.png',
            'signed_at' => now(),
        ]);

        // 1. Unfinalized (pre-signed only): API contract endpoint returns 403
        $apiResponse = $this->actingAs($adopterUser, 'sanctum')
            ->getJson("/api/adoption-applications/{$app->id}/contract");

        $apiResponse->assertStatus(403);
        $apiResponse->assertJsonFragment([
            'success' => false,
        ]);

        // 2. Direct contract download route returns 403 for adopter
        $downloadResponse = $this->actingAs($adopterUser)->get(route('contract.download', [
            'id' => $app->id,
        ]));
        $downloadResponse->assertStatus(403);

        // 3. Staff verifies documents and finalizes handover
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);
        \App\Models\StaffProfile::create([
            'user_id' => $staff->id,
            'staff_code' => 'STF-0099',
            'full_name' => $staff->name,
            'status' => 'active',
            'digital_signature_path' => 'signatures/staff_saved.png',
        ]);
        \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/staff_saved.png', 'fake_staff_sig');

        $finalizeResponse = $this->actingAs($staff)->post(route('adoption-applications.finalize-handover', $app), [
            'id_document_verified' => '1',
            'barangay_cert_verified' => '1',
            'use_saved_signature' => '1',
        ]);

        $finalizeResponse->assertRedirect();
        $this->assertTrue($app->fresh()->is_finalized);

        // 4. Now API contract endpoint unlocks for adopter
        $apiUnlocked = $this->actingAs($adopterUser, 'sanctum')
            ->getJson("/api/adoption-applications/{$app->id}/contract");
        $apiUnlocked->assertStatus(200);
        $apiUnlocked->assertJsonStructure(['url']);
    }

    public function test_sidebar_displays_red_circle_when_new_request_exists(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Bruno',
            'type' => 'dog',
            'status' => 'available',
        ]);

        AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'John Doe',
            'applicant_email' => 'john@example.com',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($staff)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('data-testid="new-adoption-request-indicator"', false);
    }

    public function test_sidebar_does_not_display_red_circle_when_no_new_requests(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('data-testid="new-adoption-request-indicator"', false);
    }

    public function test_sidebar_red_circle_disappears_when_adoption_requests_viewed_and_reappears_on_new_request(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Bruno',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $app1 = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'John Doe',
            'applicant_email' => 'john@example.com',
            'status' => 'pending',
        ]);

        // Indicator visible before visiting
        $beforeVisit = $this->actingAs($staff)->get(route('dashboard'));
        $beforeVisit->assertSee('data-testid="new-adoption-request-indicator"', false);

        // Staff visits Adoption Requests (simulating click / navigation)
        $visit = $this->actingAs($staff)->get(route('adoption-applications.index'));
        $visit->assertStatus(200);

        // Indicator should now disappear on Dashboard and across other pages
        $afterVisit = $this->actingAs($staff)->get(route('dashboard'));
        $afterVisit->assertDontSee('data-testid="new-adoption-request-indicator"', false);

        // A new adoption application arrives later
        AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Jane Smith',
            'applicant_email' => 'jane@example.com',
            'status' => 'pending',
        ]);

        // Indicator should reappear
        $afterNewApp = $this->actingAs($staff)->get(route('dashboard'));
        $afterNewApp->assertSee('data-testid="new-adoption-request-indicator"', false);

        // Staff clicks it via mark-viewed endpoint
        $this->actingAs($staff)->post(route('adoption-applications.mark-viewed'));

        // Indicator disappears again
        $afterDismiss = $this->actingAs($staff)->get(route('dashboard'));
        $afterDismiss->assertDontSee('data-testid="new-adoption-request-indicator"', false);
    }

    public function test_adopter_can_update_pet_name_via_api(): void
    {
        $adopter = User::factory()->create([
            'role' => 'adopter',
            'email' => 'milo_parent@example.com',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => null,
            'type' => 'dog',
            'breed' => 'Aspin',
            'status' => 'adopted',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Milo Parent',
            'applicant_email' => $adopter->email,
            'status' => 'approved',
            'message' => "Address: Carmen, CDO\nHome Type: House",
        ]);

        $response = $this->actingAs($adopter, 'sanctum')->postJson("/api/pets/{$pet->id}/update-name", [
            'name' => 'Rocky Balboa',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'pet_name' => 'Rocky Balboa',
            'pet' => [
                'id' => $pet->id,
                'name' => 'Rocky Balboa',
            ],
        ]);

        // Verify Pet database record was updated
        $pet->refresh();
        $this->assertEquals('Rocky Balboa', $pet->name);

        // Verify application message updated with Proposed Pet Name
        $app->refresh();
        $this->assertStringContainsString('Proposed Pet Name: Rocky Balboa', $app->message);

        // Verify unauthorized user cannot update someone else's pet
        $stranger = User::factory()->create(['role' => 'adopter', 'email' => 'stranger@example.com']);
        $unauthResponse = $this->actingAs($stranger, 'sanctum')->postJson("/api/pets/{$pet->id}/update-name", [
            'name' => 'Hacked Name',
        ]);
        $unauthResponse->assertStatus(403);
    }

    public function test_application_stores_recommendation_source_and_compatibility_score(): void
    {
        $adopter = User::factory()->create(['role' => 'adopter']);
        $pet = Pet::create([
            'name' => 'Charlie',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $response = $this->actingAs($adopter, 'sanctum')->postJson('/api/adoption-applications', [
            'pet_id'               => $pet->id,
            'full_name'            => 'Jane Doe',
            'phone'                => '09123456789',
            'address'              => 'Cagayan de Oro City',
            'application_source'   => 'recommendation',
            'compatibility_score'  => 92.5,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('adoption_applications', [
            'pet_id'              => $pet->id,
            'applicant_name'      => 'Jane Doe',
            'application_source'  => 'recommendation',
            'compatibility_score' => 92.5,
        ]);
    }

    public function test_application_defaults_to_manual_browsing(): void
    {
        $adopter = User::factory()->create(['role' => 'adopter']);
        $pet = Pet::create([
            'name' => 'Bella',
            'type' => 'cat',
            'status' => 'available',
        ]);

        $response = $this->actingAs($adopter, 'sanctum')->postJson('/api/adoption-applications', [
            'pet_id'    => $pet->id,
            'full_name' => 'John Smith',
            'phone'     => '09987654321',
            'address'   => 'Iligan City',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('adoption_applications', [
            'pet_id'              => $pet->id,
            'applicant_name'      => 'John Smith',
            'application_source'  => 'manual_browsing',
            'compatibility_score' => null,
        ]);
    }

    public function test_admin_and_staff_see_recommendation_match_on_show_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $pet = Pet::create([
            'name' => 'Rocky',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id'              => $pet->id,
            'applicant_name'      => 'Elena Gilbert',
            'applicant_email'     => 'elena@example.com',
            'applicant_phone'     => '09112223334',
            'status'              => 'pending',
            'application_source'  => 'recommendation',
            'compatibility_score' => 88.0,
        ]);

        $response = $this->actingAs($admin)->get(route('adoption-applications.show', $app));

        $response->assertStatus(200);
        $response->assertSee('Pet Recommendation Match');
        $response->assertSee('AI / ML Origin');
        $response->assertSee('88%');
        $response->assertSee('High Compatibility');
    }

    public function test_admin_and_staff_see_manual_browsing_on_show_page(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'email_verified_at' => now()]);
        $pet = Pet::create([
            'name' => 'Luna',
            'type' => 'cat',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id'              => $pet->id,
            'applicant_name'      => 'Damon Salvatore',
            'applicant_email'     => 'damon@example.com',
            'applicant_phone'     => '09223334445',
            'status'              => 'pending',
            'application_source'  => 'manual_browsing',
        ]);

        $response = $this->actingAs($staff)->get(route('adoption-applications.show', $app));

        $response->assertStatus(200);
        $response->assertSee('Manual Catalog Browsing');
        $response->assertSee('Manual Origin');
    }

    public function test_admin_and_staff_see_origin_badges_on_index_page(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'email_verified_at' => now()]);
        $pet1 = Pet::create(['name' => 'Pet One', 'type' => 'dog', 'status' => 'available']);
        $pet2 = Pet::create(['name' => 'Pet Two', 'type' => 'cat', 'status' => 'available']);

        AdoptionApplication::create([
            'pet_id'              => $pet1->id,
            'applicant_name'      => 'Rec User',
            'applicant_email'     => 'rec@example.com',
            'status'              => 'pending',
            'application_source'  => 'recommendation',
            'compatibility_score' => 95.0,
        ]);

        AdoptionApplication::create([
            'pet_id'              => $pet2->id,
            'applicant_name'      => 'Manual User',
            'applicant_email'     => 'manual@example.com',
            'status'              => 'pending',
            'application_source'  => 'manual_browsing',
        ]);

        $response = $this->actingAs($staff)->get(route('adoption-applications.index'));

        $response->assertStatus(200);
        $response->assertSee('AI Match');
        $response->assertSee('95%');
        $response->assertSee('Manual Catalog');
    }

    public function test_competing_applications_are_placed_on_priority_waitlist_when_primary_is_approved(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name'   => 'Max',
            'type'   => 'dog',
            'status' => 'available',
        ]);

        $appPrimary = AdoptionApplication::create([
            'pet_id'              => $pet->id,
            'applicant_name'      => 'Alice Primary',
            'applicant_email'     => 'alice@example.com',
            'status'              => 'pending',
            'compatibility_score' => 92.0,
        ]);

        $appSecondary = AdoptionApplication::create([
            'pet_id'              => $pet->id,
            'applicant_name'      => 'Bob Backup',
            'applicant_email'     => 'bob@example.com',
            'status'              => 'pending',
            'compatibility_score' => 78.0,
        ]);

        $response = $this->actingAs($staff)->patch(route('adoption-applications.update', $appPrimary), [
            'status'         => 'approved',
            'scheduled_at'   => now()->addDays(5)->format('Y-m-d'),
            'event_location' => 'Centrio Mall CDO',
            'event_notes'    => 'Bring valid ID for screening.',
        ]);

        $response->assertRedirect();

        // Primary is approved/scheduled
        $this->assertEquals('approved', $appPrimary->fresh()->status);

        // Secondary is placed on priority waitlist (under_review), NOT rejected
        $this->assertEquals('under_review', $appSecondary->fresh()->status);
    }

    public function test_show_page_displays_applicant_queue_when_multiple_applicants_exist(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name'   => 'Bella',
            'type'   => 'dog',
            'status' => 'available',
        ]);

        $app1 = AdoptionApplication::create([
            'pet_id'              => $pet->id,
            'applicant_name'      => 'First Applicant',
            'applicant_email'     => 'first@example.com',
            'status'              => 'pending',
            'compatibility_score' => 88.0,
        ]);

        $app2 = AdoptionApplication::create([
            'pet_id'              => $pet->id,
            'applicant_name'      => 'Second Applicant',
            'applicant_email'     => 'second@example.com',
            'status'              => 'pending',
            'compatibility_score' => 75.0,
        ]);

        $response = $this->actingAs($staff)->get(route('adoption-applications.show', $app1));

        $response->assertStatus(200);
        $response->assertSee('Applicant Queue and Priority Ranking');
        $response->assertSee('2 Total Applicants');
        $response->assertSee('Second Applicant');
        $response->assertSee('75% Match');
        $response->assertSee('Approve and Schedule Final Screening');
    }

    public function test_adopter_and_staff_signatures_render_with_dark_mode_visibility_classes_on_show_page(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name'   => 'Bella',
            'type'   => 'dog',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id'                 => $pet->id,
            'applicant_name'         => 'Test Adopter',
            'applicant_email'        => 'adopter@example.com',
            'status'                 => 'approved',
            'signature_path'         => 'signatures/adopter_sig.png',
            'staff_signature_path'   => 'signatures/staff_sig.png',
            'id_document_verified'   => true,
            'barangay_cert_verified' => true,
            'documents_verified_at'  => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('adoption-applications.show', $app));

        $response->assertStatus(200);
        $response->assertSee('filter dark:invert dark:brightness-200');
        $response->assertSee('Adopter Digital Signature');
        $response->assertSee('Staff Digital Signature');
    }

    public function test_staff_can_finalize_handover_by_drawing_new_signature_and_saving_to_profile(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Milo',
            'type' => 'cat',
            'status' => 'available',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Sarah Connor',
            'applicant_email' => 'sarah@example.com',
            'status' => 'approved',
            'signature_path' => 'signatures/adopter_sig.png',
        ]);

        // 1x1 transparent PNG base64
        $fakeBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($staff)->post(route('adoption-applications.finalize-handover', $app), [
            'id_document_verified' => '1',
            'barangay_cert_verified' => '1',
            'use_saved_signature' => '0',
            'signature_data' => $fakeBase64,
            'save_signature_to_profile' => '1',
        ]);

        $response->assertRedirect();
        $this->assertTrue($app->fresh()->is_finalized);
        $this->assertEquals('adopted', $pet->fresh()->status);
        $this->assertNotNull($app->fresh()->staff_signature_path);

        // Verify it synced to staff profile
        $staffProfile = $staff->fresh()->staffProfile;
        $this->assertNotNull($staffProfile);
        $this->assertEquals($app->fresh()->staff_signature_path, $staffProfile->digital_signature_path);
    }

    public function test_show_page_displays_handover_signing_modal_for_approved_application(): void
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
            'applicant_name' => 'John Wick',
            'applicant_email' => 'wick@example.com',
            'status' => 'approved',
            'signature_path' => 'signatures/wick_sig.png',
        ]);

        $response = $this->actingAs($staff)->get(route('adoption-applications.show', $app));

        $response->assertStatus(200);
        $response->assertSee('Adoption Handover');
        $response->assertSee('handoverSignatureCanvas');
        $response->assertSee('Valid Government ID');
        $response->assertSee('Barangay Certificate of Residency');
        $response->assertSee('Sign Handover');
    }

    public function test_staff_can_reset_finalized_handover_to_retest(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Ghost',
            'type' => 'dog',
            'status' => 'adopted',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Jon Snow',
            'applicant_email' => 'jon@example.com',
            'status' => 'approved',
            'signature_path' => 'signatures/adopter_sig.png',
            'staff_signature_path' => 'signatures/staff_sig.png',
            'staff_id' => $staff->id,
            'staff_name' => $staff->name,
            'staff_signed_at' => now(),
            'documents_verified_at' => now(),
            'id_document_verified' => true,
            'barangay_cert_verified' => true,
        ]);

        $this->assertTrue($app->is_finalized);

        $response = $this->actingAs($staff)->post(route('adoption-applications.reset-handover', $app));

        $response->assertRedirect();
        $this->assertFalse($app->fresh()->is_finalized);
        $this->assertNull($app->fresh()->staff_signature_path);
        $this->assertNull($app->fresh()->documents_verified_at);
        $this->assertEquals('available', $pet->fresh()->status);
    }

    public function test_user_can_remove_saved_signature_from_profile(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $profile = \App\Models\StaffProfile::create([
            'user_id' => $staff->id,
            'staff_code' => 'STF-TEST',
            'full_name' => $staff->name,
            'status' => 'active',
            'digital_signature_path' => 'signatures/saved_sig.png',
        ]);
        \Illuminate\Support\Facades\Storage::disk('public')->put('signatures/saved_sig.png', 'fake_sig_content');

        $response = $this->actingAs($staff)->delete(route('profile.signature.destroy'));

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'signature-deleted');
        $this->assertNull($profile->fresh()->digital_signature_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing('signatures/saved_sig.png');
    }
}


