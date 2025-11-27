# Quick Start

Get up and running with Spectra in just a few minutes.

## Prerequisites

Ensure you have:
- [Installed Spectra](installation.md)
- Configured the `use-spectra` gate
- Authenticated user with proper permissions

## Accessing Spectra

1. Start your Laravel development server:
```bash
php artisan serve
```

2. Log in to your application with a user that has the `use-spectra` permission

3. Navigate to `/spectra` in your browser:
```
http://localhost:8000/spectra
```

## Interface Overview

The Spectra interface consists of several panels:

- **Left Panel**: Endpoint Tree - Browse all your routes
- **Center Panel**: Request Builder - Configure and send requests
- **Right Panel**: Collections - Save and manage request collections
- **Bottom Panel**: Response Viewer - View response data

## Your First Request

### 1. Select an Endpoint

In the **Endpoint Tree** (left panel):
- Browse or search for an endpoint (press `Cmd/Ctrl + K` for quick search)
- Click on an endpoint to select it
- The request builder will populate with the endpoint details

### 2. Configure the Request

In the **Request Builder** (center panel):

**Method & URL**: Automatically populated from the selected endpoint

**Parameters**: Fill in any required parameters
- Path parameters (e.g., `{id}`)
- Query parameters
- Body parameters (for POST/PUT/PATCH)

**Headers**: Add custom headers if needed
- Click "Add Header"
- Enter key and value
- Headers like `Content-Type` are added automatically

### 3. Choose Authentication

In the **Auth Panel** (top of request builder):

Select one of the authentication modes:

- **Current User**: Use your current session (default)
- **Impersonate**: Execute as a different user
- **Bearer Token**: Provide an API token
- **Basic Auth**: Use username/password

Example using Bearer token:
```
Bearer your-api-token-here
```

### 4. Send the Request

Click the **"Send Request"** button (or press `Cmd/Ctrl + Enter`)

### 5. View the Response

The **Response Viewer** displays:
- **JSON Tab**: Formatted JSON response
- **Raw Tab**: Raw response text
- **Headers Tab**: Response headers
- **Cookies Tab**: Response cookies

## Common Tasks

### Browsing Endpoints

**Search for endpoints:**
1. Press `Cmd/Ctrl + K` or click the search icon
2. Type your search query
3. Press Enter to select

**Filter by method:**
- GET endpoints are shown in blue
- POST endpoints are shown in green
- PUT/PATCH endpoints are shown in yellow
- DELETE endpoints are shown in red

### Working with Parameters

**Path Parameters:**
```
/api/users/{id}
```
- Fill in the `id` field with a value like `1` or `123`

**Query Parameters:**
```
/api/users?page=1&per_page=10
```
- Add query parameters in the Query section
- Each parameter gets its own field

**Body Parameters:**
For POST/PUT/PATCH requests:
- JSON body is automatically generated from your FormRequest schema
- Edit the JSON directly or use the form fields

### Saving Requests

**Save a request to collections:**
1. Configure your request
2. Click "Save" in the Collections panel
3. Enter a name
4. Click "Save"

**Load a saved request:**
1. Open the Collections panel
2. Click on a saved request
3. The request builder will populate automatically

### Inspecting Cookies

**View Laravel cookies:**
1. Open the Cookie panel (right side)
2. Click "Refresh Cookies"
3. View decrypted cookie values

## Example Workflow

Let's create and test a user creation endpoint:

### 1. Find the Endpoint
```
POST /api/users
```

### 2. Fill in Parameters
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

### 3. Choose Authentication
Select "Current User" to use your session, or "Bearer Token" for API authentication.

### 4. Send Request
Click "Send Request"

### 5. View Response
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

### 6. Save for Later
Click "Save" and name it "Create User - Success Case"

## Keyboard Shortcuts

- `Cmd/Ctrl + K`: Quick search endpoints
- `Cmd/Ctrl + Enter`: Send request
- `Cmd/Ctrl + S`: Save request to collection
- `/`: Focus search box
- `Esc`: Close modals/dialogs

## Tips & Tricks

### 1. Use Schema Validation
Spectra automatically validates your input against FormRequest rules. Invalid fields are highlighted.

### 2. Copy Responses
Click the copy icon in the response viewer to copy JSON to clipboard.

### 3. Dark Mode
Toggle dark mode with the moon/sun icon in the top right.

### 4. Export Collections
Share collections with your team:
1. Click "Export" in Collections
2. Save the JSON file
3. Team members can import it

### 5. View Request History
Your last request is saved automatically. Reload the page to continue where you left off.

## Common Scenarios

### Testing Authentication

**Test a protected endpoint:**
1. Select the endpoint
2. Choose "Bearer Token" auth
3. Enter a valid token
4. Send request

**Test as different users:**
1. Choose "Impersonate" auth
2. Enter the user ID or email
3. Send request

### Working with File Uploads

For endpoints that accept files:
1. Use the file input in the request builder
2. Select a file from your system
3. Spectra will include it in the multipart request

### Testing Validation

**Test validation errors:**
1. Send a request with invalid data
2. View validation errors in the response
3. Fix the data and retry

## Troubleshooting

**Can't access `/spectra`:**
- Ensure you're authenticated
- Check the `use-spectra` gate definition
- Verify `SPECTRA_ENABLED=true` in `.env`

**Endpoints not showing:**
- Clear route cache: `php artisan route:clear`
- Refresh the page

**Request fails:**
- Check authentication mode
- Verify parameter values
- Review response for error messages

## Next Steps

- [Authentication](authentication.md) - Learn about auth modes
- [Schema Generation](features/schema-generation.md) - Understand JSON Schema
- [Collections](features/collections.md) - Master request collections
- [Security](security.md) - Review security features
