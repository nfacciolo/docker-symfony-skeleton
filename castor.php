<?php
use Castor\Attribute\AsTask;

use function Castor\import;
use function Castor\run;

import(__DIR__ . '/.castor');


#[AsTask]
function up(): void
{
    run('docker compose up');
}
