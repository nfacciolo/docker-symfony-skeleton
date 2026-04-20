<?php

namespace Reactic\DockerSymfonySkeleton\Composer;

use Composer\Script\Event;

class ScriptHandler
{
    public static function install(Event $event): void
    {
        $projectDir = getcwd();
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $castorDist = $vendorDir . '/reactic/docker-symfony-skeleton/.castor/templates/castor.php.dist';
        $castorTarget = $projectDir . '/castor.php';

        if (file_exists($castorTarget)) {
            $event->getIO()->write('<info>castor.php already exists, skipping.</info>');
            return;
        }

        if (!file_exists($castorDist)) {
            $event->getIO()->writeError('<error>castor.php.dist not found in package.</error>');
            return;
        }

        copy($castorDist, $castorTarget);
        $event->getIO()->write('<info>castor.php created at project root.</info>');
        $event->getIO()->write('<comment>Run `castor up` to start your Docker environment.</comment>');
    }
}