<?php

$appHost = parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
$trustedHosts = array_filter(array_map('trim', explode(',', (string) env('TRUSTED_HOSTS', $appHost))));

if (env('APP_ENV', 'production') === 'local') {
    $trustedHosts = array_unique([...$trustedHosts, 'localhost', '127.0.0.1']);
}

return [
    'two_factor_required' => (bool) env('TWO_FACTOR_REQUIRED', false),
    'trusted_hosts' => array_map(
        fn (string $host) => '^(.+\.)?'.preg_quote($host, '/').'$',
        $trustedHosts,
    ),
];
