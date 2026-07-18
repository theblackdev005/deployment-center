# Deploy Center

Plateforme privee de gestion des deploiements des projets vers les domaines Hostinger.

## Etat actuel

- authentification administrateur sans inscription publique ;
- gestion des projets GitHub ;
- gestion des serveurs SSH ;
- gestion des domaines et dossiers cibles ;
- preparation et historique des deploiements ;
- interface responsive en francais.

Le moteur d'execution SSH sera ajoute dans l'etape suivante. Les mots de passe, jetons et cles privees ne doivent jamais etre commits dans Git.

## Installation locale

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
php artisan serve
```

Renseigner `ADMIN_NAME`, `ADMIN_EMAIL` et `ADMIN_PASSWORD` dans `.env` avant d'executer le seeder.

## Compatibilite

Le projet cible PHP 8.3 afin de rester compatible avec les hebergements Hostinger actuels.
