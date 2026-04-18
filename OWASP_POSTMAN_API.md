# Campfix API - OWASP Top 10 2013 Security & Postman Guide

## Quick Start

**Server:** `http://127.0.0.1:8000`

### Register & Get Token
```bash
curl -X POST http://127.0.0.1:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"name\":\"YourName\",\"email\":\"you@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\"}"
```

### Use Token
Add header: `Authorization: Bearer YOUR_TOKEN`

---

## OWASP Top 10 2013 - Security Fixes Applied

| # | Vulnerability | Fix Applied |
|---|---------------|-------------|
| A1 | Injection | Eloquent ORM (parameterized queries) |
| A2 | Broken Auth | Secure OTP (random_int) + rate limiting |
| A3 | XSS | Auto-escaped JSON output |
| A4 | IDOR | Authorization checks on all resources |
| A5 | Security Misconfig | CORS + rate limiting configured |
| A6 | Sensitive Data | bcrypt hashing + Sanctum tokens |
| A7 | Access Control | Role-based middleware |
| A8 | CSRF | Token-based API authentication |
| A9 | Vulnerable Components | Laravel 12 + Sanctum |
| A10 | Unvalidated Redirects | No dynamic redirects |

---

## API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register & get token |
| POST | `/api/auth/login` | Login |
| GET | `/api/auth/user` | Get current user |
| POST | `/api/auth/logout` | Logout |

### Concerns
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/concerns` | List concerns |
| POST | `/api/concerns` | Create concern |
| GET | `/api/concerns/{id}` | View concern |
| PUT | `/api/concerns/{id}` | Update concern |
| DELETE | `/api/concerns/{id}` | Delete concern |

### Events
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/events` | List events |
| POST | `/api/events` | Create event |
| GET | `/api/events/{id}` | View event |

### Categories
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/categories` | List categories |

### Admin
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/admin/dashboard` | Dashboard stats |
| GET | `/api/admin/users` | List users |
| GET | `/api/admin/concerns` | All concerns |

---

## Postman Collection (JSON)

Import this into Postman:

```json
{
  "info": {
    "name": "Campfix API",
    "description": "API with OWASP Top 10 2013 security"
  },
  "variable": [
    {"key": "base_url", "value": "http://127.0.0.1:8000/api"},
    {"key": "token", "value": ""}
  ],
  "auth": {"type": "bearer", "bearer": [{"key": "token", "value": "{{token}}"}]},
  "item": [
    {
      "name": "Health Check",
      "request": {"method": "GET", "url": "{{base_url}}/health"}
    },
    {
      "name": "Register",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/auth/register",
        "header": [{"key": "Content-Type", "value": "application/json"}],
        "body": {"mode": "raw", "raw": "{\"name\":\"Test\",\"email\":\"test@test.com\",\"password\":\"12345678\",\"password_confirmation\":\"12345678\"}"}
      }
    },
    {
      "name": "Get User",
      "request": {"method": "GET", "url": "{{base_url}}/auth/user"}
    },
    {
      "name": "Logout",
      "request": {"method": "POST", "url": "{{base_url}}/auth/logout"}
    },
    {
      "name": "Categories",
      "request": {"method": "GET", "url": "{{base_url}}/categories"}
    }
  ]
}
```

---

## Files Created

| File | Purpose |
|------|---------|
| `config/cors.php` | CORS configuration |
| `routes/api.php` | API routes |
| `app/Http/Controllers/Api/*.php` | API Controllers |
| `app/Providers/AppServiceProvider.php` | Rate limiting |
| `app/Models/User.php` | Added HasApiTokens |
| `composer.json` | Added Sanctum |
| `postman_collection.json` | Postman collection |
| `SECURITY.md` | Full security docs |

---

## Rate Limits

- API: 60 requests/minute
- Login: 5 attempts/minute
- OTP: 3 requests/minute
