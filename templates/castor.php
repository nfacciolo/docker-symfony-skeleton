<?php
use Castor\Attribute\AsTask;

use function Castor\import;
use function Castor\run;
use function Castor\io;

import(__DIR__ . '/vendor/reactic/docker-symfony-skeleton/.castor');


function hasDatabase(): bool
{
    return file_exists(__DIR__ . '/config/packages/doctrine.yaml');
}

function hasEncore(): bool
{
    return file_exists(__DIR__ . '/webpack.config.js');
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
    run('mkdir -p public/media');

    io()->writeln('Building images...');
    run(sprintf('docker compose %s build', composeArgs()));

    io()->writeln('Starting containers...');
    run(sprintf('docker compose %s up', composeArgs()));
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
