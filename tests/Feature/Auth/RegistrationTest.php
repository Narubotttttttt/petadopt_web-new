<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\RegistrationVerificationCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_admin_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Create Admin Account');
        $response->assertSee('Identity and Email');
        $response->assertSee('Set Password');
    }

    public function test_registration_verification_code_can_be_requested(): void
    {
        Notification::fake();

        $response = $this->postJson('/register/send-code', [
            'name' => 'Prospective Admin',
            'email' => 'admin@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $cachedOtp = Cache::get('reg_otp_admin@example.com');
        $this->assertNotNull($cachedOtp);
        $this->assertEquals(6, strlen($cachedOtp));

        Notification::assertSentOnDemand(RegistrationVerificationCodeNotification::class);
    }

    public function test_registration_code_can_be_verified(): void
    {
        Cache::put('reg_otp_admin@example.com', '123456', now()->addMinutes(15));

        $response = $this->postJson('/register/verify-code', [
            'email' => 'admin@example.com',
            'code' => '123456',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertTrue(Cache::get('reg_verified_admin@example.com'));
    }

    public function test_registration_code_fails_with_invalid_digits(): void
    {
        Cache::put('reg_otp_admin@example.com', '123456', now()->addMinutes(15));

        $response = $this->postJson('/register/verify-code', [
            'email' => 'admin@example.com',
            'code' => '999999',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    public function test_step_1_fails_without_verifying_code(): void
    {
        $response = $this->post('/register/step-1', [
            'name' => 'Unverified Admin',
            'email' => 'unverified@example.com',
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull(session('registration_data'));
    }

    public function test_step_1_fails_when_submitting_incorrect_code_even_if_previously_verified(): void
    {
        Cache::put('reg_otp_step1@example.com', '654321', now()->addMinutes(15));
        Cache::put('reg_verified_step1@example.com', true, now()->addMinutes(30));

        $response = $this->post('/register/step-1', [
            'name' => 'Step One User',
            'email' => 'step1@example.com',
            'code' => '999999',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull(session('registration_data'));
        $this->assertNull(Cache::get('reg_verified_step1@example.com'));
    }

    public function test_step_1_succeeds_with_valid_code_and_redirects_to_set_password(): void
    {
        Cache::put('reg_otp_step1@example.com', '654321', now()->addMinutes(15));

        $response = $this->post('/register/step-1', [
            'name' => 'Step One User',
            'email' => 'step1@example.com',
            'code' => '654321',
        ]);

        $response->assertRedirect(route('register.set-password'));
        $response->assertSessionHas('registration_data', [
            'name' => 'Step One User',
            'email' => 'step1@example.com',
            'role' => 'admin',
        ]);
        $this->assertTrue(Cache::get('reg_verified_step1@example.com'));
    }

    public function test_set_password_screen_redirects_to_step_1_if_no_session(): void
    {
        $response = $this->get('/register/set-password');

        $response->assertRedirect(route('register'));
        $response->assertSessionHas('info');
    }

    public function test_set_password_screen_renders_when_session_exists(): void
    {
        $response = $this->withSession([
            'registration_data' => [
                'name' => 'Verified Admin Candidate',
                'email' => 'candidate@example.com',
                'role' => 'admin',
            ],
        ])->get('/register/set-password');

        $response->assertStatus(200);
        $response->assertSee('Set Admin Password');
        $response->assertSee('Verified Admin Candidate');
        $response->assertSee('candidate@example.com');
        $response->assertSee('Complete Admin Registration');
    }

    public function test_set_password_validates_password_confirmation(): void
    {
        $response = $this->withSession([
            'registration_data' => [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ],
        ])->post('/register/set-password', [
            'password' => 'password123',
            'password_confirmation' => 'mismatch-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_set_password_completes_admin_registration_and_logs_in(): void
    {
        Cache::put('reg_verified_admin@example.com', true, now()->addMinutes(30));

        $response = $this->withSession([
            'registration_data' => [
                'name' => 'Initial Administrator',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ],
        ])->post('/register/set-password', [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertNotNull($admin->staffProfile);
        $this->assertEquals('ADM-0001', $admin->staffProfile->staff_code);
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('registration_data'));
    }

    public function test_set_password_completes_staff_registration_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->withSession([
            'registration_data' => [
                'name' => 'Staff Member',
                'email' => 'staff@example.com',
                'role' => 'staff',
            ],
        ])->post('/register/set-password', [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.com',
            'role' => 'staff',
        ]);

        $staff = User::where('email', 'staff@example.com')->first();
        $this->assertNotNull($staff);
        $this->assertNotNull($staff->email_verified_at);
        $this->assertTrue($staff->hasVerifiedEmail());
        $this->assertNotNull($staff->staffProfile);
        $this->assertEquals('STF-0002', $staff->staffProfile->staff_code);

        // Admin remains authenticated
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('registration_data'));
    }

    public function test_guest_cannot_access_set_password_when_admin_already_exists(): void
    {
        User::factory()->create(['role' => 'admin']);

        $response = $this->withSession([
            'registration_data' => [
                'name' => 'Intruder',
                'email' => 'intruder@example.com',
                'role' => 'admin',
            ],
        ])->get('/register/set-password');

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info', 'Public registration is closed. Please contact your shelter administrator for portal access.');

        $postResponse = $this->withSession([
            'registration_data' => [
                'name' => 'Intruder',
                'email' => 'intruder@example.com',
                'role' => 'admin',
            ],
        ])->post('/register/set-password', [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $postResponse->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['email' => 'intruder@example.com']);
    }

    public function test_registration_fails_without_verifying_code(): void
    {
        $response = $this->post('/register', [
            'name' => 'Unverified Admin',
            'email' => 'unverified@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('verification_code');
        $this->assertDatabaseMissing('users', ['email' => 'unverified@example.com']);
    }

    public function test_initial_admin_can_register_with_verified_code_and_is_immediately_active(): void
    {
        Cache::put('reg_verified_admin@example.com', true, now()->addMinutes(30));

        $response = $this->post('/register', [
            'name' => 'Initial Administrator',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue($admin->hasVerifiedEmail());
        $this->assertNotNull($admin->staffProfile);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_guest_cannot_register_when_admin_already_exists(): void
    {
        User::factory()->create(['role' => 'admin']);

        $response = $this->get('/register');
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info', 'Public registration is closed. Please contact your shelter administrator for portal access.');

        $postResponse = $this->post('/register', [
            'name' => 'Sneaky Guest',
            'email' => 'guest@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $postResponse->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['email' => 'guest@example.com']);
    }

    public function test_admin_can_create_staff_account_with_verified_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Cache::put('reg_verified_staff@example.com', true, now()->addMinutes(30));

        $response = $this->actingAs($admin)->post('/register', [
            'name' => 'Staff Member',
            'email' => 'staff@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'staff@example.com',
            'role' => 'staff',
        ]);

        $staff = User::where('email', 'staff@example.com')->first();
        $this->assertNotNull($staff);
        $this->assertNotNull($staff->email_verified_at);
        $this->assertTrue($staff->hasVerifiedEmail());
        $this->assertNotNull($staff->staffProfile);

        // Admin remains authenticated
        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_cannot_access_registration(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->get('/register');

        $response->assertStatus(403);
    }
}
