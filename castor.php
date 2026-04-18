<?php
use Castor\Attribute\AsTask;

use function Castor\import;
use function Castor\run;
use function Castor\io;

import(__DIR__ . '/.castor');


#[AsTask]
function up(): void
{
    run('mkdir -p public/media');

    io()->writeln('Building images...');
    run('docker compose build');

    io()->writeln('Starting containers...');
    run('docker compose up');
}

#[AsTask]
function build(): void
{
    run('docker compose build');
}

#[AsTask]
function down(): void
{
    run('docker compose down');
}
