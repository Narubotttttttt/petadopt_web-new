<?php

namespace Tests\Feature;

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
}
