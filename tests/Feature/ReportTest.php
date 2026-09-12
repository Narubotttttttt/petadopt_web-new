<?php

namespace Tests\Feature;

use App\Models\AdoptionApplication;
use App\Models\MedicalLog;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_export_csv_for_overview(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('reports.export.csv', ['type' => 'overview']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Metric', $content);
        $this->assertStringContainsString('Total Rescued Pets (Intakes)', $content);
        $this->assertStringContainsString('Approved Adoptions', $content);
    }

    public function test_staff_can_export_csv_for_adoptions(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Rusty',
            'breed' => 'Golden Retriever',
            'type' => 'dog',
            'status' => 'available',
        ]);

        AdoptionApplication::create([
            'pet_id' => $pet->id,
            'user_id' => $staff->id,
            'applicant_name' => 'Jane Doe',
            'applicant_email' => 'jane@example.com',
            'applicant_phone' => '09123456789',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('reports.export.csv', ['type' => 'adoptions']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Application ID', $content);
        $this->assertStringContainsString('Jane Doe', $content);
        $this->assertStringContainsString('Rusty', $content);
    }

    public function test_staff_can_export_csv_for_intakes(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        Pet::create([
            'name' => 'Whiskers',
            'breed' => 'Domestic Short Hair',
            'type' => 'cat',
            'status' => 'available',
        ]);

        $response = $this->actingAs($staff)->get(route('reports.export.csv', ['type' => 'intakes']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Pet ID', $content);
        $this->assertStringContainsString('Whiskers', $content);
    }

    public function test_staff_can_export_csv_for_medical(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $pet = Pet::create([
            'name' => 'Charlie',
            'breed' => 'Labrador',
            'type' => 'dog',
            'status' => 'available',
        ]);

        MedicalLog::create([
            'pet_id' => $pet->id,
            'date' => now()->toDateString(),
            'category' => 'vaccination',
            'administered_by' => 'Dr. Vet',
            'created_by' => $staff->id,
        ]);

        $response = $this->actingAs($staff)->get(route('reports.export.csv', ['type' => 'medical']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Log ID', $content);
        $this->assertStringContainsString('Vaccination', $content);
        $this->assertStringContainsString('Charlie', $content);
    }

    public function test_staff_can_export_csv_for_compliance(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('reports.export.csv', ['type' => 'compliance']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Adopter Code', $content);
        $this->assertStringContainsString('Full Name', $content);
    }
}
