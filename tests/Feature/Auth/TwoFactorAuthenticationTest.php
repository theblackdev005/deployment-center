<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_enable_and_confirm_two_factor_authentication(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('two-factor.enable'), ['password' => 'password'])
            ->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('QR Code de double authentification');

        $code = (new Google2FA)->getCurrentOtp($user->two_factor_secret);

        $this->actingAs($user)
            ->post(route('two-factor.confirm'), ['code' => $code])
            ->assertRedirect()
            ->assertSessionHas('two_factor_recovery_codes');

        $this->assertTrue($user->fresh()->hasTwoFactorAuthentication());
        $this->assertCount(8, $user->fresh()->two_factor_recovery_codes);
    }

    public function test_two_factor_enabled_user_must_complete_the_login_challenge(): void
    {
        $google2fa = new Google2FA;
        $user = User::factory()->create([
            'two_factor_secret' => $google2fa->generateSecretKey(32),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();

        $this->post(route('two-factor.login'), [
            'code' => $google2fa->getCurrentOtp($user->two_factor_secret),
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_production_policy_requires_two_factor_configuration(): void
    {
        config()->set('security.two_factor_required', true);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('warning');
    }
}
