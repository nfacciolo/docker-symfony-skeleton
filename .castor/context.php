<?php

namespace context;

use Castor\Attribute\AsContext;
use Castor\Attribute\AsTask;
use Castor\Context;

use function Castor\context;
use function configuration\ensureConfiguration;
use function configuration\loadConfiguration;


#[AsContext(default: true)]
function getContext(): Context
{
    $skipTasks = ['reinit'];
    $config = array_intersect($skipTasks, $_SERVER['argv'] ?? [])
        ? loadConfiguration()
        : ensureConfiguration();

    return new Context(environment: [
        'PROJECT_NAME' => $config['PROJECT_NAME'] ?? '',
        'APP_PORT'     => $config['APP_PORT'] ?? '800',
    ]);
}

#[AsTask(description: 'Display default context', aliases: ['dc'])]
function displayDefaultContext(): void
{
    $context = context();
    dump($context);
}

