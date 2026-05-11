# PHP + MySQL Auth Starter

This folder is isolated from existing project files and can be used as a team baseline.

## Quick Start
1. Create database/table:
   - Run `sql/schema.sql`
2. Optional demo user:
   - Generate hash:
     - `php -r "echo password_hash('demo123', PASSWORD_DEFAULT), PHP_EOL;"`
   - Replace hash placeholder in `sql/seed.sql`, then run it.
3. Configure DB env (or keep defaults in `api/config/db.php`):
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - default DB name is `car_sales`
4. Start PHP server from this folder:
   - `php -S 127.0.0.1:8080`
5. Open:
   - `http://127.0.0.1:8080/register.html`
   - `http://127.0.0.1:8080/login.html`

## API Endpoints
- `POST /api/auth/register.php`
- `POST /api/auth/login.php`
- `POST /api/auth/logout.php`
- `GET /api/auth/me.php`

## Team Rule
- All teammates should follow `BACKEND_CONTRACT.md` exactly for request/response fields and status codes.
