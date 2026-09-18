<?php

namespace Tests\Feature;

use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_pet_history_page(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Max',
            'breed' => 'Golden Retriever',
            'color' => 'Golden',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'available',
        ]);

        MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'vaccination',
            'administered_by' => 'Dr. Smith',
            'created_by' => $staff->id,
        ]);

        $response = $this->actingAs($staff)->get(route('pet-history.index'));

        $response->assertStatus(200);
        $response->assertSee('Pet History');
        $response->assertSee('Pets in History');
        $response->assertDontSee('Historical Rescues');
        $response->assertSee('Max');
        $response->assertSee('Vaccination');
        $response->assertDontSee('petHistoryCombinedChart');
    }

    public function test_staff_can_filter_pet_history_by_event_type_and_species(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $dog = Pet::create([
            'name' => 'Bella',
            'breed' => 'Beagle',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $cat = Pet::create([
            'name' => 'Luna',
            'breed' => 'Persian',
            'type' => 'cat',
            'status' => 'available',
        ]);

        $response = $this->actingAs($staff)->get(route('pet-history.index', [
            'species' => 'cat',
            'event_type' => 'intake',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Luna');
        $response->assertDontSee('Bella');
    }

    public function test_dashboard_renders_successfully(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Rocky',
            'breed' => 'Bulldog',
            'type' => 'dog',
            'status' => 'available',
        ]);

        $response = $this->actingAs($staff)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Adoption Trends');
        $response->assertDontSee('dashboardPetHistoryChart');
    }

    public function test_guest_cannot_access_pet_history(): void
    {
        $response = $this->get(route('pet-history.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_pet_history_event_details_payload_and_responsive_elements_render(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Charlie',
            'breed' => 'Labrador',
            'color' => 'Black',
            'gender' => 'male',
            'type' => 'dog',
            'status' => 'adopted',
            'description' => 'Rescued from street care.',
        ]);

        $app = AdoptionApplication::create([
            'pet_id' => $pet->id,
            'user_id' => $staff->id,
            'applicant_name' => 'Jane Doe',
            'applicant_email' => 'jane@example.com',
            'applicant_phone' => '09123456789',
            'id_type' => 'Passport',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'checkup',
            'administered_by' => 'Dr. Vet',
            'created_by' => $staff->id,
            'description' => 'Overall healthy examination.',
        ]);

        $response = $this->actingAs($staff)->get(route('pet-history.index'));

        $response->assertStatus(200);
        $response->assertSee('Charlie');
        $response->assertSee('Jane Doe');
        $response->assertSee('Dr. Vet');
        // Check for modal elements and responsive views
        $response->assertSee('modalTab');
        $response->assertSee('openDetail');
        $response->assertSee('history-detail-modal-title');
        $response->assertSee('hidden lg:block', false);
        $response->assertSee('block lg:hidden', false);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $events */
        $events = $response->viewData('events');
        $this->assertNotEmpty($events);
        foreach ($events as $event) {
            $this->assertArrayHasKey('id', $event);
            $this->assertArrayHasKey('intake_date', $event);
            $this->assertArrayHasKey('pet_description', $event);
            $this->assertArrayHasKey('timeline', $event);
            $this->assertNotEmpty($event['timeline']);
        }
    }

    public function test_staff_can_filter_pet_history_by_status(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        Pet::create([
            'name' => 'Rusty',
            'breed' => 'Poodle',
            'type' => 'dog',
            'status' => 'available',
        ]);

        Pet::create([
            'name' => 'Milo',
            'breed' => 'Siamese',
            'type' => 'cat',
            'status' => 'adopted',
        ]);

        $responseAdopted = $this->actingAs($staff)->get(route('pet-history.index', ['status' => 'adopted']));
        $responseAdopted->assertStatus(200);
        $responseAdopted->assertSee('Milo');
        $responseAdopted->assertDontSee('Rusty');

        $responseShelter = $this->actingAs($staff)->get(route('pet-history.index', ['status' => 'in_shelter']));
        $responseShelter->assertStatus(200);
        $responseShelter->assertSee('Rusty');
        $responseShelter->assertDontSee('Milo');
    }
}

