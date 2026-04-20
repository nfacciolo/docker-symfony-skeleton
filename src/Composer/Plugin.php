<?php

namespace Reactic\DockerSymfonySkeleton\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void {}

    public function uninstall(Composer $composer, IOInterface $io): void {}

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'onPostInstall',
            ScriptEvents::POST_UPDATE_CMD => 'onPostInstall',
        ];
    }

    public function onPostInstall(Event $event): void
    {
        $projectDir = getcwd();
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        $castorDist = $vendorDir . '/reactic/docker-symfony-skeleton/.castor/templates/castor.php.dist';
        $castorTarget = $projectDir . '/castor.php';

        if (file_exists($castorTarget)) {
            $this->io->write('<info>castor.php already exists, skipping.</info>');
            return;
        }

        if (!file_exists($castorDist)) {
            $this->io->writeError('<error>castor.php.dist not found in package.</error>');
            return;
        }

        copy($castorDist, $castorTarget);
        $this->io->write('<info>castor.php created at project root.</info>');
        $this->io->write('<comment>Run `castor up` to start your Docker environment.</comment>');
    }
}
