<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorAuthenticationService
{
    public function __construct(private readonly Google2FA $google2fa = new Google2FA) {}

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey(32);
    }

    public function qrCode(User $user): string
    {
        return $this->google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $user->two_factor_secret,
            220,
        );
    }

    public function verifyCode(User $user, string $code): bool
    {
        $step = $this->google2fa->verifyKeyNewer(
            $user->two_factor_secret,
            preg_replace('/\D/', '', $code),
            $user->two_factor_last_used_step,
            1,
        );

        if ($step === false) {
            return false;
        }

        $user->forceFill(['two_factor_last_used_step' => $step])->save();

        return true;
    }

    /** @return array<int, string> */
    public function regenerateRecoveryCodes(User $user): array
    {
        $codes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5).'-'.Str::random(5)))
            ->all();

        $user->forceFill([
            'two_factor_recovery_codes' => array_map(fn (string $code) => Hash::make($code), $codes),
        ])->save();

        return $codes;
    }

    public function verifyRecoveryCode(User $user, string $recoveryCode): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        foreach ($codes as $index => $hash) {
            if (! Hash::check(Str::upper(trim($recoveryCode)), $hash)) {
                continue;
            }

            unset($codes[$index]);
            $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

            return true;
        }

        return false;
    }
}
