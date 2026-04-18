# Docker Symfony Skeleton

Template de démarrage pour projets Symfony entièrement conteneurisé. Aucune dépendance PHP requise sur la machine hôte — Docker suffit.

## Principe

Au premier démarrage, Symfony est automatiquement installé dans le container via `composer create-project`. Le projet est ensuite disponible et monté en volume pour le développement.

La configuration du projet (nom, etc.) est gérée par [Castor](https://castor.jolicode.com/) et stockée dans `.env`.

## Prérequis

- [Docker](https://docs.docker.com/get-docker/)
- [Castor](https://castor.jolicode.com/installation/)

## Démarrage

```bash
castor up
```

Au premier lancement, le nom du projet est demandé, puis les images sont buildées et Symfony est installé automatiquement.

L'application est accessible sur **http://localhost:800**.

## Tâches Castor

| Commande | Description |
|---|---|
| `castor up` | Build les images et démarre les containers |
| `castor build` | Build les images sans démarrer |
| `castor down` | Arrête les containers |
| `castor config` | Reconfigure le projet |
| `castor reinit` | Réinitialise le projet (supprime les fichiers Symfony) |

## Commandes quotidiennes (Makefile)

| Commande | Description |
|---|---|
| `make sh` | Ouvre un shell dans le container app |
| `make cc` | Vide le cache Symfony |
| `make mm` | Génère une migration |
| `make m` | Exécute les migrations |
| `make t` | Lance les tests PHPUnit |
| `make dr` | Debug des routes |
| `make nw` | Lance le watcher Node |

## Structure

```
.
├── .castor/                  # Tâches et configuration Castor
│   ├── configuration/        # Gestion du .env
│   └── critical/             # Tâches critiques (reinit, config)
├── docker/
│   ├── nginx/                # Configuration Nginx
│   └── php/                  # Configuration PHP, entrypoint
├── castor.php                # Point d'entrée Castor
├── compose.yml               # Services Docker
└── Dockerfile                # Images app_php_dev et app_php_prod
```

## Variables d'environnement

Les variables de configuration sont stockées dans `.env` à la racine. Les valeurs sensibles peuvent être surchargées dans `.env.local` (non commité).

| Variable | Description | Default |
|---|---|---|
| `PROJECT_NAME` | Nom du projet (préfixe des containers) | demandé au démarrage |
| `DATABASE_USERNAME` | Utilisateur PostgreSQL | `app` |
| `DATABASE_PASSWORD` | Mot de passe PostgreSQL | `secret` |
| `DATABASE_NAME` | Nom de la base de données | `app` |
