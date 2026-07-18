<section>
    <header>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Double authentification</h2>
                <p class="mt-1 text-sm text-slate-500">Ajoutez un code temporaire à votre mot de passe pour protéger l’accès aux hébergements.</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $user->hasTwoFactorAuthentication() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                {{ $user->hasTwoFactorAuthentication() ? 'Activée' : 'À configurer' }}
            </span>
        </div>
    </header>

    @if (session('warning'))
        <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">{{ session('warning') }}</div>
    @endif

    @if (session('two_factor_recovery_codes'))
        <div class="mt-5 rounded-md border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm font-bold text-amber-950">Enregistrez ces codes de secours maintenant</p>
            <p class="mt-1 text-xs text-amber-800">Ils ne seront plus affichés. Chaque code fonctionne une seule fois.</p>
            <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm font-semibold text-amber-950 sm:grid-cols-4">
                @foreach (session('two_factor_recovery_codes') as $code)
                    <span>{{ $code }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if ($user->hasTwoFactorAuthentication())
        <p class="mt-5 text-sm text-slate-600">La connexion exige désormais le mot de passe et un code généré sur votre téléphone.</p>
        <form method="POST" action="{{ route('two-factor.recovery-codes') }}" class="mt-4 max-w-md">
            @csrf
            <label for="two_factor_management_password" class="ui-label">Mot de passe actuel</label>
            <input id="two_factor_management_password" name="password" type="password" required autocomplete="current-password" class="ui-input">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <div class="mt-3 flex flex-wrap gap-2">
                <button class="ui-button-secondary">Générer de nouveaux codes</button>
                @unless (config('security.two_factor_required'))
                    <button formaction="{{ route('two-factor.disable') }}" class="ui-button-danger" onclick="return confirm('Désactiver la double authentification ?')">Désactiver</button>
                @endunless
            </div>
        </form>
    @elseif (filled($user->two_factor_secret))
        <div class="mt-5 grid gap-5 sm:grid-cols-[220px_1fr] sm:items-center">
            <img src="{{ $twoFactorQrCode }}" alt="QR Code de double authentification" class="h-[220px] w-[220px] rounded-md border border-slate-200 bg-white p-2">
            <div>
                <p class="text-sm font-bold text-slate-900">1. Scannez le QR Code</p>
                <p class="mt-1 text-sm text-slate-500">Utilisez une application d’authentification compatible TOTP.</p>
                <p class="mt-4 text-sm font-bold text-slate-900">2. Confirmez avec le code affiché</p>
                <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-2 flex max-w-sm gap-2">
                    @csrf
                    <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code" required class="ui-input mt-0 font-mono" placeholder="000000">
                    <button class="ui-button-primary">Confirmer</button>
                </form>
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                <details class="mt-4">
                    <summary class="cursor-pointer text-xs font-semibold text-slate-500">Saisie manuelle</summary>
                    <code class="mt-2 block break-all rounded-md bg-slate-100 px-3 py-2 text-xs text-slate-700">{{ $user->two_factor_secret }}</code>
                </details>
            </div>
        </div>
    @else
        <p class="mt-5 text-sm text-slate-600">Vous aurez besoin d’une application comme Google Authenticator, Microsoft Authenticator ou 1Password.</p>
        <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-4 max-w-md">
            @csrf
            <label for="two_factor_enable_password" class="ui-label">Mot de passe actuel</label>
            <input id="two_factor_enable_password" name="password" type="password" required autocomplete="current-password" class="ui-input">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            <button class="ui-button-primary mt-3">Configurer la double authentification</button>
        </form>
    @endif
</section>
