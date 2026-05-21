# FRTZ PawCare — API Reference

Base URL: `http://127.0.0.1:8000`

## Authentication

### Login (JWT)

```http
POST /api/login
Content-Type: application/json

{
  "email": "customer@example.com",
  "password": "Secret1a"
}
```

**Response 200**

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```

Use on protected routes:

```http
Authorization: Bearer <token>
```

### Register

```http
POST /api/register
Content-Type: application/json

{
  "email": "customer@example.com",
  "password": "Secret1a"
}
```

Password rules: min 8 chars, uppercase, lowercase, and a number.

---

## Customer endpoints (`/api/customer`)

Requires verified account + `Authorization: Bearer <token>`.

### Profile

```http
GET /api/customer/profile
```

```http
PATCH /api/customer/profile
Content-Type: application/json

{
  "email": "new@example.com",
  "password": "NewSecret1b"
}
```

### Orders

```http
GET /api/customer/orders
```

```http
POST /api/customer/orders
Content-Type: application/json

{
  "items": [
    { "productId": 1, "quantity": 2 }
  ]
}
```

**Response 201**

```json
{
  "success": true,
  "data": {
    "id": 5,
    "status": "pending",
    "subtotal": "19.98",
    "tax": "1.60",
    "total": "21.58",
    "items": [...]
  }
}
```

### Payments (stub gateway)

```http
POST /api/customer/orders/5/payments
Content-Type: application/json

{
  "method": "card"
}
```

Marks order as `paid` and returns a `transactionReference`.

### Appointments

```http
GET /api/customer/appointments
```

```http
POST /api/customer/appointments
Content-Type: application/json

{
  "name": "Buddy",
  "petType": "dog",
  "appointmentDate": "2026-06-01T10:00:00+02:00"
}
```

---

## Mobile API (`/api/mobile`)

Used by the Expo customer app in `mobile/`. Same database as the web app.

| Method | Path | Auth |
|--------|------|------|
| GET | `/api/mobile/health` | Public |
| GET | `/api/mobile/products` | Public |
| GET | `/api/mobile/services` | Public |
| GET | `/api/mobile/sync/catalog` | Public |
| GET | `/api/mobile/sync` | JWT — full account snapshot |
| GET | `/api/mobile/me` | JWT |
| GET | `/api/mobile/orders` | JWT |
| POST | `/api/mobile/orders` | JWT — `{ "items": [{ "productId": 1, "quantity": 2 }] }` |
| POST | `/api/mobile/orders/{id}/payments` | JWT — `{ "method": "card" }` |
| GET | `/api/mobile/appointments` | JWT |
| POST | `/api/mobile/appointments` | JWT — same body as customer appointments |

Demo login: run `php bin/console app:create-demo-customer`, then `customer@pawcare.local` / `Customer@12345`.

---

## Error format

```json
{
  "success": false,
  "error": {
    "code": "validation_failed",
    "message": "Validation failed.",
    "violations": [
      { "field": "name", "message": "Pet name is required." }
    ]
  }
}
```

Common HTTP status codes: `400`, `401`, `403`, `404`, `409`, `422`, `500`.

---

## Staff / Admin (API Platform)

Browse **Swagger UI** at `/api/docs` for full CRUD on products, services, appointments, orders, and users (role-restricted).
