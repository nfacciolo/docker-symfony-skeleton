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

        if (file_exists($target)) {
            if (!io()->confirm(sprintf('"%s" existe déjà. Écraser ?', $relativePath), false)) {
                io()->writeln(sprintf('  <comment>↷</comment> %s ignoré', $relativePath));
                continue;
            }
        }

        copy($item->getPathname(), $target);
        io()->writeln(sprintf('  <info>✓</info> %s', $relativePath));
    }

    io()->success('Fichiers Docker initialisés à la racine du projet.');
}
