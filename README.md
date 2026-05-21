# FRTZ PawCare

Pet care web platform with admin panel, customer shop, appointments, and REST API (API Platform + JWT). Built with **Symfony 7.3** and **PHP 8.2+**.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8 (Docker recommended)
- OpenSSL (for JWT keys)

## Quick start

### 1. Install dependencies

```bash
composer install
```

### 2. Environment

Copy `.env` and set `DATABASE_URL`, mailer, and optional Google OAuth / contact form URLs.

### 3. Database (Docker)

**MySQL only** (Symfony on host):

```bash
docker compose up -d mysql phpmyadmin
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:create-default-admin
php bin/console app:create-demo-customer
```

**Full stack** (nginx + PHP-FPM app container):

```bash
# Copy secrets: cp .env.example .env.local  (APP_SECRET, Brevo, Google OAuth)
docker compose up --build -d
docker compose exec app php bin/console app:create-default-admin
docker compose exec app php bin/console app:create-demo-customer
```

App: http://127.0.0.1:8000 — set `APP_URL=http://127.0.0.1:8000` so verification emails use the correct host.

**Railway:** see `config/deploy.env.example`. Connect the repo, add MySQL, set env vars, deploy (uses `Dockerfile` + `railway.toml`). Google redirect URI: `https://<your-domain>/connect/google/check`.

Default admin (from command): `admin@pawcare.local` / `Admin@12345`  
Demo customer (mobile + API): `customer@pawcare.local` / `Customer@12345`

### 4. JWT keys (first-time setup)

```bash
php bin/console lexik:jwt:generate-keypair --skip-if-exists
```

### 5. Run the app

```bash
symfony server:start
# or: php -S 127.0.0.1:8000 -t public
```

- Web: http://127.0.0.1:8000  
- API docs: http://127.0.0.1:8000/api/docs  
- phpMyAdmin: http://127.0.0.1:8080  

## Customer API (rubric #2 — mobile-ready)

All customer endpoints use JSON: `{ "success": true, "data": ... }` or `{ "success": false, "error": { "code", "message" } }`.

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/login` | Public | JWT token (`email`, `password`) |
| POST | `/api/register` | Public | Create account + verification email |
| GET | `/api/products` | Public | Product catalog (API Platform) |
| GET | `/api/services` | Public | Services catalog |
| GET | `/api/customer/profile` | JWT | Customer profile |
| PATCH | `/api/customer/profile` | JWT | Update email/password |
| GET | `/api/customer/orders` | JWT | Order history |
| POST | `/api/customer/orders` | JWT | Create order `{ "items": [{ "productId": 1, "quantity": 2 }] }` |
| POST | `/api/customer/orders/{id}/payments` | JWT | Pay order `{ "method": "card" }` |
| GET/POST | `/api/customer/appointments` | JWT | List / book appointments |

**Mobile app** (`mobile/`): Expo customer app — see `mobile/README.md`.

**Mobile API** (same database as web):  
`GET /api/mobile/sync`, `POST /api/mobile/orders`, `POST /api/mobile/appointments`, catalog routes.

## Roles (rubric #4)

| Role | Web | API |
|------|-----|-----|
| Customer | Shop, cart, profile, book appointments | `/api/customer/*` |
| Staff | `/admin` products & services | Staff JWT + API Platform mutations |
| Admin | Full admin + appointments | `/api/users`, appointment admin |

## Documentation

- Interactive API: `/api/docs`
- Rubric checklist: `public/RUBRIC_SETUP.md`
- Full API reference: `docs/API.md`

## Tests & validation

```bash
php bin/console lint:container
php bin/console doctrine:schema:validate --skip-sync
```

## Mobile app (rubric #1)

```bash
cd mobile && npm install && npm start
```

Uses JWT + `/api/mobile/*` — orders and appointments created on mobile appear on web `/profile` immediately.
