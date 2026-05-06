# WellnessHub

WellnessHub is a full-stack app:

- **Backend**: Laravel (PHP) API
- **Frontend**: React (Vite) + TailwindCSS

## Prerequisites

### Backend (Laravel)

- **PHP**: 8.2.12
- **Composer**: 2.7.8
- **XAMPP**: Apache + MySQL running

### Frontend (React + TailwindCSS)

- **Node.js**: 20.11.1
- **npm**: 10.2.4

> Both backend and frontend ship with `.env.example` files that are **copy/paste ready** for `.env`. Update values as needed for your local machine.

## Clone the repository

```bash
git clone https://github.com/pillorajem10/wellnesshub.git
cd wellnesshub
```

## Backend (Laravel) setup

### 1) Start XAMPP services

- Open **XAMPP Control Panel**
- Start:
  - **Apache**
  - **MySQL**

### 2) Install backend dependencies

```bash
cd wellnesshub-backend
composer install
```

### 3) Create and configure environment file

Copy `.env.example` to `.env` (copy-paste ready):

```bash
copy .env.example .env
```

Open `wellnesshub-backend/.env` and verify/update at minimum:

- **APP_URL** (default): `http://localhost:8000`
- **FRONTEND_URL** (default): `http://localhost:5173`
- **Database** (XAMPP MySQL defaults are already set in the example):
  - `DB_HOST=127.0.0.1`
  - `DB_PORT=3306`
  - `DB_DATABASE=wellnesshub`
  - `DB_USERNAME=root`
  - `DB_PASSWORD=` (empty by default in many XAMPP installs)

> If you change ports/hosts, also update `SANCTUM_STATEFUL_DOMAINS` to match your frontend origin.

### 4) Generate app key

```bash
php artisan key:generate
```

### 5) Run migrations and seeders

```bash
php artisan migrate
php artisan db:seed
```

### 6) Start the backend server

```bash
php artisan serve
```

By default the API will run at `http://localhost:8000`.

### Typesense Setup, SSL Certificate, and Reindexing

The backend supports Typesense (cloud) for search. Typesense settings live in `wellnesshub-backend/.env` (copied from `.env.example`).

#### Typesense SSL Certificate Setup for XAMPP / Windows

If you’re using **Typesense Cloud over HTTPS** from a local **Windows + XAMPP** setup, PHP (Guzzle/cURL) may fail with:

- `cURL error 60: SSL certificate problem: unable to get local issuer certificate`

This usually happens because your local PHP doesn’t know where to find a trusted **CA certificate bundle**. The fix is to download a CA bundle and point PHP to it in `php.ini`.

Do **not** disable SSL verification — it’s unsafe and not recommended.

1. Download the latest CA bundle:
   - `https://curl.se/ca/cacert.pem`

2. Save it in a local XAMPP PHP folder, for example:
   - `C:/xampp/php/extras/ssl/cacert.pem`

3. Check the active PHP configuration file:

```bash
php --ini
```

4. Open the active `php.ini` file (usually):
   - `C:/xampp/php/php.ini`

5. Add or update these lines:

```ini
curl.cainfo="C:/xampp/php/extras/ssl/cacert.pem"
openssl.cafile="C:/xampp/php/extras/ssl/cacert.pem"
```

6. Restart Apache from XAMPP and reopen the terminal.

7. Clear Laravel config/cache:

```bash
php artisan config:clear
php artisan cache:clear
```

8. Test Typesense search or run the reindex flow again.

If this step is skipped, the app may still run, but **Typesense search/reindexing may fail locally** because Laravel cannot verify the SSL certificate from Typesense Cloud.

#### Reindexing Typesense

After setting up the database, seeders, and your Typesense collections, you can reindex searchable data using either the CLI command or the API route.

**Option 1: Reindex using Artisan command (recommended locally)**

```bash
php artisan typesense:reindex
```

- Recommended for local development
- Avoids browser/API timeout issues for larger data sets

**Option 2: Reindex using API route**

Endpoint:
- `POST http://127.0.0.1:8000/api/typesense/reindex`

Headers:
- `Accept: application/json`
- `Content-Type: application/json`
- `Authorization: Bearer YOUR_AUTH_TOKEN`

Example `curl`:

```bash
curl -X POST "http://127.0.0.1:8000/api/typesense/reindex" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_AUTH_TOKEN"
```

- Use a valid Laravel/Sanctum Bearer token from a logged-in user
- Do **not** use the Typesense API key as the Bearer token
- The Typesense API key stays in `wellnesshub-backend/.env`
- The API route is useful for manually triggering reindexing from an authenticated request

After successful reindexing:
- Protocols and threads from the database should appear in the Typesense collections
- Search results should return `"source": "typesense"` (when the response includes a `source` field)

## Frontend (React / Vite + TailwindCSS) setup

### 1) Install frontend dependencies

```bash
cd ..\wellnesshub-frontend
npm install
```

### 2) Create and configure environment file

Copy `.env.example` to `.env` (copy-paste ready):

```bash
copy .env.example .env
```

Open `wellnesshub-frontend/.env` and set the backend API URL:

- `VITE_API_URL=http://127.0.0.1:8000/api`

### 3) Start the frontend dev server

```bash
npm run dev
```

Vite will print the local URL (commonly `http://localhost:5173`).

## Run Backend + Frontend together (local testing)

Run both servers at the same time to test search, filters, voting, comments, and Typesense integration.

### Terminal A (Backend)

```bash
cd wellnesshub-backend
php artisan serve
```

### Terminal B (Frontend)

```bash
cd wellnesshub-frontend
npm run dev
```

### Quick verification checklist

- **Backend**: `http://localhost:8000` responds (and your API routes under `/api` are reachable)
- **Frontend**: `http://localhost:5173` loads and can call the API (`VITE_API_URL`)
- **Auth/session (Sanctum)**: frontend origin matches backend `.env` values (`FRONTEND_URL`, `SANCTUM_STATEFUL_DOMAINS`)
- **Database**: migrations + seeders ran successfully (data exists for filtering/voting/comments)

## Typesense (search)

Typesense settings are configured in `wellnesshub-backend/.env` (copy from `.env.example`).

If search isn’t returning results:
- Confirm the Typesense environment variables are set correctly in `wellnesshub-backend/.env`
- Ensure the Typesense collection referenced by the app exists and is populated
- If you’re using Typesense Cloud on Windows/XAMPP, complete the SSL certificate setup above

