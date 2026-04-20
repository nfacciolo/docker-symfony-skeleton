# Tester l'installation du package

Ce guide explique comment simuler l'installation de `reactic/docker-symfony-skeleton` dans un projet via `composer require`.

## Prérequis

- PHP >= 8.2
- Composer
- Docker
- Castor (`composer global require jolicode/castor`)

## Setup du projet de test

Le dossier `docker-symfony-skeleton-test/` (à la racine du repo) sert de projet cible pour simuler l'installation.

Créer le `composer.json` du projet test :

```json
{
    "name": "test/symfony-project",
    "type": "project",
    "require": {},
    "repositories": [
        {
            "type": "path",
            "url": "./docker-symfony-skeleton"
        }
    ],
    "minimum-stability": "dev",
    "prefer-stable": true,
    "config": {
        "allow-plugins": {
            "reactic/docker-symfony-skeleton": true
        }
    }
}
```

## Installation

Depuis `docker-symfony-skeleton-test/` :

```bash
composer require reactic/docker-symfony-skeleton:*@dev
```

### Ce qui doit se passer

1. Composer installe le package dans `vendor/reactic/docker-symfony-skeleton/` (symlink vers le dossier local)
2. Le plugin Composer (`Plugin.php`) se déclenche en post-install
3. `castor.php` est copié à la racine du projet

### Résultat attendu

```
docker-symfony-skeleton-test/
├── castor.php          ← créé par le plugin
├── composer.json
├── composer.lock
└── vendor/
```

## Lancer l'environnement Docker

```bash
castor up
```

### Scénario sans Symfony installé

1. Castor détecte l'absence de `src/Kernel.php`
2. Propose : `Aucune application Symfony détectée. Créer un nouveau projet Symfony ?`
3. Si confirmé :
   - Build des images Docker
   - Démarrage des containers en mode détaché (`-d`)
   - Attente que le container `app` soit prêt
   - `composer create-project symfony/skeleton` exécuté via `docker compose exec`
   - `bin/console cache:clear` lancé après l'installation

### Scénario avec Symfony déjà installé

1. Castor ne propose pas l'installation
2. Build + démarrage des containers
3. L'entrypoint Docker exécute `bin/console cache:clear` au démarrage

## Initialiser les fichiers Docker

Si les fichiers Docker ne sont pas présents :

```bash
castor docker:init
```

Copie à la racine : `Dockerfile`, `compose.yml`, `.dockerignore`, `docker/` et les configs nginx/php/node.

## Repartir de zéro

Pour supprimer tout et recommencer :

```bash
# Vider le projet test (garder uniquement le dossier du package)
rm -rf vendor composer.lock castor.php docker/ Dockerfile compose.yml .env.local

# Réinstaller
composer require reactic/docker-symfony-skeleton:*@dev
```
