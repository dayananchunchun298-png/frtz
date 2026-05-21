# FRTZ PawCare — Rubric setup & demo guide

## Quick run (deployment #9)

```bash
composer install
docker compose up -d
php bin/console lexik:jwt:generate-keypair --skip-if-exists
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:create-default-admin
php bin/console app:create-demo-customer
symfony server:start
```

**Mobile app:**

```bash
cd mobile && npm install && npm start
```

- Web: http://127.0.0.1:8000  
- API docs: http://127.0.0.1:8000/api/docs  
- phpMyAdmin: http://127.0.0.1:8080  
- Mobile demo login: `customer@pawcare.local` / `Customer@12345`

See also: `README.md`, `docs/API.md`, `mobile/README.md`

---

## #1 Customer Mobile App Integration

| Check | How to demo |
|-------|-------------|
| Consumes Customer API | Expo app in `mobile/` uses JWT + `/api/mobile/*` |
| Navigation & UX | Bottom tabs: Shop, Book, Orders, Profile |
| End-to-end | Login → add to cart → checkout → book appointment → pay order → pull refresh |

---

## #2 Customer API (5+ REST endpoints)

| Domain | Endpoints |
|--------|-----------|
| Products | `GET /api/products`, `GET /api/mobile/products` |
| Bookings | `GET/POST /api/customer/appointments`, `POST /api/mobile/appointments` |
| Orders | `GET/POST /api/customer/orders`, `POST /api/mobile/orders` |
| Profile | `GET/PATCH /api/customer/profile`, `GET /api/mobile/me` |
| Payments | `POST /api/customer/orders/{id}/payments`, mobile equivalent |

Auth: `POST /api/login` → JWT → `Authorization: Bearer <token>`

---

## #3 Authentication & security

- JWT (Lexik) for API; session + CSRF for web login  
- Email verification required (`is_verified`) before customer API  
- Password: min 8 chars, upper, lower, number (`PasswordStrength` validator)  
- Google OAuth for staff (`/connect/google`)  
- Demo credentials: `php bin/console app:create-demo-customer`

---

## #4 RBAC

| Role | Access |
|------|--------|
| **Customer** | Shop, cart, `/profile`, `/appointments/new`, `/api/customer/*`, mobile app |
| **Staff** | `/admin` products & services, staff API mutations |
| **Admin** | Full `/admin`, `/dashboard`, `/api/users` |

Demo admin: `php bin/console app:create-default-admin`

---

## #5 Mobile / web sync

Shared MySQL database; mobile `GET /api/mobile/sync` returns user, orders, appointments, catalog.

**Demo:** Place order in mobile → open web **My Account** → order listed. Book appointment on mobile → web **Appointments** shows it.

---

## #6 Database

Entities: User, Product, Service, Appointment, Order, OrderItem, Payment.  
User linked to Order and Appointment. Run migrations after pull.

---

## #7 Error handling

API JSON errors: `{ "success": false, "error": { "code", "message" } }`  
Validation: HTTP `422` with `violations[]`.  
Web: flash messages + form errors.  
Mobile: inline `ErrorBanner` + alerts.

---

## #8 UI/UX

Shared branding: navy `#1e3a8a`, orange `#ff6b35`, violet `#a78bfa`, dark gradient background.  
Web: `base.html.twig` + `app.css`. Mobile: `mobile/src/theme.ts`. Responsive web `@media`; mobile native layouts.

---

## #9 Deployment & stability

- Docker MySQL + phpMyAdmin  
- Symfony local server or `php -S`  
- `php bin/console lint:container` before demo  

---

## #10 Documentation

- Swagger: `/api/docs`  
- `docs/API.md` — request/response samples  
- `README.md` — installation  
- `mobile/README.md` — mobile setup  

---

## Earlier checklist items

### Landing / About / Contact

- Home `/` — five sections  
- `/about` — Meet the team  
- `/contact` — set `CONTACT_FORM_EMBED_URL` in `.env`  

### Google OAuth (staff)

1. Google Cloud OAuth client  
2. Redirect: `http://127.0.0.1:8000/connect/google/check`  
3. `.env`: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`  

### Email verification

- Web: `/register` → email link  
- API: `POST /api/register`, `GET /api/verify-email?token=...`  
