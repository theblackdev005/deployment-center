# Mise en production sécurisée

## Préparation

1. Le domaine doit disposer d’un certificat HTTPS valide.
2. Le serveur web doit servir uniquement le dossier `public/`.
3. PHP 8.3 minimum et les extensions Laravel requises doivent être disponibles.
4. Créer une base MySQL et un utilisateur dédié à cette seule application.

## Installation

1. Copier `.env.production.example` vers `.env`.
2. Installer les dépendances :

```bash
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build
```

3. Ouvrir `https://votre-domaine.com/installation`.
4. Renseigner le nom du projet, l’email, le compte administrateur, la base de données, le logo et le favicon.
5. Terminer l’installation, puis exécuter `php artisan deployment-center:security-check` si un terminal est disponible.

À la fin, l’assistant crée un verrou dans `storage/app/installed.lock` et ne peut plus être rouvert. La commande `php artisan deployment-center:install` reste disponible comme solution de secours en terminal.

## Automatisation obligatoire

Ajouter cette tâche cron toutes les minutes afin d’exécuter la synchronisation Hostinger quotidienne :

```cron
* * * * * cd /chemin/du/projet && php artisan schedule:run > /dev/null 2>&1
```

## Après la première connexion

1. Ouvrir **Mon compte**.
2. Activer la double authentification.
3. Enregistrer les huit codes de secours hors du serveur.
4. Tester l’envoi SMTP et la synchronisation de chaque compte Hostinger.
5. Sauvegarder séparément la base de données et le fichier `.env`, notamment `APP_KEY`.

Ne jamais conserver une copie publique de `.env`, des clés SSH, de la base SQLite locale ou des jetons API.
