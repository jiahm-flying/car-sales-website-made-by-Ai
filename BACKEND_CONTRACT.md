# BACKEND CONTRACT (QMSL Auth)

## 1. Goal
- Provide a unified, team-wide auth contract for `register`, `login`, `logout`, and `me`.
- All frontend pages must call backend APIs; do not use localStorage as auth source of truth.

## 2. Runtime Standard
- PHP: `8.2+`
- MySQL: `8.0+`
- Charset: `utf8mb4`
- Session auth: enabled (`PHPSESSID` cookie)

## 3. Directory Convention
- `api/config/db.php` database connection
- `api/config/response.php` response helpers
- `api/auth/register.php`
- `api/auth/login.php`
- `api/auth/logout.php`
- `api/auth/me.php`
- `sql/schema.sql`
- `sql/seed.sql` (optional demo data)

## 4. User Data Schema
Request fields and DB fields must be aligned:
- `name` string, required, max 80
- `address` string, required, max 150
- `phone` string, required, max 20
- `email` string, required, max 120, unique
- `username` string, required, max 40, unique
- `password` string, required, max 60 (stored as `password_hash`)

## 5. API Contract

### 5.1 POST `/api/auth/register.php`
Request JSON:
```json
{
  "name": "Zhang Wei",
  "address": "Chaoyang Road 123",
  "phone": "13812345678",
  "email": "seller@example.com",
  "username": "zhangwei01",
  "password": "abc12345"
}
```

Success `201`:
```json
{
  "ok": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Zhang Wei",
      "address": "Chaoyang Road 123",
      "phone": "13812345678",
      "email": "seller@example.com",
      "username": "zhangwei01",
      "created_at": "2026-05-10 10:00:00"
    }
  }
}
```

Failure examples:
- `409` username/email conflict
- `422` validation failed

### 5.2 POST `/api/auth/login.php`
Request JSON:
```json
{
  "username": "zhangwei01",
  "password": "abc12345"
}
```

Success `200`:
```json
{
  "ok": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Zhang Wei",
      "address": "Chaoyang Road 123",
      "phone": "13812345678",
      "email": "seller@example.com",
      "username": "zhangwei01",
      "created_at": "2026-05-10 10:00:00"
    }
  }
}
```

Failure:
- `401` invalid username/password
- `422` missing fields

### 5.3 POST `/api/auth/logout.php`
Success `200`:
```json
{
  "ok": true,
  "message": "Logout successful"
}
```

### 5.4 GET `/api/auth/me.php`
If logged in, success `200` with `data.user`.
If not logged in, `401`:
```json
{
  "ok": false,
  "message": "Not authenticated"
}
```

## 6. Response Format (Mandatory)
All endpoints must return JSON with:
- `ok`: boolean
- `message`: string
- `data`: object (optional)
- `errors`: object (optional, for validation)

## 7. Validation Rules (Unified)
- `name`: letters/spaces, 2-80 chars
- `address`: non-empty, 3-150 chars
- `phone`: `^1[3-9]\d{9}$`
- `email`: valid email format
- `username`: `^[A-Za-z0-9]{6,40}$`
- `password`: `^[A-Za-z0-9]{6,60}$`

## 8. Security Rules (Mandatory)
- Store password with `password_hash(..., PASSWORD_DEFAULT)`.
- Verify password with `password_verify(...)`.
- Never return `password_hash` in API responses.
- Use prepared statements only; no string-concatenated SQL.
- Use `session_regenerate_id(true)` after login/register success.

## 9. Frontend Calling Rules (Mandatory)
- Use `fetch(..., { credentials: "include" })`.
- Always parse and check `result.ok`.
- Do not redirect before API success.
- Keep frontend form validation aligned with this contract.
