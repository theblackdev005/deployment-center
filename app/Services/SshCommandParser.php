<?php

namespace App\Services;

use InvalidArgumentException;

class SshCommandParser
{
    /**
     * @return array{host: string, port: int, username: string}
     */
    public function parse(string $command): array
    {
        $parts = preg_split('/\s+/', trim($command)) ?: [];

        if (array_shift($parts) !== 'ssh') {
            throw new InvalidArgumentException('La commande doit commencer par ssh.');
        }

        $port = 22;
        $connection = null;

        for ($index = 0; $index < count($parts); $index++) {
            if ($parts[$index] === '-p') {
                $port = (int) ($parts[++$index] ?? 0);
                continue;
            }

            if (str_contains($parts[$index], '@')) {
                $connection = $parts[$index];
                continue;
            }

            throw new InvalidArgumentException('La commande SSH contient une option non reconnue.');
        }

        if (! $connection || ! preg_match('/^([A-Za-z0-9._-]+)@([A-Za-z0-9.-]+)$/', $connection, $matches)) {
            throw new InvalidArgumentException('Le format utilisateur@serveur est invalide.');
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('Le port SSH est invalide.');
        }

        return [
            'username' => $matches[1],
            'host' => $matches[2],
            'port' => $port,
        ];
    }
}
