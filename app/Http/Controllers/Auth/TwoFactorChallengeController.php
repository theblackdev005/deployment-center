<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('two_factor_login')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorAuthenticationService $twoFactor): RedirectResponse
    {
        $pendingLogin = $request->session()->get('two_factor_login');

        if (! is_array($pendingLogin) || ! isset($pendingLogin['user_id'])) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = User::find($pendingLogin['user_id']);
        $valid = $user && (
            filled($request->input('code'))
                ? $twoFactor->verifyCode($user, $request->string('code')->toString())
                : $twoFactor->verifyRecoveryCode($user, $request->string('recovery_code')->toString())
        );

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => 'Le code de sécurité est invalide ou a déjà été utilisé.',
            ]);
        }

        Auth::login($user, (bool) ($pendingLogin['remember'] ?? false));
        $request->session()->forget('two_factor_login');
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
