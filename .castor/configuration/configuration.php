<?php

namespace configuration;

use function Castor\io;

const CONFIG_KEYS = ['PROJECT_NAME', 'APP_PORT'];

function getEnvFilePath(): string
{
    return dirname(__DIR__, 2) . '/.env.local';
}

function parseEnvFile(string $path): array
{
    if (!file_exists($path)) {
        return [];
    }

    $result = [];

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        $result[$key] = $value;
    }

    return $result;
}

function appendEnvVariables(string $path, array $variables): void
{
    $lines = [];

    foreach ($variables as $key => $value) {
        $value = (string) $value;
        if (str_contains($value, ' ') || str_contains($value, '#')) {
            $value = '"' . $value . '"';
        }
        $lines[] = $key . '=' . $value;
    }

    file_put_contents($path, implode("\n", $lines) . "\n", FILE_APPEND);
}

function removeEnvVariables(string $path, array $keys): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = array_filter(
        file($path, FILE_IGNORE_NEW_LINES),
        static function (string $line) use ($keys): bool {
            foreach ($keys as $key) {
                if (str_starts_with($line, $key . '=')) {
                    return false;
                }
            }
            return true;
        }
    );

    file_put_contents($path, implode("\n", array_values($lines)) . "\n");
}

function hasConfiguration(): bool
{
    $current = parseEnvFile(getEnvFilePath());
    return !empty(array_intersect_key($current, array_flip(CONFIG_KEYS)));
}

function clearConfiguration(): void
{
    removeEnvVariables(getEnvFilePath(), CONFIG_KEYS);
}

function ensureConfiguration(): array
{
    $envFile = getEnvFilePath();
    $current = parseEnvFile($envFile);

    $missingKeys = array_diff(CONFIG_KEYS, array_keys($current));
    if (empty($missingKeys)) {
        return $current;
    }

    io()->title('Configuration du projet');
    io()->writeln('Des variables de configuration sont manquantes dans .env.');
    io()->newLine();

    $prompts = [
        'PROJECT_NAME' => fn() => io()->ask('Nom du projet', 'my-project'),
        'APP_PORT'     => fn() => io()->ask('Port de l\'application', '800'),
    ];

    $newVars = [];
    foreach ($missingKeys as $key) {
        if (isset($prompts[$key])) {
            $newVars[$key] = ($prompts[$key])();
        }
    }

    appendEnvVariables($envFile, $newVars);
    io()->newLine();
    io()->success('Configuration sauvegardée dans .env');

    return array_merge($current, $newVars);
}

function loadConfiguration(): array
{
    return parseEnvFile(getEnvFilePath());
}
