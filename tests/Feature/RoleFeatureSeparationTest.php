<?php

namespace Tests\Feature;

use App\Models\AdoptersProfile;
use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RoleFeatureSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Officer',
            'email' => 'admin@caws.org',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->staff = User::factory()->create([
            'name' => 'Caregiver Staff',
            'email' => 'staff@caws.org',
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);
    }

    public function test_staff_cannot_access_staff_management_while_admin_can(): void
    {
        $staffResponse = $this->actingAs($this->staff)->get(route('users.index'));
        $staffResponse->assertStatus(403);

        $adminResponse = $this->actingAs($this->admin)->get(route('users.index'));
        $adminResponse->assertStatus(200);
    }

    public function test_staff_cannot_access_staff_registration_while_admin_can(): void
    {
        $staffResponse = $this->actingAs($this->staff)->get(route('register'));
        $staffResponse->assertStatus(403);

        $adminResponse = $this->actingAs($this->admin)->get(route('register'));
        $adminResponse->assertStatus(200);
    }

    public function test_unauthenticated_guest_is_redirected_from_registration_when_admin_exists(): void
    {
        // When admin already exists, guest visiting /register is gracefully redirected to login
        $guestResponse = $this->get(route('register'));
        $guestResponse->assertRedirect(route('login'));
        $guestResponse->assertSessionHas('info');
    }

    public function test_staff_can_intake_and_edit_pet_but_cannot_delete_pet(): void
    {
        // 1. Staff can create pet
        $createResponse = $this->actingAs($this->staff)->post(route('pets.store'), [
            'name' => 'Milo',
            'breed' => 'Aspin',
            'color' => 'Brown',
            'gender' => 'male',
            'type' => 'dog',
            'photo' => UploadedFile::fake()->create('milo.jpg', 100, 'image/jpeg'),
        ]);
        $createResponse->assertRedirect(route('pets.index'));

        $pet = Pet::where('breed', 'Aspin')->first();
        $this->assertNotNull($pet);

        // 2. Staff can edit pet
        $editResponse = $this->actingAs($this->staff)->patch(route('pets.update', $pet), [
            'breed' => 'Aspin Mix',
            'color' => 'Golden Brown',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);
        $editResponse->assertRedirect(route('pets.index'));
        $this->assertSame('Aspin Mix', $pet->fresh()->breed);

        // 3. Staff CANNOT delete pet (403 Forbidden)
        $staffDeleteResponse = $this->actingAs($this->staff)->delete(route('pets.destroy', $pet));
        $staffDeleteResponse->assertStatus(403);
        $this->assertDatabaseHas('pets', ['id' => $pet->id]);

        // 4. Admin CAN delete pet
        $adminDeleteResponse = $this->actingAs($this->admin)->delete(route('pets.destroy', $pet));
        $adminDeleteResponse->assertRedirect(route('pets.index'));
        $this->assertDatabaseMissing('pets', ['id' => $pet->id]);
    }

    public function test_staff_can_log_medical_entry_but_cannot_delete_medical_log(): void
    {
        $pet = Pet::create([
            'name' => 'Luna',
            'breed' => 'Siamese',
            'color' => 'Cream',
            'gender' => 'female',
            'type' => 'cat',
            'status' => 'available',
        ]);

        // 1. Staff logs medical treatment
        $createResponse = $this->actingAs($this->staff)->post(route('medical-logs.store'), [
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'vaccination',
            'administered_by' => 'Dr. Cruz',
        ]);
        $createResponse->assertRedirect();

        $log = MedicalLog::where('pet_id', $pet->id)->first();
        $this->assertNotNull($log);

        // 2. Staff CANNOT delete medical log (403 Forbidden)
        $staffDeleteResponse = $this->actingAs($this->staff)->delete(route('medical-logs.destroy', $log));
        $staffDeleteResponse->assertStatus(403);
        $this->assertDatabaseHas('medical_logs', ['id' => $log->id]);

        // 3. Admin CAN delete medical log
        $adminDeleteResponse = $this->actingAs($this->admin)->delete(route('medical-logs.destroy', $log));
        $adminDeleteResponse->assertRedirect();
        $this->assertDatabaseMissing('medical_logs', ['id' => $log->id]);
    }

    public function test_staff_cannot_update_adopter_standing_while_admin_can(): void
    {
        $adopterProfile = AdoptersProfile::create([
            'adopter_code' => 'ADP-0001',
            'full_name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '09123456789',
            'address' => 'Cagayan de Oro City',
            'status' => 'active',
        ]);

        // 1. Staff CANNOT update adopter status or sanctions (403 Forbidden)
        $staffResponse = $this->actingAs($this->staff)->patch(route('adopters.update-status', $adopterProfile->id), [
            'status' => 'blacklisted',
            'admin_notes' => 'Suspected neglect',
        ]);
        $staffResponse->assertStatus(403);
        $this->assertSame('active', $adopterProfile->fresh()->status);

        // 2. Admin CAN update adopter status and add admin notes
        $adminResponse = $this->actingAs($this->admin)->patch(route('adopters.update-status', $adopterProfile->id), [
            'status' => 'blacklisted',
            'admin_notes' => 'Confirmed contract violation',
        ]);
        $adminResponse->assertRedirect();
        $this->assertSame('blacklisted', $adopterProfile->fresh()->status);
        $this->assertSame('Confirmed contract violation', $adopterProfile->fresh()->admin_notes);
    }

    public function test_both_staff_and_admin_can_process_adoption_applications(): void
    {
        $pet = Pet::create([
            'name' => 'Barky',
            'breed' => 'Aspin',
            'color' => 'Black',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $app1 = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'applicant_name' => 'Maria Clara',
            'applicant_email' => 'maria@example.com',
            'applicant_phone' => '09111111111',
            'address' => 'CDO',
            'status' => 'pending',
            'message' => 'Proposed Pet Name: Barky',
        ]);

        // Staff can approve adoption application
        $staffApproveResponse = $this->actingAs($this->staff)->patch(route('adoption-applications.update', $app1), [
            'status' => 'approved',
            'event_location' => 'CAWS Center',
            'event_notes' => 'Ready for pickup',
        ]);
        $staffApproveResponse->assertRedirect();
        $this->assertSame('approved', $app1->fresh()->status);

        // Pet is now marked adopted
        $this->assertSame('adopted', $pet->fresh()->status);
    }
}
