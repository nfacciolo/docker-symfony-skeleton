<?php

use function Castor\import;

$castorPath = file_exists(__DIR__ . '/vendor/reactic/docker-symfony-skeleton/.castor')
    ? __DIR__ . '/vendor/reactic/docker-symfony-skeleton/.castor'
    : __DIR__ . '/.castor';
import($castorPath);
