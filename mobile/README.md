# FRTZ PawCare — Customer Mobile App

Expo (React Native) customer app for rubric criterion **#1 Mobile Integration** and **#5 Web/Mobile Sync**.

## API

All requests use the production Railway backend:

**https://frtz-production.up.railway.app**

Configured in `mobile/src/api.ts` as `API_BASE_URL`. There is no localhost override in the app.

Web app (same database): https://frtz-production.up.railway.app/

## Prerequisites

- Node.js 18+
- Internet access (device or emulator reaches Railway)

## Run

```bash
cd mobile
npm install
npm start
```

Press `w` for web, or scan QR with Expo Go on a device.

After switching from a local API build, **log in again** (old JWT tokens are invalid).

## Features (end-to-end)

- JWT login / register (`POST /api/login`, `/api/register`)
- Shop catalog + checkout (`POST /api/mobile/orders`)
- Book appointments (`POST /api/mobile/appointments`)
- Pay pending orders (`POST /api/mobile/orders/{id}/payments`)
- Pull-to-refresh sync (`GET /api/mobile/sync`) — same database as web `/profile`

## Demo credentials

- `customer@pawcare.local` / `Customer@12345`

(Account must exist on Railway; run `php bin/console app:create-demo-customer` in Railway shell if needed.)
