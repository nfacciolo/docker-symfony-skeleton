<?php

namespace critical;

use Castor\Attribute\AsTask;
use function Castor\io;
use function Castor\run;
use function configuration\clearConfiguration;
use function configuration\ensureConfiguration;
use function configuration\hasConfiguration;

#[AsTask(name:'reinit', description: 'Reinitialize the Symfony template by removing project files')]
function reinit(): void
{
    io()->title('Reinitializing Symfony template...');

    run('docker compose down -v');

    $itemsToRemove = [
        'bin',
        'config',
        'migrations',
        'public',
        'tmp',
        'src',
        'templates',
        'var',
        'vendor',
        '.env*',
        'composer.*',
        'node_modules',
        'package-lock.json',
        'compose.override.yaml',
        'symfony.lock',
    ];

    foreach ($itemsToRemove as $item) {
        io()->writeln(sprintf('Removing: %s', $item));
        run(sprintf('rm -rf %s', $item));
    }

    run('cp .castor/critical/resources/.gitignore.original .gitignore');

    io()->success('Template reinitialized successfully!');
}

#[AsTask(description: 'Configure or reconfigure the project settings')]
function config(): void
{
    if (hasConfiguration()) {
        if (!io()->confirm('La configuration existe déjà. Voulez-vous la reconfigurer ?', false)) {
            io()->info('Configuration annulée.');
            return;
        }

        clearConfiguration();
        io()->writeln('Configuration existante supprimée.');
        io()->newLine();
    }

    ensureConfiguration();
}
