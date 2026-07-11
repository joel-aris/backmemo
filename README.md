# VALIDIKA Backend

API Laravel 12 separee du frontend React. Toutes les interactions avec PostgreSQL passent par les endpoints REST `/api/v1`.

## Stack

- PHP 8.4+
- Laravel 12
- PostgreSQL
- Laravel Sanctum
- spatie/laravel-permission
- TOTP compatible Google Authenticator, Microsoft Authenticator et Authy

## Installation

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

L'extraction OCR (`POST /api/v1/ocr/extract`) necessite le binaire `tesseract` sur le serveur :

```bash
sudo apt-get install -y tesseract-ocr tesseract-ocr-fra
```

Composer n'est pas disponible dans cet environnement Codex, donc l'installation des vendors et l'execution des tests doivent etre lancees sur la machine de developpement.

## Securite incluse

- Hash Argon2id via Laravel `hashed` et usage explicite de `Hash::make`.
- Auth API Sanctum avec expiration courte et rotation a la connexion.
- RBAC avec roles Super Admin, Administrateur, President, Secretaire, Pharmacien, Auditeur et Visiteur.
- Validation stricte par `FormRequest`.
- Rate limiting sur l'API et l'authentification.
- Headers HTTP securises.
- CORS restreint au frontend.
- Journal d'audit chaine par SHA-256.
- Documents avec empreinte SHA-256, signature HMAC et QR token.
- Profils pharmaciens publies uniquement avec photo obligatoire.

## Tests

```bash
cd backend
composer test
```
