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
    io()->success('Packages base de données installés.');
}

#[AsTask(description: 'Install a composer package')]
function package(string $name): void
{
    run(sprintf('docker compose exec app composer require %s', escapeshellarg($name)));
}
