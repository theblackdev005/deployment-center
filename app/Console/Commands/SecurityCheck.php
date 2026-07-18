<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SecurityCheck extends Command
{
    protected $signature = 'deployment-center:security-check';

    protected $description = 'Vérifie les réglages essentiels avant la mise en production';

    public function handle(): int
    {
        $checks = [
            ['Environnement production', app()->isProduction()],
            ['Mode debug désactivé', ! config('app.debug')],
            ['Adresse HTTPS', str_starts_with((string) config('app.url'), 'https://')],
            ['Clé APP_KEY présente', filled(config('app.key'))],
            ['Cookies de session HTTPS', (bool) config('session.secure')],
            ['Sessions chiffrées', (bool) config('session.encrypt')],
            ['Session de 60 minutes maximum', (int) config('session.lifetime') <= 60],
            ['Double authentification obligatoire', (bool) config('security.two_factor_required')],
            ['Domaines de confiance configurés', count(config('security.trusted_hosts', [])) > 0],
        ];

        $this->table(['Contrôle', 'État'], array_map(
            fn (array $check) => [$check[0], $check[1] ? 'OK' : 'À corriger'],
            $checks,
        ));

        if (collect($checks)->contains(fn (array $check) => ! $check[1])) {
            $this->error('La configuration ne doit pas encore être exposée sur Internet.');

            return self::FAILURE;
        }

        $this->info('Les réglages essentiels de production sont conformes.');

        return self::SUCCESS;
    }
}
