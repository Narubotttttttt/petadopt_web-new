<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\AdminStaffVerifyEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertSee('Verify Your Email');
    }

    public function test_unverified_user_is_redirected_to_verify_email_when_accessing_dashboard(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified_by_authenticated_user(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'staff']);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_can_be_verified_by_guest_from_mobile_link(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'staff']);

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Guest accesses link without session
        $response = $this->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'staff']);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertStatus(403);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_can_be_verified_via_6_digit_otp(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'staff']);

        Event::fake();
        Cache::put('email_verification_otp_' . $user->id, '654321', now()->addMinutes(60));

        $response = $this->actingAs($user)->post('/verify-email/otp', [
            'otp' => '654321',
        ]);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertNull(Cache::get('email_verification_otp_' . $user->id));
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_fails_verification_with_invalid_otp(): void
    {
        $user = User::factory()->unverified()->create(['role' => 'staff']);

        Cache::put('email_verification_otp_' . $user->id, '654321', now()->addMinutes(60));

        $response = $this->actingAs($user)->post('/verify-email/otp', [
            'otp' => '111111',
        ]);

        $response->assertSessionHasErrors('otp');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_admin_can_resend_verification_email_for_unverified_staff(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->unverified()->create(['role' => 'staff']);

        $response = $this->actingAs($admin)->post("/users/{$staff->id}/resend-verification");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Notification::assertSentTo($staff, AdminStaffVerifyEmail::class);
    }

    public function test_admin_cannot_resend_verification_for_already_verified_staff(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($admin)->post("/users/{$staff->id}/resend-verification");

        $response->assertRedirect();
        $response->assertSessionHas('info');

        Notification::assertNothingSent();
    }
}
