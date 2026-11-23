<?php

namespace Tests\Feature;

use App\Models\AdoptionApplication;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_pdf_report(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('reports.export.pdf', ['type' => 'overview']));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_staff_cannot_export_pdf_report(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('reports.export.pdf', ['type' => 'overview']));

        $response->assertStatus(403);
    }

    public function test_csv_export_route_is_removed(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/reports/export/csv?type=overview');

        $response->assertStatus(404);
    }

    public function test_staff_can_view_reports_index_without_export_pdf_button(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($staff)->get(route('reports.index', ['type' => 'overview']));

        $response->assertStatus(200);
        $response->assertSee('System Reports');
        $response->assertDontSee('Export PDF');
        $response->assertDontSee('Export CSV');
        $response->assertDontSee('Print</span>', false);
    }

    public function test_admin_sees_export_pdf_button_on_reports_index(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('reports.index', ['type' => 'overview']));

        $response->assertStatus(200);
        $response->assertSee('System Reports');
        $response->assertSee('Export PDF');
        $response->assertDontSee('Export CSV');
        $response->assertDontSee('Print</span>', false);
    }

    public function test_compliance_tag_is_removed_and_defaults_to_overview(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Overview');
        $response->assertSee('Adoptions');
        $response->assertSee('Pet Inventory');
        $response->assertDontSee('Compliance</a>', false);
        $response->assertDontSee('Adopter Compliance Records');

        // Accessing type=compliance falls back cleanly to overview
        $responseCompliance = $this->actingAs($admin)->get(route('reports.index', ['type' => 'compliance']));
        $responseCompliance->assertStatus(200);
        $responseCompliance->assertSee('Live Shelter Census');
        $responseCompliance->assertDontSee('Adopter Compliance Records');
    }

    public function test_adoptions_pdf_export_includes_only_finalized_adoptions_and_no_color_tags(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $pet1 = Pet::create([
            'name' => 'Charlie Finalized',
            'type' => 'dog',
            'breed' => 'Aspin',
            'status' => 'adopted',
        ]);

        $pet2 = Pet::create([
            'name' => 'Bella Pending',
            'type' => 'cat',
            'breed' => 'Puspin',
            'status' => 'in_shelter',
        ]);

        $pet3 = Pet::create([
            'name' => 'Max Scheduled',
            'type' => 'dog',
            'breed' => 'Aspin',
            'status' => 'in_shelter',
        ]);

        $pet4 = Pet::create([
            'name' => 'Luna Rejected',
            'type' => 'cat',
            'breed' => 'Puspin',
            'status' => 'in_shelter',
        ]);

        // 1. Finalized adoption: approved and documents verified
        $finalized = AdoptionApplication::create([
            'pet_id' => $pet1->id,
            'applicant_name' => 'Alice Finalized',
            'applicant_email' => 'alice@example.com',
            'applicant_phone' => '09171111111',
            'status' => 'approved',
            'approved_at' => now(),
            'documents_verified_at' => now(),
        ]);

        // 2. Pending adoption: pending
        $pending = AdoptionApplication::create([
            'pet_id' => $pet2->id,
            'applicant_name' => 'Bob Pending',
            'applicant_email' => 'bob@example.com',
            'applicant_phone' => '09172222222',
            'status' => 'pending',
        ]);

        // 3. Scheduled / unfinalized adoption: approved but documents NOT verified
        $scheduled = AdoptionApplication::create([
            'pet_id' => $pet3->id,
            'applicant_name' => 'Charlie Scheduled',
            'applicant_email' => 'charlie@example.com',
            'applicant_phone' => '09173333333',
            'status' => 'approved',
            'approved_at' => now(),
            'documents_verified_at' => null,
        ]);

        // 4. Rejected adoption
        $rejected = AdoptionApplication::create([
            'pet_id' => $pet4->id,
            'applicant_name' => 'David Rejected',
            'applicant_email' => 'david@example.com',
            'applicant_phone' => '09174444444',
            'status' => 'rejected',
        ]);

        // Export PDF stream as admin
        $response = $this->actingAs($admin)->get(route('reports.export.pdf', ['type' => 'adoptions']));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));

        // Direct view rendering check with export data to verify content exclusions and clean tags
        $controller = app(\App\Http\Controllers\ReportController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('generateReportData');
        $method->setAccessible(true);

        $request = \Illuminate\Http\Request::create(route('reports.export.pdf'), 'GET', ['type' => 'adoptions']);
        $reportData = $method->invoke($controller, $request, true);

        $viewHtml = view('pdf.report', array_merge($reportData, [
            'generatedByName' => $admin->name,
            'generatedByRole' => 'Administrator',
            'generatedAt'     => now()->format('F d, Y h:i A'),
            'logoBase64'      => null,
            'signatureBase64' => null,
        ]))->render();

        // Finalized adoption must be present
        $this->assertStringContainsString('Alice Finalized', $viewHtml);
        $this->assertStringContainsString('Charlie Finalized', $viewHtml);
        $this->assertStringContainsString('Finalized', $viewHtml);

        // Non-finalized applications must NOT be present
        $this->assertStringNotContainsString('Bob Pending', $viewHtml);
        $this->assertStringNotContainsString('Charlie Scheduled', $viewHtml);
        $this->assertStringNotContainsString('David Rejected', $viewHtml);

        // Color badge tags must be completely removed
        $this->assertStringNotContainsString('badge-approved', $viewHtml);
        $this->assertStringNotContainsString('badge-pending', $viewHtml);
        $this->assertStringNotContainsString('badge-rejected', $viewHtml);
        $this->assertStringNotContainsString('badge-available', $viewHtml);
        $this->assertStringNotContainsString('badge-inshelter', $viewHtml);
        $this->assertStringNotContainsString('badge-adopted', $viewHtml);
        $this->assertStringNotContainsString('badge-deceased', $viewHtml);
        $this->assertStringNotContainsString('badge-neutral', $viewHtml);
    }
}
