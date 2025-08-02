# Simple File Manager

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.1-777BB4.svg)
![Laravel](https://img.shields.io/badge/Laravel-9.0-FF2D20.svg)
![Vue.js](https://img.shields.io/badge/Vue.js-3.0-4FC08D.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

Un gestionnaire de fichiers sécurisé permettant aux utilisateurs authentifiés de gérer leurs propres fichiers via une interface moderne et intuitive.

## 📋 Fonctionnalités

- **Authentification sécurisée** : Inscription, connexion et gestion de profil utilisateur
- **Isolation des fichiers** : Chaque utilisateur ne peut accéder qu'à ses propres fichiers
- **Upload de fichiers** : Support pour PDF, DOCX, PNG, JPG, ODT avec limite de taille
- **Gestion complète** : Liste, recherche, tri, filtrage, téléchargement et suppression de fichiers

## 🚀 Installation

### Prérequis
- [Docker](https://www.docker.com/products/docker-desktop/) (avec Docker Compose)

### Étapes d'installation

1. **Clonez le dépôt**
   ```bash
   git clone https://github.com/KheroubiAymen/filemanager.git
   cd filemanager
   ```

2. **Configurez le fichier d'environnement**
   ```bash
   # Copiez le fichier d'exemple
   cp .env.example .env
   
   # Modifiez les paramètres si nécessaire, notamment:
   # - DB_HOST=db
   # - DB_DATABASE=filemanager
   # - DB_USERNAME=filemanager
   # - DB_PASSWORD=filemanager
   ```

3. **Lancez les conteneurs Docker**
   ```bash
   docker-compose up -d
   ```

4. **Suivez les logs pour attendre la fin de la configuration**
   ```bash
   docker-compose logs -f
   ```
   Le conteneur exécute de nombreuses tâches d'initialisation:
   - Installation des dépendances PHP (Composer)
   - Installation des dépendances JavaScript (npm)
   - Compilation des assets (npm run build)
   - Création du lien symbolique pour le stockage
   - Génération de la clé d'application
   - Exécution des migrations de base de données
   - Mise en cache de la configuration et des routes
   
   Attendez de voir "Apache/2.4.x (Debian) PHP/8.1.x configured -- resuming normal operations" dans les logs, ce qui indique que l'application est prête.

5. **Accédez à l'application**
   ```
   http://localhost:8080
   ```
   Si vous avez configuré un port différent dans docker-compose.yml, utilisez-le à la place de 8080.

### Dépannage

- **Si le port 8080 est déjà utilisé**, modifiez le port dans docker-compose.yml:
  ```yaml
  ports:
    - "8081:80"  # Changez 8080 pour un autre port disponible
  ```

- **Si l'initialisation échoue**, vous pouvez exécuter les commandes manuellement:
  ```bash
  # Vérifier les logs pour identifier l'erreur
  docker-compose logs -f
  
  # Exécuter les commandes manuellement si nécessaire
  docker-compose exec app composer install
  docker-compose exec app npm install
  docker-compose exec app npm run build
  docker-compose exec app php artisan storage:link
  docker-compose exec app php artisan migrate --force
  docker-compose exec app chmod -R 777 storage bootstrap/cache
  ```
  
- **Pour redémarrer complètement l'application**:
  ```bash
  docker-compose down
  docker-compose up -d
  ```

## 🏗 Choix d'architecture

### Architecture Backend (Laravel)

- **Laravel 9 avec PHP 8.1** : Framework moderne, sécurisé et performant
- **Architecture MVC** : Séparation claire des responsabilités
- **Middleware d'authentification** : Protection des routes et isolation des données par utilisateur
- **Eloquent ORM** : Interactions sécurisées avec la base de données
- **Storage API** : Gestion optimisée des fichiers avec isolation par utilisateur

### Architecture Frontend

- **Vue.js 3** : Framework réactif pour une interface utilisateur fluide
- **Inertia.js** : Communication transparente entre Laravel et Vue sans API séparée
- **Tailwind CSS** : Design responsive et moderne
- **Compilation Vite** : Performances optimales en production

### Conteneurisation

- **Docker multi-conteneurs** : Séparation des services (application, base de données)
- **Configuration automatisée** : Installation et configuration simplifiées
- **Environnement isolé** : Garantit un fonctionnement identique sur toutes les machines
- **Volumes persistants** : Conservation des données entre les redémarrages

### Sécurité

- **Authentification robuste** : Protection contre les attaques par force brute
- **Validation stricte** : Vérification des types et tailles de fichiers
- **Isolation des données** : Chaque utilisateur n'accède qu'à ses propres fichiers
- **Protection CSRF** : Sécurité des formulaires et requêtes

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.