# CampFix API Documentation v1.0

## Overview
This document outlines the API endpoints available in the CampFix system, implementing OWASP API Security Top 10 protections.

## API Versioning
All API endpoints are versioned under `/api/v1/` path. Current version: **v1.0**

Example: `GET /api/v1/concerns`

## Breaking Changes Policy
- Major version changes (v2, v3) may include breaking changes
- Minor updates maintain backward compatibility
- Deprecated endpoints will be marked in documentation

## Authentication
All API endpoints require JWT authentication unless otherwise noted.

### Login
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

### User Info
```
GET /api/auth/user
Authorization: Bearer {token}
```

## Rate Limiting
- Login endpoints: 5 attempts per 15 minutes
- Sensitive operations (CRUD, status changes): 10 attempts per minute
- General API access: 60 requests per minute

## API Endpoints

### Categories
```
GET /api/categories
POST /api/categories (Admin only)
DELETE /api/categories/{id} (Admin only)
```

### Concerns
```
GET /api/concerns (User sees own concerns)
POST /api/concerns
GET /api/concerns/{id} (Owner, assigned user, or MIS only)
PUT /api/concerns/{id}
DELETE /api/concerns/{id}
```

### Reports
```
GET /api/reports (Admin roles only)
POST /api/reports
GET /api/reports/{id} (Admin, assigned maintenance, or shared rooms reports)
PUT /api/reports/{id} (Admin roles only)
DELETE /api/reports/{id} (Admin roles only)
```

### Events
```
GET /api/events (Filtered by user permissions)
POST /api/events
GET /api/events/{id} (Filtered by permissions)
PUT /api/events/{id} (Admin roles only)
DELETE /api/events/{id} (Admin roles only)

POST /api/events/{id}/approve (Role-based approval workflow)
POST /api/events/{id}/reject (Role-based rejection)
```

## Security Features Implemented

### API1:2023 Broken Object Level Authorization (BOLA)
- Ownership validation for concerns and reports
- Role-based access controls
- Assigned user checks for maintenance staff

### API2:2023 Broken Authentication
- JWT token authentication
- Token refresh mechanisms
- Secure password handling

### API3:2023 Broken Object Property Level Authorization
- Field-level access control for sensitive data
- Cost, resolution notes, and maintenance details hidden from unauthorized users
- Admin and assigned user access only for sensitive fields

### API4:2023 Unrestricted Resource Consumption
- Rate limiting on login (5/15min)
- Rate limiting on sensitive operations (10/min)
- Request size limits
- Resource pagination limits

### API5:2023 Broken Function Level Authorization (BFLA)
- Role-based function access
- Middleware validation for admin operations
- Approval workflow restrictions

### API6:2023 Unrestricted Access to Sensitive Business Flows
- Multi-level approval system for events
- Status change validations
- Business rule enforcement (e.g., cannot approve already approved items)

### API7:2023 Server Side Request Forgery (SSRF)
- SSRF protection middleware available
- URL validation and sanitization
- Internal network blocking
- Domain whitelisting

### API8:2023 Security Misconfiguration
- Security headers (CSP, HSTS, X-Frame-Options, etc.)
- HTTPS enforcement
- Secure cookie settings
- Input sanitization

### API9:2023 Improper Inventory Management
- Documented API endpoints
- Version control
- Clear authentication requirements

### API10:2023 Unsafe Consumption of APIs
- Third-party API response validation
- Timeout protection
- Error handling without information disclosure
- Response structure validation

## Error Responses
All API errors follow a consistent format:

```json
{
  "error": "Error message",
  "code": "error_code",
  "retry_after": 60 // for rate limited requests
}
```

## Status Codes
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Validation Error
- 429: Too Many Requests
- 500: Internal Server Error

## Data Validation
- All inputs are sanitized
- SQL injection prevention via Eloquent ORM
- XSS prevention via input sanitization
- File upload validation and security scanning

## Logging and Monitoring
- Security events logged
- Rate limit violations tracked
- API usage monitoring
- Failed authentication attempts logged