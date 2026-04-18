<?php

namespace install;

use Castor\Attribute\AsTask;
use function Castor\io;
use function Castor\run;

#[AsTask(description: 'Install Doctrine ORM and database packages')]
function database(): void
{
    io()->title('Installation des packages base de données...');
    run('docker compose exec app composer require symfony/orm-pack');
    io()->writeln('Démarrage du service database...');
    run('docker compose --profile db up -d --wait database');
    io()->writeln('Test de connexion à la base de données...');
    run('docker compose exec app bin/console dbal:run-sql "SELECT 1"');
    io()->success('Connexion à la base de données établie.');
    io()->success('Packages base de données installés.');
}

#[AsTask(name: 'database', namespace: 'remove', description: 'Remove Doctrine ORM and database packages')]
function removeDatabase(): void
{
    io()->title('Suppression des packages base de données...');
    run('docker compose exec app composer remove symfony/orm-pack');
    io()->success('Packages base de données supprimés.');
}

#[AsTask(description: 'Install dev tools (maker-bundle, profiler-pack)')]
function dev(): void
{
    io()->title('Installation des outils de développement...');
    run('docker compose exec app composer require --dev symfony/maker-bundle symfony/profiler-pack');
    io()->success('Outils de développement installés.');
}

#[AsTask(description: 'Install web stack (twig-pack, security-bundle, form, validator)')]
function web(): void
{
    io()->title('Installation du stack web...');
    run('docker compose exec app composer require symfony/twig-pack symfony/security-bundle symfony/form symfony/validator');
    io()->success('Stack web installé.');
}

#[AsTask(description: 'Install a composer package')]
function package(string $name): void
{
    run(sprintf('docker compose exec app composer require %s', escapeshellarg($name)));
}

#[AsTask(name: 'package', namespace: 'remove', description: 'Remove a composer package')]
function removePackage(string $name): void
{
    run(sprintf('docker compose exec app composer remove %s', escapeshellarg($name)));
}
