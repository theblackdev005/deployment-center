<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\EnvEditor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class InstallDeploymentCenter extends Command
{
    protected $signature = 'deployment-center:install
        {--app-name= : Nom de la plateforme}
        {--admin-name= : Nom de l’administrateur}
        {--admin-email= : Adresse email de l’administrateur}';

    protected $description = 'Prépare la base et crée le premier administrateur de manière sécurisée';

    public function handle(): int
    {
        $appName = $this->option('app-name') ?: env('APP_NAME') ?: $this->ask('Nom de la plateforme', 'Deploy Center');

        if (blank(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
            if (preg_match('/^APP_KEY=(.*)$/m', file_get_contents(base_path('.env')), $matches)) {
                config()->set('app.key', trim($matches[1], " \t\n\r\0\x0B\"'"));
            }
            $this->info('Clé de chiffrement générée.');
        }

        Artisan::call('migrate', ['--force' => true]);
        $this->info('Base de données mise à jour.');

        if (! User::exists()) {
            $name = $this->option('admin-name') ?: env('ADMIN_NAME') ?: $this->ask('Nom de l’administrateur', 'Administrateur');
            $email = $this->option('admin-email') ?: env('ADMIN_EMAIL') ?: $this->ask('Email de l’administrateur');
            $password = env('ADMIN_PASSWORD') ?: $this->secret('Mot de passe administrateur (8 caractères minimum)');

            $validated = Validator::make(compact('name', 'email', 'password'), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', Password::defaults()],
            ])->validate();

            $administrator = new User;
            $administrator->forceFill([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ])->save();
            EnvEditor::write([
                'APP_NAME' => $appName,
                'APP_CONTACT_EMAIL' => strtolower($validated['email']),
                'MAIL_FROM_ADDRESS' => strtolower($validated['email']),
                'MAIL_FROM_NAME' => $appName,
            ]);
            $this->info('Compte administrateur créé.');
        } else {
            $this->line('Un compte administrateur existe déjà : aucune donnée de connexion n’a été modifiée.');
        }

        if (file_put_contents(storage_path('app/installed.lock'), now()->toIso8601String(), LOCK_EX) === false) {
            $this->error('Le verrou de sécurité de l’installation ne peut pas être créé.');

            return self::FAILURE;
        }

        Artisan::call('optimize');
        $this->info('Installation terminée. Connectez-vous puis activez immédiatement la double authentification.');
        $this->newLine();
        $this->line('Cron requis : * * * * * php '.base_path('artisan').' schedule:run > /dev/null 2>&1');

        return self::SUCCESS;
    }
}
