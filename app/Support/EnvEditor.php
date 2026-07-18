<?php

namespace App\Support;

use RuntimeException;

class EnvEditor
{
    public static function read(): array
    {
        $path = base_path('.env');

        if (! is_file($path)) {
            return [];
        }

        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = self::decode($value);
        }

        return $values;
    }

    public static function write(array $updates): void
    {
        $path = base_path('.env');

        if (! is_file($path)) {
            $example = base_path('.env.example');

            if (! is_file($example) || ! copy($example, $path)) {
                throw new RuntimeException('Le fichier .env ne peut pas être créé.');
            }
        }

        if (! is_writable($path)) {
            throw new RuntimeException('Le fichier .env n’est pas accessible en écriture.');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];
        $written = [];

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key] = explode('=', $line, 2);
            $key = trim($key);

            if (array_key_exists($key, $updates)) {
                $lines[$index] = $key.'='.self::encode($updates[$key]);
                $written[$key] = true;
            }
        }

        foreach ($updates as $key => $value) {
            if (! isset($written[$key])) {
                $lines[] = $key.'='.self::encode($value);
            }
        }

        if (file_put_contents($path, implode(PHP_EOL, $lines).PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException('La configuration ne peut pas être enregistrée.');
        }
    }

    private static function decode(string $value): string
    {
        $value = trim($value);

        if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
            return stripcslashes(substr($value, 1, -1));
        }

        return $value;
    }

    private static function encode(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $value = (string) ($value ?? '');

        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|=|"|\$/', $value)) {
            return '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
        }

        return $value;
    }
}
