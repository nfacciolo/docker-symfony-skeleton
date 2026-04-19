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

#[AsTask(description: 'List recommended packages to install')]
function recommended(): void
{
    io()->title('Packages recommandés');

    io()->section('Dev (install:dev)');
    io()->listing([
        '<info>symfony/maker-bundle</info> — génération d\'entités, contrôleurs, formulaires...',
        '<info>symfony/profiler-pack</info> — barre de debug + profiler (inclut var-dumper, monolog)',
    ]);

    io()->section('Base de données (install:database)');
    io()->listing([
        '<info>symfony/orm-pack</info> — Doctrine ORM, migrations',
    ]);

    io()->section('Stack web (install:web)');
    io()->listing([
        '<info>symfony/twig-pack</info> — templating (inutile si API pure)',
        '<info>symfony/security-bundle</info> — authentification/autorisation',
        '<info>symfony/form</info> — formulaires',
        '<info>symfony/validator</info> — validation',
    ]);

    io()->section('Symfony UX — Composants UI (install:package <nom>)');
    io()->listing([
        '<info>symfony/ux-twig-component</info> — composants PHP/Twig réutilisables',
        '<info>symfony/ux-live-component</info> — composants réactifs sans JS (dépend de twig-component)',
        '<info>symfony/ux-icons</info> — icônes SVG dans Twig',
    ]);

    io()->section('Symfony UX — Interactivité (install:package <nom>)');
    io()->listing([
        '<info>symfony/ux-turbo</info> — navigation SPA-like sans framework JS',
        '<info>symfony/stimulus-bundle</info> — contrôleurs Stimulus (base de tout UX)',
    ]);

    io()->section('Symfony UX — Formulaires (install:package <nom>)');
    io()->listing([
        '<info>symfony/ux-autocomplete</info> — select avec recherche Ajax',
        '<info>symfony/ux-cropperjs</info> — recadrage d\'images',
        '<info>symfony/ux-dropzone</info> — zone de dépôt pour uploads',
    ]);

    io()->section('Symfony UX — Data / Carte (install:package <nom>)');
    io()->listing([
        '<info>symfony/ux-chartjs</info> — graphiques Chart.js',
        '<info>symfony/ux-map</info> — cartes Leaflet / Google Maps',
    ]);

    io()->section('Symfony UX — Intégrations JS (install:package <nom>)');
    io()->listing([
        '<info>symfony/ux-react</info> — composants React',
        '<info>symfony/ux-vue</info> — composants Vue.js',
    ]);

    io()->section('Symfony UX — Divers (install:package <nom>)');
    io()->listing([
        '<info>symfony/ux-notify</info> — notifications navigateur via Mercure',
        '<info>symfony/ux-translator</info> — traductions Symfony en JS',
        '<info>symfony/ux-toolkit</info> — composants et templates réutilisables',
    ]);

    io()->section('Au cas par cas (install:package <nom>)');
    io()->listing([
        '<info>doctrine/doctrine-fixtures-bundle</info> — données de test',
        '<info>symfony/serializer-pack</info> — sérialisation JSON (essentiel pour API)',
        '<info>symfony/translation</info> — i18n',
        '<info>symfony/http-client</info> — appels HTTP sortants',
        '<info>symfony/mailer</info> — envoi d\'emails',
        '<info>symfony/asset-mapper</info> — assets frontend (moderne)',
        '<info>symfony/webpack-encore-bundle</info> — assets frontend (webpack)',
    ]);
}

#[AsTask(description: 'Install Webpack Encore and run npm install')]
function encore(): void
{
    io()->title('Installation de Webpack Encore...');
    run('docker compose exec app composer require symfony/webpack-encore-bundle');
    io()->writeln('Démarrage du service node...');
    run('docker compose --profile node up -d node');
    io()->writeln('Installation des dépendances npm...');
    run('docker compose exec node npm install');
    io()->success('Webpack Encore installé.');
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
