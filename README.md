# Projet d'intégration

Ce projet est une application web de gestion des files d'attente pour une mairie, avec une interface publique pour les citoyens et une interface d'administration.

## Structure du projet

- `index/` : pages publiques, formulaires de réservation, authentification et assets frontend
- `admin/` : panneau d'administration et gestion des services/institutions
- `projet_integration.sql` : export de la base de données MySQL

## Prérequis

- WAMP / XAMPP / MAMP
- PHP 7.4+ ou plus récent
- MySQL
- Un navigateur web moderne

## Installation

1. Placez le dossier du projet dans le répertoire racine de votre serveur local, par exemple :
   - `C:\wamp64\www\projet d'integration\4new`

2. Importez le fichier SQL dans MySQL :
   - Ouvrez phpMyAdmin
   - Créez une base de données nommée `projet_integration`
   - Importez `projet_integration.sql`

3. Configurez la connexion à la base de données si nécessaire dans les fichiers PHP utilisés par l'application.

4. Démarrez WAMP et ouvrez l'application dans votre navigateur :
   - `http://localhost/projet%20d'integration/4new/index/`

## Fonctionnalités principales

- Inscription et connexion des utilisateurs
- Réservation de services
- Gestion des files d'attente
- Interface d'administration
- Gestion des institutions et services

## Notes

- Le projet utilise PHP avec PDO et MySQL.
- Les assets frontend sont stockés dans les dossiers `index/css/`, `index/js/` et `admin/css/`.

## Développement

Pour contribuer au projet, il est recommandé de garder le code organisé, de tester les formulaires d'authentification et de vérifier les accès à la base de données après chaque modification.
