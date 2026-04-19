# Docker Symfony Skeleton

Template de démarrage pour projets Symfony, entièrement conteneurisé. L'objectif est de **normaliser les méthodes et processus** de création et de gestion des projets Symfony au quotidien.

Aucune dépendance PHP ou Composer requise sur la machine hôte — Docker et Castor suffisent.

## Prérequis

- [Docker](https://docs.docker.com/get-docker/)
- [Castor](https://castor.jolicode.com/installation/)

## Premier démarrage

```bash
castor up
```

Au premier lancement :
1. Le nom du projet est demandé et sauvegardé dans `.env`
2. Les images Docker sont buildées
3. Symfony est installé automatiquement dans le container via `composer create-project`

L'application est accessible sur **http://localhost:800**.

---

## Toutes les commandes disponibles

### Stack Docker

| Commande | Description |
|---|---|
| `castor up` | Build les images et démarre les containers |
| `castor build` | Build les images sans démarrer |
| `castor down` | Arrête les containers |

> Le service `database` est démarré automatiquement si Doctrine est installé (`config/packages/doctrine.yaml` détecté).

---

### Configuration

| Commande | Description |
|---|---|
| `castor config` | Configure ou reconfigure le nom du projet |
| `castor reinit` | Réinitialise complètement le projet (supprime tous les fichiers Symfony, volumes Docker inclus) |

---

### Installation de packages

| Commande | Description |
|---|---|
| `castor install:recommended` | Affiche la liste des packages recommandés avec leurs descriptions |
| `castor install:dev` | Installe les outils de dev : `maker-bundle` + `profiler-pack` |
| `castor install:web` | Installe le stack web : `twig-pack` + `security-bundle` + `form` + `validator` |
| `castor install:database` | Installe Doctrine ORM, démarre le service database et teste la connexion |
| `castor install:package <nom>` | Installe n'importe quel package Composer |

### Désinstallation de packages

| Commande | Description |
|---|---|
| `castor remove:database` | Désinstalle Doctrine ORM |
| `castor remove:package <nom>` | Désinstalle n'importe quel package Composer |

---

### Packages recommandés

Lancer `castor install:recommended` pour afficher la liste complète. Aperçu :

**Dev**
- `symfony/maker-bundle` — génération d'entités, contrôleurs, formulaires
- `symfony/profiler-pack` — barre de debug + profiler

**Stack web**
- `symfony/twig-pack` — templating
- `symfony/security-bundle` — authentification/autorisation
- `symfony/form` + `symfony/validator` — formulaires et validation

**Symfony UX**
- `symfony/ux-twig-component` — composants PHP/Twig réutilisables
- `symfony/ux-live-component` — composants réactifs sans JS
- `symfony/ux-turbo` — navigation SPA-like sans framework JS
- `symfony/ux-icons` — icônes SVG dans Twig
- `symfony/ux-chartjs` — graphiques Chart.js
- `symfony/ux-map` — cartes Leaflet / Google Maps
- _(et plus via `castor install:recommended`)_

---

## Structure du projet

```
.
├── .castor/
│   └── composer.json         # Dépendances Castor (optionnel, nécessite PHP+Composer sur le host)
├── castor-symfony/           # Package de tâches Castor (reactic/castor-symfony)
│   ├── configuration/        # Gestion de la configuration .env
│   ├── critical/             # Tâches critiques (reinit, config)
│   └── install.php           # Tâches d'installation de packages
├── docker/
│   ├── nginx/                # Configuration Nginx
│   ├── node/                 # Configuration Node
│   └── php/                  # Configuration PHP-FPM, entrypoint, xdebug
├── castor.php                # Point d'entrée Castor
├── compose.yml               # Services Docker
└── Dockerfile                # Images app_php_dev et app_php_prod
```

---

## Services Docker

| Service | Description | Port |
|---|---|---|
| `proxy` | Nginx (reverse proxy) | `800` |
| `app` | PHP-FPM (Symfony) | — |
| `database` | PostgreSQL 17 _(profil `db`)_ | `5432` |
| `node` | Node LTS | — |

---

## Variables d'environnement

Stockées dans `.env`. Les valeurs sensibles peuvent être surchargées dans `.env.local` (non commité).

| Variable | Description | Défaut |
|---|---|---|
| `PROJECT_NAME` | Préfixe des containers Docker | demandé au démarrage |
| `DATABASE_USERNAME` | Utilisateur PostgreSQL | `app` |
| `DATABASE_PASSWORD` | Mot de passe PostgreSQL | `secret` |
| `DATABASE_NAME` | Nom de la base de données | `app` |

---

## Workflow Castor avec Composer (optionnel)

Si PHP et Composer sont installés sur le host, les tâches Castor peuvent être gérées comme un vrai package Composer :

```bash
composer install --working-dir=.castor
```

Cela installe `reactic/castor-symfony` depuis le dossier local `castor-symfony/` via un path repository. Les mises à jour du package seront ensuite gérées via `composer update --working-dir=.castor`.
