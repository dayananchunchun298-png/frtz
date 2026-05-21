# FRTZ PawCare — Customer Mobile App

Expo (React Native) customer app for rubric criterion **#1 Mobile Integration** and **#5 Web/Mobile Sync**.

## Prerequisites

- Node.js 18+
- Symfony API running at `http://127.0.0.1:8000`
- Demo customer: `php bin/console app:create-demo-customer`

## Run

```bash
cd mobile
npm install
npm start
```

Press `w` for web, or scan QR with Expo Go on a device.

### API URL

| Environment | API base URL |
|-------------|----------------|
| Web / iOS simulator | `http://127.0.0.1:8000` |
| Android emulator | `http://10.0.2.2:8000` (set in Profile tab) |
| Physical device | Your PC LAN IP, e.g. `http://192.168.1.x:8000` |

## Features (end-to-end)

- JWT login / register (`POST /api/login`, `/api/register`)
- Shop catalog + checkout (`POST /api/mobile/orders`)
- Book appointments (`POST /api/mobile/appointments`)
- Pay pending orders (`POST /api/mobile/orders/{id}/payments`)
- Pull-to-refresh sync (`GET /api/mobile/sync`) — same database as web `/profile`

## Demo credentials

- `customer@pawcare.local` / `Customer@12345`
- `fritzmarvindayanan@gmail.com` / `Customer@12345` (after `app:create-demo-customer`)
