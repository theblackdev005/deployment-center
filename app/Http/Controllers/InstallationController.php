<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\EnvEditor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class InstallationController extends Controller
{
    public function show(): View
    {
        $requirements = $this->requirements();

        return view('installation.index', [
            'env' => EnvEditor::read(),
            'requirements' => $requirements,
            'requirementsPass' => collect($requirements)->every(fn (array $item): bool => $item['ready']),
        ]);
    }

    public function check(): RedirectResponse
    {
        foreach ([storage_path(), storage_path('app'), storage_path('framework'), storage_path('logs'), base_path('bootstrap/cache'), public_path()] as $path) {
            if (is_dir($path) && ! is_writable($path)) {
                @chmod($path, 0775);
            }
        }

        return redirect()->route('installation.show')->with('status', 'Les prérequis ont été vérifiés à nouveau.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:120'],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'database_driver' => ['required', 'in:sqlite,mysql'],
            'database_host' => ['required_if:database_driver,mysql', 'nullable', 'string', 'max:190'],
            'database_port' => ['required_if:database_driver,mysql', 'nullable', 'integer', 'min:1', 'max:65535'],
            'database_name' => ['required_if:database_driver,mysql', 'nullable', 'string', 'max:190'],
            'database_username' => ['required_if:database_driver,mysql', 'nullable', 'string', 'max:190'],
            'database_password' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp'],
            'favicon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,ico'],
        ]);

        if (collect($this->requirements())->contains(fn (array $item): bool => ! $item['ready'])) {
            return back()->withInput()->withErrors(['installation' => 'Corrigez les prérequis du serveur avant de poursuivre.']);
        }

        try {
            $database = $this->configureDatabase($data);
            DB::purge();
            DB::connection()->getPdo();

            $branding = $this->storeBranding($request);
            $local = in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'], true);
            $appUrl = $local ? $request->getSchemeAndHttpHost() : 'https://'.$request->getHost();
            $appKey = EnvEditor::read()['APP_KEY'] ?? '';

            EnvEditor::write(array_merge([
                'APP_NAME' => $data['app_name'],
                'APP_ENV' => $local ? 'local' : 'production',
                'APP_KEY' => $appKey !== '' ? $appKey : 'base64:'.base64_encode(random_bytes(32)),
                'APP_DEBUG' => $local,
                'APP_URL' => $appUrl,
                'APP_CONTACT_EMAIL' => strtolower($data['admin_email']),
                'APP_LOCALE' => 'fr',
                'APP_FALLBACK_LOCALE' => 'fr',
                'TRUSTED_HOSTS' => $request->getHost(),
                'MAIL_FROM_ADDRESS' => strtolower($data['admin_email']),
                'MAIL_FROM_NAME' => $data['app_name'],
                'SESSION_DRIVER' => 'file',
                'CACHE_STORE' => 'file',
            ], $database, $branding));

            config(['app.name' => $data['app_name'], 'app.url' => $appUrl]);
            Artisan::call('migrate', ['--force' => true]);

            $administrator = User::firstOrNew(['email' => strtolower($data['admin_email'])]);
            $administrator->forceFill([
                'name' => $data['admin_name'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ])->save();

            $lock = storage_path('app/installed.lock');
            if (file_put_contents($lock, now()->toIso8601String(), LOCK_EX) === false) {
                throw new \RuntimeException('Le verrou de sécurité ne peut pas être créé.');
            }

            foreach (['config:clear', 'route:clear', 'view:clear', 'cache:clear'] as $command) {
                try {
                    Artisan::call($command);
                } catch (Throwable) {
                    // Le verrou reste la source de vérité si un cache ne peut pas être vidé immédiatement.
                }
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput($request->except(['password', 'password_confirmation', 'database_password']))
                ->withErrors(['installation' => 'L’installation n’a pas pu être terminée. Vérifiez la base de données et les permissions des dossiers.']);
        }

        return redirect()->route('login')->with('status', 'Installation terminée. Vous pouvez maintenant vous connecter.');
    }

    private function configureDatabase(array $data): array
    {
        if ($data['database_driver'] === 'sqlite') {
            $path = database_path('database.sqlite');

            if (! is_file($path) && ! touch($path)) {
                throw new \RuntimeException('La base SQLite ne peut pas être créée.');
            }

            config([
                'database.default' => 'sqlite',
                'database.connections.sqlite.database' => $path,
            ]);

            return [
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => $path,
            ];
        }

        $connection = [
            'host' => $data['database_host'],
            'port' => (int) $data['database_port'],
            'database' => $data['database_name'],
            'username' => $data['database_username'],
            'password' => $data['database_password'] ?? '',
        ];

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $connection['host'],
            'database.connections.mysql.port' => $connection['port'],
            'database.connections.mysql.database' => $connection['database'],
            'database.connections.mysql.username' => $connection['username'],
            'database.connections.mysql.password' => $connection['password'],
        ]);

        return [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $connection['host'],
            'DB_PORT' => $connection['port'],
            'DB_DATABASE' => $connection['database'],
            'DB_USERNAME' => $connection['username'],
            'DB_PASSWORD' => $connection['password'],
        ];
    }

    private function storeBranding(Request $request): array
    {
        $directory = public_path('branding');
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Le dossier des visuels ne peut pas être créé.');
        }

        $updates = [];

        foreach (['logo' => 'APP_LOGO_URL', 'favicon' => 'APP_FAVICON_URL'] as $input => $key) {
            if (! $request->hasFile($input)) {
                continue;
            }

            $file = $request->file($input);
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
            $name = $input.'-'.Str::lower(Str::random(10)).'.'.$extension;
            $file->move($directory, $name);
            $updates[$key] = '/branding/'.$name;
        }

        return $updates;
    }

    private function requirements(): array
    {
        $requirements = [
            'PHP 8.3 ou supérieur' => [
                'value' => PHP_VERSION,
                'ready' => version_compare(PHP_VERSION, '8.3.0', '>='),
            ],
        ];

        foreach (['ctype', 'curl', 'dom', 'fileinfo', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml'] as $extension) {
            $requirements['Extension '.$extension] = [
                'value' => extension_loaded($extension) ? 'Disponible' : 'Manquante',
                'ready' => extension_loaded($extension),
            ];
        }

        $pdoDriverReady = extension_loaded('pdo_mysql') || extension_loaded('pdo_sqlite');
        $requirements['Pilote de base de données'] = [
            'value' => $pdoDriverReady ? 'Disponible' : 'PDO MySQL ou SQLite manquant',
            'ready' => $pdoDriverReady,
        ];

        foreach ([storage_path(), storage_path('app'), storage_path('framework'), storage_path('logs'), base_path('bootstrap/cache'), public_path()] as $path) {
            $label = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
            $requirements[$label] = [
                'value' => is_writable($path) ? 'Accessible en écriture' : 'Permission requise',
                'ready' => is_writable($path),
            ];
        }

        $envReady = is_file(base_path('.env')) ? is_writable(base_path('.env')) : is_writable(base_path());
        $requirements['Fichier .env'] = [
            'value' => $envReady ? 'Prêt' : 'Permission requise',
            'ready' => $envReady,
        ];

        return $requirements;
    }
}
