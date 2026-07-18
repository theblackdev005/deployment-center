@extends('layouts.installation')

@section('content')
<main class="shell">
    <div class="panel">
        <header>
            <div class="head-copy">
                <span class="mark">DC</span>
                <div><h1>Installation de la plateforme</h1><p>Une configuration simple avant la première connexion</p></div>
            </div>
            <strong>Laravel {{ app()->version() }}</strong>
        </header>

        <div class="content">
            @if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
            @if($errors->any())
                <div class="errors"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <h2>Prérequis du serveur</h2>
            <p class="lead">Tous les éléments ci-dessous doivent être disponibles avant de lancer l’installation.</p>
            <table class="requirements"><tbody>
                @foreach($requirements as $name => $requirement)
                    <tr><td>{{ $name }}</td><td class="{{ $requirement['ready'] ? 'ready' : 'missing' }}">{{ $requirement['value'] }}</td></tr>
                @endforeach
            </tbody></table>
            @unless($requirementsPass)
                <form method="post" action="{{ route('installation.check') }}" class="actions">@csrf<button type="submit" class="secondary">Vérifier à nouveau</button></form>
            @else
                @php($selectedDriver = old('database_driver', ($env['DB_CONNECTION'] ?? 'sqlite') === 'mysql' ? 'mysql' : 'sqlite'))
                <form method="post" action="{{ route('installation.store') }}" enctype="multipart/form-data">
                    @csrf
                    <h3>Identité de la plateforme</h3>
                    <p class="lead">Le nom et l’email seront utilisés dans le tableau de bord et les notifications.</p>
                    <div class="grid">
                        <div><label for="app_name">Nom du projet</label><input id="app_name" name="app_name" value="{{ old('app_name', $env['APP_NAME'] ?? 'Deploy Center') }}" required></div>
                        <div><label for="admin_email">Adresse email</label><input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email', $env['APP_CONTACT_EMAIL'] ?? $env['ADMIN_EMAIL'] ?? '') }}" required></div>
                    </div>

                    <h3>Compte administrateur</h3>
                    <p class="lead">Ce sera le seul compte créé automatiquement.</p>
                    <div class="grid">
                        <div class="full"><label for="admin_name">Nom de l’administrateur</label><input id="admin_name" name="admin_name" value="{{ old('admin_name', $env['ADMIN_NAME'] ?? 'Administrateur') }}" required></div>
                        <div><label for="password">Mot de passe</label><input id="password" type="password" name="password" autocomplete="new-password" required><span class="hint">8 caractères minimum avec majuscule, minuscule, chiffre et symbole.</span></div>
                        <div><label for="password_confirmation">Confirmer le mot de passe</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required></div>
                    </div>

                    <h3>Base de données</h3>
                    <p class="lead">SQLite convient à une installation simple. MySQL est recommandé si votre hébergement fournit déjà une base.</p>
                    <div class="choice">
                        <label><input type="radio" name="database_driver" value="sqlite" @checked($selectedDriver === 'sqlite')><span><strong>Installation simple</strong><span>La base est créée automatiquement.</span></span></label>
                        <label><input type="radio" name="database_driver" value="mysql" @checked($selectedDriver === 'mysql')><span><strong>Base MySQL</strong><span>Utilisez les accès fournis par l’hébergeur.</span></span></label>
                    </div>
                    <div class="grid" id="mysql-fields" style="margin-top:18px; {{ $selectedDriver === 'mysql' ? '' : 'display:none' }}">
                        <div><label for="database_host">Serveur</label><input id="database_host" name="database_host" value="{{ old('database_host', $env['DB_HOST'] ?? '127.0.0.1') }}"></div>
                        <div><label for="database_port">Port</label><input id="database_port" type="number" name="database_port" value="{{ old('database_port', $env['DB_PORT'] ?? '3306') }}"></div>
                        <div><label for="database_name">Nom de la base</label><input id="database_name" name="database_name" value="{{ old('database_name', $env['DB_DATABASE'] ?? '') }}"></div>
                        <div><label for="database_username">Utilisateur</label><input id="database_username" name="database_username" value="{{ old('database_username', $env['DB_USERNAME'] ?? '') }}"></div>
                        <div class="full"><label for="database_password">Mot de passe de la base</label><input id="database_password" type="password" name="database_password" autocomplete="new-password"></div>
                    </div>

                    <h3>Logo et favicon</h3>
                    <p class="lead">Les dimensions sont libres. Les visuels pourront être remplacés plus tard si nécessaire.</p>
                    <div class="visuals">
                        <div><label for="logo">Logo</label><div class="preview" id="logo-preview">Aperçu du logo</div><input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp"></div>
                        <div><label for="favicon">Favicon</label><div class="preview" id="favicon-preview">Aperçu du favicon</div><input id="favicon" type="file" name="favicon" accept="image/png,image/jpeg,image/webp,image/x-icon"></div>
                    </div>

                    <div class="actions">
                        <span class="hint">L’assistant sera définitivement verrouillé après validation.</span>
                        <button type="submit">Installer la plateforme</button>
                    </div>
                </form>
            @endunless
        </div>
    </div>
</main>

<script>
    const mysqlFields = document.getElementById('mysql-fields');
    for (const radio of document.querySelectorAll('input[name="database_driver"]')) {
        radio.addEventListener('change', () => {
            mysqlFields.style.display = radio.checked && radio.value === 'mysql' ? 'grid' : 'none';
        });
    }

    for (const inputName of ['logo', 'favicon']) {
        const input = document.getElementById(inputName);
        const preview = document.getElementById(`${inputName}-preview`);
        input?.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;
            const image = document.createElement('img');
            image.src = URL.createObjectURL(file);
            image.alt = `Aperçu du ${inputName}`;
            preview.replaceChildren(image);
        });
    }
</script>
@endsection
