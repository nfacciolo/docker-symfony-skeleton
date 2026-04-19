<?php

namespace docker;

use Castor\Attribute\AsTask;
use function Castor\io;

#[AsTask(name: 'init', namespace: 'docker', description: 'Copy Docker skeleton files to the project root')]
function init(): void
{
    $templatesDir = __DIR__ . '/templates';
    $projectDir   = getcwd();

    io()->title('Initialisation des fichiers Docker...');

    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($templatesDir, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relativePath = $iterator->getSubPathname();
        $target       = $projectDir . '/' . $relativePath;

        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
            continue;
        }

        $destination = preg_replace('/\.dist$/', '', $target);
        $displayPath = preg_replace('/\.dist$/', '', $relativePath);

        if (file_exists($destination)) {
            if (!io()->confirm(sprintf('"%s" existe déjà. Écraser ?', $displayPath), false)) {
                io()->writeln(sprintf('  <comment>↷</comment> %s ignoré', $displayPath));
                continue;
            }
        }
        copy($item->getPathname(), $destination);
        io()->writeln(sprintf('  <info>✓</info> %s', $displayPath));
    }

    io()->success('Fichiers Docker initialisés à la racine du projet.');
}

#[AsTask(name: 'remove', namespace: 'docker', description: 'Remove Docker skeleton files from the project root')]
function remove(): void
{
    $templatesDir = __DIR__ . '/templates';
    $projectDir   = getcwd();

    io()->title('Suppression des fichiers Docker...');

    if (!io()->confirm('Cette action supprimera les fichiers Docker du projet. Continuer ?', false)) {
        io()->info('Annulé.');
        return;
    }

    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($templatesDir, \RecursiveDirectoryIterator::SKIP_DOTS),
        \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        $relativePath = preg_replace('/\.dist$/', '', $iterator->getSubPathname());
        $target       = $projectDir . '/' . $relativePath;

        if ($item->isDir()) {
            if (is_dir($target) && !(new \FilesystemIterator($target))->valid()) {
                rmdir($target);
                io()->writeln(sprintf('  <info>✓</info> %s/', $relativePath));
            }
            continue;
        }

        if (file_exists($target)) {
            unlink($target);
            io()->writeln(sprintf('  <info>✓</info> %s', $relativePath));
        }
    }

    io()->success('Fichiers Docker supprimés.');
}
