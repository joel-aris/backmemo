# VALIDIKA API REST

Base URL: `/api/v1`

Headers JSON:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

## Authentification

| Method | Route | Auth | Description |
| --- | --- | --- | --- |
| POST | `/auth/register` | Public | Cree un compte, exige verification email et activation 2FA. |
| POST | `/auth/login` | Public | Authentifie, remplace l'ancien token API et retourne un token Sanctum. |
| POST | `/auth/logout` | Sanctum | Revoque le token courant. |
| POST | `/auth/forgot-password` | Public | Demande une reinitialisation. |
| POST | `/auth/reset-password` | Public | Flux prepare pour reset password. |
| POST | `/auth/verify-email` | Sanctum | Valide l'email. |
| POST | `/auth/resend-verification` | Sanctum | Renvoie l'email de verification. |
| POST | `/auth/2fa/enable` | Sanctum | Genere secret TOTP, QR URL et codes de recuperation. |
| POST | `/auth/2fa/verify` | Sanctum | Confirme le code TOTP. |
| POST | `/auth/2fa/disable` | Sanctum | Desactive la 2FA. |
| GET | `/auth/me` | Sanctum | Retourne l'utilisateur, ses roles et permissions. |

## Pharmaciens

Routes protegees par `auth:sanctum`, `verified`, permissions et policies.

| Method | Route | Description |
| --- | --- | --- |
| GET | `/pharmacists` | Recherche paginee par texte, province et commune. |
| POST | `/pharmacists` | Cree un pharmacien. Photo obligatoire. |
| GET | `/pharmacists/{id}` | Affiche la fiche et les documents associes. |
| PUT | `/pharmacists/{id}` | Met a jour la fiche. |
| DELETE | `/pharmacists/{id}` | Archive via soft delete. |

Creation minimale:

```json
{
  "photo": "multipart-file",
  "first_name": "Jean",
  "middle_name": "Mukendi",
  "last_name": "Kabamba",
  "ordinal_number": "ONP-RDC-24-01892",
  "sex": "male",
  "province": "Kinshasa",
  "city": "Kinshasa",
  "commune": "Gombe",
  "professional_address": "Avenue de la Justice 12",
  "professional_phone": "+243858575940",
  "professional_email": "jean@validika.cd",
  "professional_status": "active",
  "registered_at": "2019-03-12",
  "practice_started_at": "2019-03-18",
  "license_number": "CNOP-RDC-2026-001892",
  "license_status": "active",
  "license_expires_at": "2026-12-31",
  "pharmacy_establishment": "Pharmacie Centrale"
}
```

## Documents

| Method | Route | Description |
| --- | --- | --- |
| POST | `/documents/upload` | Upload securise, calcule SHA-256 et QR token. |
| GET | `/documents` | Liste paginee. |
| GET | `/documents/{id}` | Detail. |
| PUT | `/documents/{id}` | Metadonnees. |
| DELETE | `/documents/{id}` | Archive. |
| POST | `/documents/{id}/sign` | Signe le document. |
| POST | `/documents/{id}/verify` | Verifie hash et signature. |

## OCR (pre-remplissage formulaires)

| Method | Route | Auth | Description |
| --- | --- | --- | --- |
| POST | `/ocr/extract` | Sanctum (throttle 10/min) | Scanne une carte professionnelle, un diplome ou une piece d'identite (`document`: image jpg/jpeg/png/webp, 8 Mo max) et retourne le texte brut + un pre-remplissage best-effort (`first_name`, `last_name`, `ordinal_number`, `license_number`, `license_expires_at`). L'image n'est jamais persistee sur disque. Necessite `tesseract-ocr` + `tesseract-ocr-fra` installes sur le serveur. |

```json
// Reponse 200
{
  "raw_text": "...",
  "fields": {
    "first_name": "Jean",
    "last_name": "Kabamba",
    "ordinal_number": "PH-RDC-2026-000123",
    "license_number": null,
    "license_expires_at": "2027-08-15"
  }
}
```

## Verification publique

| Method | Route | Auth | Description |
| --- | --- | --- | --- |
| GET | `/verify/{qrCode}` | Public | Verifie un pharmacien, document, numero ordinal ou licence. |

Chaque verification publique cree une entree dans le journal d'audit avec IP, user-agent, UTC, action et ressource concernee.
