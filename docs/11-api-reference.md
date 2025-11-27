# API Reference

Complete API documentation for Spectra's HTTP endpoints.

## Base URL

All endpoints are prefixed with `/spectra`:

```
http://localhost:8000/spectra
```

## Authentication

All endpoints require:
1. User authentication (via configured guard)
2. Authorization via `use-spectra` gate

## Endpoints

### 1. Show Spectra UI

Renders the main Spectra interface.

**Endpoint**: `GET /spectra`

**Response**: HTML (Inertia.js page)

**Initial Data**:
```json
{
  "routes": [...],
  "authenticated": true,
  "user": {...}
}
```

### 2. List Routes

Returns all discoverable routes.

**Endpoint**: `GET /spectra/routes`

**Response**: `200 OK`

```json
{
  "data": [
    {
      "method": "GET",
      "uri": "/api/users",
      "name": "users.index",
      "action": "App\\Http\\Controllers\\UserController@index",
      "middleware": ["web", "auth"],
      "parameters": []
    },
    {
      "method": "POST",
      "uri": "/api/users",
      "name": "users.store",
      "action": "App\\Http\\Controllers\\UserController@store",
      "middleware": ["web", "auth"],
      "parameters": [
        {
          "name": "name",
          "type": "string",
          "required": true,
          "default": null
        },
        {
          "name": "email",
          "type": "string",
          "required": true,
          "default": null
        }
      ]
    }
  ]
}
```

### 3. Get Schema

Generates JSON Schema for an endpoint.

**Endpoint**: `GET /spectra/schema/{method}/{uri}`

**Parameters**:
- `method`: HTTP method (GET, POST, PUT, PATCH, DELETE)
- `uri`: URL-encoded endpoint URI

**Example**:
```
GET /spectra/schema/POST/api%2Fusers
```

**Response**: `200 OK`

```json
{
  "data": {
    "schema": "https://json-schema.org/draft/2020-12/schema",
    "properties": {
      "name": {
        "type": "string",
        "maxLength": 255
      },
      "email": {
        "type": "string",
        "format": "email"
      },
      "age": {
        "type": "integer",
        "minimum": 18,
        "maximum": 120
      }
    },
    "required": ["name", "email"]
  }
}
```

**Error Responses**:

`404 Not Found` - No FormRequest found:
```json
{
  "message": "No schema available for this endpoint"
}
```

### 4. Execute Request

Executes an API request.

**Endpoint**: `POST /spectra/execute`

**Rate Limit**: 60 requests per minute (configurable)

**Request Body**:
```json
{
  "method": "POST",
  "uri": "/api/users",
  "parameters": {
    "name": "John Doe",
    "email": "john@example.com",
    "age": 30
  },
  "headers": {
    "Accept": "application/json",
    "X-Custom-Header": "value"
  },
  "auth_mode": "current",
  "auth_value": null
}
```

**Request Fields**:
- `method` (required): HTTP method
- `uri` (required): Endpoint URI
- `parameters` (optional): Request parameters
- `headers` (optional): Custom headers
- `auth_mode` (required): Authentication mode (`current`, `impersonate`, `bearer`, `basic`)
- `auth_value` (optional): Auth credentials (depends on mode)

**Response**: `200 OK`

```json
{
  "data": {
    "status_code": 201,
    "headers": {
      "content-type": ["application/json"],
      "cache-control": ["no-cache, private"]
    },
    "body": {
      "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2024-01-15T10:30:00.000000Z"
      }
    },
    "execution_time": 0.125
  }
}
```

**Error Responses**:

`422 Validation Error`:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "uri": ["The uri field is required."],
    "method": ["The selected method is invalid."]
  }
}
```

`429 Too Many Requests`:
```json
{
  "message": "Too Many Attempts.",
  "retry_after": 60
}
```

`403 Forbidden` (impersonation):
```json
{
  "message": "This action is unauthorized."
}
```

### 5. List Cookies

Returns all cookies with decrypted values.

**Endpoint**: `GET /spectra/cookies`

**Response**: `200 OK`

```json
{
  "data": [
    {
      "name": "laravel_session",
      "value": {
        "_token": "abc123xyz",
        "login_web_59ba36...": 123
      },
      "encrypted": true,
      "expires": 1704067200,
      "domain": "localhost",
      "path": "/",
      "secure": false,
      "httpOnly": true,
      "sameSite": "lax"
    },
    {
      "name": "XSRF-TOKEN",
      "value": "csrf-token-here",
      "encrypted": false,
      "expires": null,
      "domain": "localhost",
      "path": "/",
      "secure": false,
      "httpOnly": false,
      "sameSite": "lax"
    }
  ]
}
```

## Response Format

All successful responses follow this structure:

```json
{
  "data": {
    // Response data
  }
}
```

Error responses use Laravel's standard format:

```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

## Rate Limiting

The execute endpoint is rate-limited (default: 60 requests/minute).

**Headers**:
- `X-RateLimit-Limit`: Maximum requests per window
- `X-RateLimit-Remaining`: Remaining requests
- `Retry-After`: Seconds until rate limit resets

**Example**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
```

When rate-limited:
```
HTTP/1.1 429 Too Many Requests
Retry-After: 60
```

## CSRF Protection

All POST/PUT/PATCH/DELETE requests require CSRF token:

**Headers**:
```
X-CSRF-TOKEN: {token}
```

Automatically handled by Inertia.js.

## Content Types

**Request**:
- `application/json` (default)
- `multipart/form-data` (file uploads)
- `application/x-www-form-urlencoded`

**Response**:
- `application/json` (always)

## Error Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created |
| 400 | Bad Request | Invalid request |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | Not authorized |
| 404 | Not Found | Resource not found |
| 422 | Validation Error | Invalid input |
| 429 | Too Many Requests | Rate limited |
| 500 | Server Error | Internal error |

## Example Requests

### Using cURL

**List routes**:
```bash
curl -X GET http://localhost:8000/spectra/routes \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=..."
```

**Get schema**:
```bash
curl -X GET "http://localhost:8000/spectra/schema/POST/api%2Fusers" \
  -H "Accept: application/json" \
  -H "Cookie: laravel_session=..."
```

**Execute request**:
```bash
curl -X POST http://localhost:8000/spectra/execute \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=..." \
  -H "X-CSRF-TOKEN: ..." \
  -d '{
    "method": "POST",
    "uri": "/api/users",
    "parameters": {
      "name": "John Doe",
      "email": "john@example.com"
    },
    "auth_mode": "current"
  }'
```

### Using JavaScript

**Execute request**:
```javascript
const response = await fetch('/spectra/execute', {
  method: 'POST',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({
    method: 'POST',
    uri: '/api/users',
    parameters: {
      name: 'John Doe',
      email: 'john@example.com'
    },
    auth_mode: 'current'
  })
});

const result = await response.json();
console.log(result.data);
```

### Using Inertia

**From React component**:
```typescript
import { router } from '@inertiajs/react';

router.post('/spectra/execute', {
  method: 'POST',
  uri: '/api/users',
  parameters: {
    name: 'John Doe',
    email: 'john@example.com'
  },
  auth_mode: 'current'
}, {
  onSuccess: (response) => {
    console.log(response.data);
  }
});
```

## Next Steps

- [Request Execution](../features/request-execution.md) - Detailed execution docs
- [Schema Generation](../features/schema-generation.md) - Schema details
- [Authentication](../authentication.md) - Auth modes
