<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HostingerApiClient
{
    public function __construct(private readonly string $token) {}

    /** @return array<int, array<string, mixed>> */
    public function websites(): array
    {
        $websites = [];
        $page = 1;

        do {
            $payload = $this->get('/api/hosting/v1/websites', ['page' => $page, 'per_page' => 100]);
            $items = $payload['data'] ?? [];
            $websites = array_merge($websites, is_array($items) ? $items : []);
            $total = (int) ($payload['meta']['total'] ?? count($websites));
            $page++;
        } while (count($websites) < $total && $page <= 100);

        return $websites;
    }

    /** @return array<int, array<string, mixed>> */
    public function domains(): array
    {
        $payload = $this->get('/api/domains/v1/portfolio');

        return array_values($payload['data'] ?? $payload);
    }

    /** @return array<int, array<string, mixed>> */
    public function subscriptions(): array
    {
        $payload = $this->get('/api/billing/v1/subscriptions');

        return array_values($payload['data'] ?? $payload);
    }

    /**
     * @param  array<int, array<string, mixed>>  $websites
     * @return array{details: array<string, array<string, mixed>>, warnings: array<int, string>}
     */
    public function phpDetails(array $websites): array
    {
        $details = [];
        $warnings = [];

        foreach (array_chunk($websites, 10) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk) {
                foreach ($chunk as $website) {
                    $domain = (string) ($website['domain'] ?? '');
                    $username = (string) ($website['username'] ?? '');

                    if ($domain === '' || $username === '') {
                        continue;
                    }

                    $pool->as($domain)
                        ->withToken($this->token)
                        ->acceptJson()
                        ->timeout(20)
                        ->get($this->url('/api/hosting/v1/accounts/'.rawurlencode($username).'/websites/'.rawurlencode($domain).'/php/details'));
                }
            });

            foreach ($responses as $domain => $response) {
                if ($response instanceof Response && $response->successful()) {
                    $payload = $response->json();
                    $details[$domain] = $payload['data'] ?? $payload;
                } else {
                    $warnings[] = 'Version PHP indisponible pour '.$domain.'.';
                }
            }
        }

        return ['details' => $details, 'warnings' => $warnings];
    }

    /** @return array<string, mixed> */
    private function get(string $path, array $query = []): array
    {
        $response = Http::withToken($this->token)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500, throw: false)
            ->get($this->url($path), $query);

        if ($response->unauthorized() || $response->forbidden()) {
            throw new RuntimeException('Le jeton API Hostinger a été refusé. Vérifiez le compte et le jeton.');
        }

        if (! $response->successful()) {
            throw new RuntimeException('Hostinger ne répond pas correctement (erreur '.$response->status().').');
        }

        return $response->json() ?? [];
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.hostinger.base_url'), '/').'/'.ltrim($path, '/');
    }
}
