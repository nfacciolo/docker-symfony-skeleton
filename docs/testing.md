# Tester l'installation du package

Ce guide explique comment simuler l'installation de `reactic/docker-symfony-skeleton` dans un projet via `composer require`.

## Prérequis

- PHP >= 8.2
- Composer
- Docker
- Castor (`composer global require jolicode/castor`)

## Setup du projet de test

Le dossier `test/` (à la racine du package) sert de projet cible pour simuler l'installation.

Créer le `composer.json` dans `test/` :

```json
{
    "name": "test/symfony-project",
    "type": "project",
    "require": {},
    "repositories": [
        {
            "type": "path",
            "url": "../"
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

Depuis `test/` :

```bash
composer require reactic/docker-symfony-skeleton:*@dev
```

### Ce qui doit se passer

1. Composer installe le package dans `vendor/reactic/docker-symfony-skeleton/` (symlink vers `../`)
2. Le plugin Composer (`Plugin.php`) se déclenche en post-install
3. `castor.php` est copié à la racine
4. Si nouvelle installation : proposition d'initialiser les fichiers Docker et d'installer Symfony

### Résultat attendu

```
test/
├── castor.php          ← créé par le plugin
├── composer.json
├── composer.lock
└── vendor/
```

Puis si Docker init accepté :

```
test/
├── Dockerfile
├── compose.yml
├── docker/
├── castor.php
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

## Repartir de zéro

```bash
# Depuis test/, vider le projet et recommencer
rm -rf vendor composer.lock castor.php docker/ Dockerfile compose.yml .env.local

composer require reactic/docker-symfony-skeleton:*@dev
```
