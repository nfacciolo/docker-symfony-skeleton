<?php
use Castor\Attribute\AsTask;

use function Castor\import;
use function Castor\run;
use function Castor\io;

import(__DIR__ . '/.castor');


#[AsTask]
function up(): void
{
    io()->writeln('Building images...');
    run('docker compose --env-file .env.castor build');

    io()->writeln('Starting containers...');
    run('docker compose --env-file .env.castor up');
}

#[AsTask]
function build(): void
{
    run('docker compose --env-file .env.castor build');
}

#[AsTask]
function down(): void
{
    run('docker compose --env-file .env.castor down');
}
