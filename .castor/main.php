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

#[AsTask]
function up(): void
{
    $envPrefix = '';
    if (!hasSymfony()) {
        if (io()->confirm('Aucune application Symfony détectée. Créer un nouveau projet Symfony ?', true)) {
            $envPrefix = 'SYMFONY_INIT=1 ';
        }
    }

    run('mkdir -p public/media');

    io()->writeln('Building images...');
    run(sprintf('%sdocker compose %s build', $envPrefix, composeArgs()));

    io()->writeln('Starting containers...');
    run(sprintf('%sdocker compose %s up', $envPrefix, composeArgs()));
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
