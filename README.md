# QrNova

Application professionnelle de génération de QR Codes construite avec Laravel 12, Livewire 3, Tailwind CSS 4, Alpine.js (fourni par Livewire), Endroid QR Code et Dompdf.

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Configurez MySQL dans `.env` avant `php artisan migrate`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qrnova
DB_USERNAME=root
DB_PASSWORD=
```

## Authentification

Les pages de génération et de gestion des QR Codes nécessitent une connexion.
Le seed crée le compte local suivant :

```text
E-mail : admin@qrnova.test
Mot de passe : password
```

Ces identifiants peuvent être modifiés avant le seed dans `.env` :

```dotenv
AUTH_SEED_NAME="Administrateur QrNova"
AUTH_SEED_EMAIL=admin@qrnova.test
AUTH_SEED_PASSWORD=password
```

Pour créer ou mettre à jour le compte seedé :

```bash
php artisan db:seed
```

## QR Code progressif

Le type **Profil progressif** génère une URL publique stable. La fiche peut ensuite
être enrichie avec une adresse, une photo, un e-mail ou un site web sans réimprimer
le QR Code.

En production, `APP_URL` doit contenir le domaine public exact avant la génération
du QR Code, par exemple :

```dotenv
APP_URL=https://qrnova.example.com
```

## Commandes de création utilisées

```bash
composer require livewire/livewire:^3.6 endroid/qr-code dompdf/dompdf
php artisan make:model QrCode -m
php artisan make:livewire QrCode/QrCodeGenerator
php artisan make:livewire QrCode/QrCodeIndex
php artisan make:livewire QrCode/QrCodeShow
php artisan make:controller QrCodeDownloadController --invokable
php artisan storage:link
```

## Tests

```bash
php artisan test
npm run build
```

L’environnement local créé par Laravel utilise SQLite par défaut. La production peut utiliser MySQL via les variables ci-dessus.
