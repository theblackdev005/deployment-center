<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TwoFactorSettingsController extends Controller
{
    public function enable(Request $request, TwoFactorAuthenticationService $twoFactor): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => $twoFactor->generateSecret(),
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_used_step' => null,
        ])->save();

        return back()->with('status', 'two-factor-pending');
    }

    public function confirm(Request $request, TwoFactorAuthenticationService $twoFactor): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);
        $user = $request->user();

        if (! filled($user->two_factor_secret) || ! $twoFactor->verifyCode($user, $validated['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Le code saisi est invalide. Vérifiez l’heure de votre téléphone et réessayez.',
            ]);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        $codes = $twoFactor->regenerateRecoveryCodes($user);

        return back()
            ->with('status', 'two-factor-enabled')
            ->with('two_factor_recovery_codes', $codes);
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_last_used_step' => null,
        ])->save();

        return back()->with('status', 'two-factor-disabled');
    }

    public function regenerateRecoveryCodes(Request $request, TwoFactorAuthenticationService $twoFactor): RedirectResponse
    {
        abort_unless($request->user()->hasTwoFactorAuthentication(), 403);
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        return back()
            ->with('status', 'two-factor-recovery-codes-regenerated')
            ->with('two_factor_recovery_codes', $twoFactor->regenerateRecoveryCodes($request->user()));
    }
}
