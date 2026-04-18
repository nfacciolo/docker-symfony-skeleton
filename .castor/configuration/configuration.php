<?php

namespace configuration;

use function Castor\io;

const CONFIG_KEYS = ['PROJECT_NAME', 'APP_USER', 'CREATE_DATABASE', 'ENV'];
const DB_CONFIG_KEYS = ['DATABASE_USERNAME', 'DATABASE_PASSWORD', 'DATABASE_NAME'];

function getEnvFilePath(): string
{
    return dirname(__DIR__, 2) . '/.env.castor';
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
    removeEnvVariables(getEnvFilePath(), array_merge(CONFIG_KEYS, DB_CONFIG_KEYS));
}

function ensureConfiguration(): array
{
    $envFile = getEnvFilePath();
    $current = parseEnvFile($envFile);

    $missingMain = array_filter(CONFIG_KEYS, fn($key) => !array_key_exists($key, $current));
    $createDb = ($current['CREATE_DATABASE'] ?? 'false') === 'true';
    $missingDb = $createDb
        ? array_filter(DB_CONFIG_KEYS, fn($key) => !array_key_exists($key, $current))
        : [];

    if (empty($missingMain) && empty($missingDb)) {
        return $current;
    }

    io()->title('Configuration du projet');
    io()->writeln('Des variables de configuration sont manquantes dans .env.');
    io()->writeln('Veuillez répondre aux questions suivantes pour configurer votre projet.');
    io()->newLine();

    $newVars = [];

    if (!array_key_exists('PROJECT_NAME', $current)) {
        $newVars['PROJECT_NAME'] = io()->ask('Nom du projet', 'my-project');
    }

    if (!array_key_exists('APP_USER', $current)) {
        $newVars['APP_USER'] = io()->ask("Nom de l'utilisateur système dans le container (APP_USER)", 'symfony');
    }

    if (!array_key_exists('CREATE_DATABASE', $current)) {
        $confirmed = io()->confirm('Créer une base de données ?', false);
        $newVars['CREATE_DATABASE'] = $confirmed ? 'true' : 'false';
        $current['CREATE_DATABASE'] = $newVars['CREATE_DATABASE'];
        $createDb = $confirmed;
    }

    if (!array_key_exists('ENV', $current)) {
        $newVars['ENV'] = io()->choice('Environnement', ['dev', 'prod', 'test'], 'dev');
    }

    if ($createDb) {
        if (!array_key_exists('DATABASE_USERNAME', $current)) {
            $newVars['DATABASE_USERNAME'] = io()->ask("Nom d'utilisateur de la base de données", 'root');
        }

        if (!array_key_exists('DATABASE_PASSWORD', $current)) {
            $newVars['DATABASE_PASSWORD'] = io()->askHidden('Mot de passe de la base de données') ?: 'secret';
        }

        if (!array_key_exists('DATABASE_NAME', $current)) {
            $newVars['DATABASE_NAME'] = io()->ask('Nom de la base de données', 'symfony_db');
        }
    }

    if (!empty($newVars)) {
        appendEnvVariables($envFile, $newVars);
        io()->newLine();
        io()->success('Configuration sauvegardée dans .env');
    }

    return array_merge($current, $newVars);
}

function loadConfiguration(): array
{
    return parseEnvFile(getEnvFilePath());
}
