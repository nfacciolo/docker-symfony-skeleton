<?php

use Castor\Attribute\AsTask;

use function Castor\run;
use function Castor\io;

function hasSymfony(): bool
{
    return file_exists(getcwd() . '/src/Kernel.php');
}

function hasDatabase(): bool
{
    return file_exists(getcwd() . '/config/packages/doctrine.yaml');
}

function hasEncore(): bool
{
    return file_exists(getcwd() . '/webpack.config.js');
}

function composeArgs(): string
{
    $profiles = [];
    if (hasDatabase()) $profiles[] = '--profile db';
    if (hasEncore())   $profiles[] = '--profile node';
    return implode(' ', $profiles);
}

function waitForContainer(): void
{
    for ($i = 1; $i <= 10; $i++) {
        try {
            run('docker compose exec app php -r "echo 1;" > /dev/null 2>&1');
            return;
        } catch (\Throwable $e) {
            io()->writeln(sprintf('Tentative %d/10 : %s', $i, $e->getMessage()));
            sleep(1);
        }
    }
    throw new \RuntimeException('Le container app n\'a pas démarré après 10 tentatives. Vérifiez les logs avec : docker compose logs app');
}

#[AsTask]
function up(): void
{
    $installSymfony = !hasSymfony()
        && io()->confirm('Aucune application Symfony détectée. Créer un nouveau projet Symfony ?', true);

    run('mkdir -p public/media');

    io()->writeln('Building images...');
    run(sprintf('docker compose %s build', composeArgs()));

    io()->writeln('Starting containers...');
    run(sprintf('docker compose %s up -d', composeArgs()));

    if ($installSymfony) {
        waitForContainer();
        io()->writeln('Installing Symfony...');
        run('docker compose exec app composer create-project symfony/skeleton:"8.0.x" ./tmp --prefer-dist --no-progress --no-interaction');
        run('docker compose exec app sh -c "cd tmp && rm -rf var && cp -R . .. && cd /var/www/html && rm -rf tmp"');
        run('docker compose exec app bin/console cache:clear');
        io()->success('Symfony installed successfully.');
    }
}

#[AsTask]
function build(): void
{
    run(sprintf('docker compose %s build', composeArgs()));
}

#[AsTask]
function down(): void
{
    run(sprintf('docker compose %s down', composeArgs()));
}
