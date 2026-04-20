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
        $templatesDir = $vendorDir . '/reactic/docker-symfony-skeleton/.castor/templates';

        $this->copyCastorPhp($projectDir, $templatesDir);

        if ($this->isNewInstall($projectDir)) {
            $this->io->write('');
            $this->io->write('<info>Nouvelle installation détectée.</info>');

            if ($this->io->askConfirmation('  Initialiser les fichiers Docker ? [Y/n] ', true)) {
                $this->copyDockerFiles($projectDir, $templatesDir);
            }

            if (!$this->hasSymfony($projectDir)) {
                if ($this->io->askConfirmation('  Installer Symfony 8 ? [Y/n] ', true)) {
                    $this->io->write('');
                    $this->io->write('<comment>Lancez `castor up` pour démarrer l\'environnement et installer Symfony 8.</comment>');
                }
            }
        }
    }

    private function copyCastorPhp(string $projectDir, string $templatesDir): void
    {
        $target = $projectDir . '/castor.php';

        if (file_exists($target)) {
            return;
        }

        $source = $templatesDir . '/castor.php.dist';
        if (!file_exists($source)) {
            $this->io->writeError('<error>castor.php.dist not found in package.</error>');
            return;
        }

        copy($source, $target);
        $this->io->write('<info>✓ castor.php créé à la racine du projet.</info>');
    }

    private function copyDockerFiles(string $projectDir, string $templatesDir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($templatesDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = $iterator->getSubPathname();

            if (str_ends_with($relativePath, 'castor.php.dist')) {
                continue;
            }

            $destination = preg_replace('/\.dist$/', '', $projectDir . '/' . $relativePath);
            $displayPath = preg_replace('/\.dist$/', '', $relativePath);

            if ($item->isDir()) {
                if (!is_dir($destination)) {
                    mkdir($destination, 0755, true);
                }
                continue;
            }

            if (file_exists($destination)) {
                continue;
            }

            copy($item->getPathname(), $destination);
            $this->io->write(sprintf('  <info>✓ %s</info>', $displayPath));
        }
    }

    private function isNewInstall(string $projectDir): bool
    {
        return !file_exists($projectDir . '/Dockerfile');
    }

    private function hasSymfony(string $projectDir): bool
    {
        return file_exists($projectDir . '/src/Kernel.php');
    }
}
