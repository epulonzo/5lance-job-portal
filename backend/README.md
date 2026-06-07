# 5Lance Backend (PHP Slim 4 REST API)

REST API for the 5Lance freelance job board. Built with PHP Slim 4, PDO (MySQL), and JWT auth, per `Group6_Proposal.pdf`.

## Stack
- PHP 8.0+, Slim 4, slim/psr7
- MySQL + PDO (parameterised queries)
- firebase/php-jwt for stateless JWT authentication
- vlucas/phpdotenv for environment config

## Local setup

1. Install dependencies (requires [Composer](https://getcomposer.org)):
   ```
   composer install
   ```
2. Copy `.env.example` to `.env` and fill in your local DB credentials and a strong `JWT_SECRET`.
3. Create the database and load the schema + sample data:
   ```
   mysql -u root < database/schema.sql
   mysql -u root < database/seed.sql
   ```
   All seeded accounts use the password `Password123` (e.g. `admin@5lance.com`, `amyzal@5lance.com` (client), `adam@5lance.com` / `saiful@5lance.com` (freelancers)).
4. Start the PHP dev server from the project root:
   ```
   php -S 127.0.0.1:8080 -t public
   ```
5. The API is now available at `http://127.0.0.1:8080`. Point the frontend's `VITE_API_URL` (or equivalent Axios base URL) at this address, and set `CORS_ORIGIN` in `.env` to the frontend's dev URL (default `http://localhost:5173`).

## Project structure
```
backend/
├── public/
│   ├── index.php       # Slim app entry point, routes, middleware
│   └── .htaccess       # Rewrites all requests to index.php (Apache)
├── src/
│   ├── Controllers/    # JobController, ApplicationController
│   ├── Middleware/     # JwtAuthMiddleware (verifies Bearer token + role)
│   └── Helpers/        # Database (PDO), JwtHelper, Validator, Responder
├── database/
│   ├── schema.sql      # users, jobs, applications tables
│   └── seed.sql        # sample accounts, jobs, applications
├── .env.example        # copy to .env and fill in
└── composer.json
```

## Endpoints implemented here (Jobs & Applications)
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/jobs` | none | List jobs (filters: `category`, `status`, `min_budget`, `max_budget`, `search`) |
| GET | `/api/jobs/{id}` | none | Get single job |
| POST | `/api/jobs` | JWT (client) | Create job |
| PUT | `/api/jobs/{id}` | JWT (client, owner) | Update job |
| DELETE | `/api/jobs/{id}` | JWT (client owner / admin) | Delete job |
| GET | `/api/jobs/{id}/applications` | JWT (client, job owner) | List applications for a job |
| POST | `/api/jobs/{id}/applications` | JWT (freelancer) | Submit application |
| PUT | `/api/applications/{id}` | JWT (client, job owner) | Update application status |
| DELETE | `/api/applications/{id}` | JWT (freelancer, owner) | Withdraw application |

Auth (`/api/auth/*`) and Users (`/api/users/*`) endpoints are owned by the teammate handling Authentication — wire them into the same `$app->group('/api', ...)` block in `public/index.php`.

## Authentication contract
Protected routes expect `Authorization: Bearer <jwt>`. The JWT payload must contain `user_id` and `role` (`freelancer` | `client` | `admin`) — `JwtAuthMiddleware` reads these to authorize requests and attaches the decoded payload to the request as `jwt_user`. Coordinate with whoever builds `/api/auth/login` to ensure the token is issued with this exact payload shape (see `src/Helpers/JwtHelper::encode`).

## Deployment (000webhost / InfinityFree)

Both are free PHP + MySQL hosts that work over (s)FTP with an Apache/LiteSpeed front end — no SSH/Composer on the server, so dependencies must be uploaded pre-built.

1. **Build locally first**: run `composer install --no-dev --optimize-autoloader` so `vendor/` contains everything needed (the host won't run Composer for you).
2. **Set production env vars**: create a `.env` on the server (outside or alongside the app) with the host's MySQL credentials (created via their control panel) and a strong, unique `JWT_SECRET`. Set `APP_ENV=production` and `CORS_ORIGIN` to your deployed frontend URL.
3. **Upload**: upload the entire `backend/` folder (including `vendor/`, excluding `.git`) via FTP. Point the host's document root at `backend/public` if it allows custom doc roots; otherwise upload `public/*` to the web root and adjust the `require __DIR__ . '/../vendor/autoload.php'` / `Dotenv::createImmutable(...)` paths in `index.php` to match the new relative location of `vendor/` and `.env`.
4. **Database**: use the host's phpMyAdmin to create the database, then import `database/schema.sql` followed by `database/seed.sql` (or your real data).
5. **Verify routing**: confirm `.htaccess` (mod_rewrite) is enabled so all requests route through `index.php` — visit `https://yourdomain/api/jobs` and confirm you get JSON, not a 404.
6. **Update the frontend**: point the deployed Vue app's API base URL at the live backend URL.

## Notes
- All queries use PDO prepared statements with bound parameters — no raw string interpolation, so the API is protected against SQL injection by construction.
- Validation errors return `422` with a `details` object keyed by field name; auth failures return `401`/`403`; not-found returns `404`; conflicts (duplicate application, applying to a closed job) return `409`.
