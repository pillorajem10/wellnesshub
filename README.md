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

The backend `.env.example` includes Typesense settings:

- `TYPESENSE_HOST`
- `TYPESENSE_PORT`
- `TYPESENSE_PROTOCOL`
- `TYPESENSE_API_KEY`
- `TYPESENSE_SEARCH_ONLY_API_KEY`
- `TYPESENSE_COLLECTION_PROTOCOLS`

If search isn’t returning results:

- Confirm these values are set correctly in `wellnesshub-backend/.env`
- Ensure the Typesense collection referenced by the app exists and is populated
- If you are using your own Typesense project, replace the API keys with your own (avoid committing secrets)

