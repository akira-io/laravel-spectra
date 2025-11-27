# Collections

Collections allow you to save, organize, and share frequently used API requests in Spectra.

## Overview

Collections provide:
- Save requests for later use
- Organize requests by project or feature
- Export/import collections as JSON
- Share with team members
- Quick request loading

## Features

### 1. Save Requests

Save any configured request:
- Endpoint selection
- Parameters
- Headers
- Authentication mode
- Auth credentials

### 2. Organize Collections

Group related requests:
- User management endpoints
- Authentication flows
- Payment processing
- Admin operations

### 3. Export/Import

Share collections:
- Export as JSON file
- Import from JSON
- Share with team
- Version control friendly

### 4. Quick Access

Load saved requests:
- One-click loading
- Preserves all settings
- Updates request builder
- Ready to send

## Using Collections

### Saving a Request

1. Configure your request:
   - Select endpoint
   - Fill in parameters
   - Set authentication
   - Add headers

2. Click "Save" in Collections panel

3. Enter a name:
   ```
   Create User - Success Case
   ```

4. Click "Save"

### Loading a Request

1. Open Collections panel
2. Find your saved request
3. Click to load
4. Request builder populates
5. Click "Send Request"

### Updating a Request

1. Load the saved request
2. Make changes
3. Click "Save" again
4. Confirm update or save as new

### Deleting a Request

1. Find request in Collections
2. Click delete icon (trash)
3. Confirm deletion

## Collection Structure

### Request Data

```typescript
interface SavedRequest {
    id: string;              // Unique identifier
    name: string;            // Display name
    method: string;          // HTTP method
    uri: string;             // Endpoint URI
    parameters: object;      // Request parameters
    headers: object;         // Custom headers
    authMode: string;        // Authentication mode
    authValue: string | null; // Auth credentials
    createdAt: string;       // ISO timestamp
    updatedAt: string;       // ISO timestamp
}
```

### Example

```json
{
  "id": "550e8400-e29b-41d4-a716-446655440000",
  "name": "Create User - Success Case",
  "method": "POST",
  "uri": "/api/users",
  "parameters": {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret123"
  },
  "headers": {
    "Accept": "application/json"
  },
  "authMode": "bearer",
  "authValue": "your-token-here",
  "createdAt": "2024-01-15T10:30:00.000Z",
  "updatedAt": "2024-01-15T10:30:00.000Z"
}
```

## Storage

### LocalStorage

Collections are stored in browser localStorage:

```javascript
// Storage key
spectra_collections

// Data structure
{
  "collections": [
    {
      "id": "uuid",
      "name": "Request name",
      // ...
    }
  ]
}
```

### Persistence

- Survives browser refreshes
- Persists across sessions
- Per-domain storage
- Not synced across devices

### Limits

Browser localStorage limits:
- Typically 5-10 MB
- Varies by browser
- Stores as strings
- Shared with application

## Export/Import

### Exporting Collections

1. Click "Export" button
2. Browser downloads JSON file
3. File name: `spectra-collections-YYYY-MM-DD.json`

**Export Format**:
```json
{
  "version": "1.0",
  "exported_at": "2024-01-15T10:30:00.000Z",
  "collections": [
    {
      "id": "uuid",
      "name": "Request name",
      "method": "POST",
      "uri": "/api/users",
      "parameters": {...},
      "headers": {...},
      "authMode": "current",
      "authValue": null,
      "createdAt": "2024-01-15T10:00:00.000Z",
      "updatedAt": "2024-01-15T10:30:00.000Z"
    }
  ]
}
```

### Importing Collections

1. Click "Import" button
2. Select JSON file
3. Choose import mode:
   - **Merge**: Add to existing collections
   - **Replace**: Replace all collections
4. Click "Import"

**Import Validation**:
- Validates JSON structure
- Checks required fields
- Generates new IDs if needed
- Preserves timestamps

### Sharing Collections

**With Team Members**:
1. Export your collections
2. Share JSON file
3. Team members import
4. Collections available locally

**Version Control**:
```bash
# Add to repository
git add spectra-collections.json
git commit -m "Add API test collections"
git push
```

**Documentation**:
Include collections in project docs:
```
docs/
  api-testing/
    spectra-collections.json
```

## Organization Strategies

### By Feature

```
User Management/
  - Create User
  - Update User
  - Delete User
  - List Users

Authentication/
  - Login
  - Logout
  - Refresh Token
  - Forgot Password
```

### By Test Case

```
Happy Path/
  - Create User - Success
  - Update User - Success

Error Cases/
  - Create User - Invalid Email
  - Create User - Duplicate Email
  - Update User - Not Found
```

### By Environment

```
Development/
  - Create User (Dev Token)
  - List Users (Dev Auth)

Staging/
  - Create User (Staging Token)
  - List Users (Staging Auth)
```

## Best Practices

### 1. Descriptive Names

**Good**:
- `Create User - Valid Data`
- `Login - Invalid Password`
- `Update Post - As Author`

**Bad**:
- `Test 1`
- `Request`
- `Endpoint`

### 2. Organize by Purpose

Group related requests together.

### 3. Include Test Scenarios

Save both success and error cases:
- Valid input
- Invalid input
- Edge cases
- Error scenarios

### 4. Document Auth Requirements

Include auth mode in name:
- `Create User (Admin Token)`
- `List Posts (Guest)`
- `Delete Comment (Author)`

### 5. Regular Cleanup

Remove outdated requests:
- Deprecated endpoints
- Old authentication tokens
- Obsolete test cases

### 6. Version Control

Commit collections to repository:
- Track changes
- Share with team
- Backup collections

### 7. Sanitize Sensitive Data

Before sharing:
- Remove real tokens
- Use example credentials
- Mask sensitive parameters

## Advanced Usage

### Collection Templates

Create templates for common patterns:

```json
{
  "name": "CRUD Template - Create",
  "method": "POST",
  "uri": "/api/resource",
  "parameters": {
    "name": "Example",
    "description": "Template description"
  },
  "authMode": "current"
}
```

### Bulk Operations

Export, modify, and re-import:

```bash
# Export collections
# Edit JSON file
# Find and replace patterns
sed -i 's/old-url/new-url/g' spectra-collections.json
# Re-import
```

### Collection Validation

Validate before importing:

```javascript
function validateCollection(collection) {
    const required = ['id', 'name', 'method', 'uri'];
    return required.every(field => collection[field]);
}
```

### Automated Collection Generation

Generate collections from tests:

```php
// In tests
public function test_create_user()
{
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];
    
    // Save as Spectra collection
    $this->saveToSpectraCollection('Create User', 'POST', '/api/users', $data);
}
```

## Troubleshooting

### Collections Not Saving

**Check localStorage**:
```javascript
// In browser console
localStorage.getItem('spectra_collections')
```

**Clear and retry**:
```javascript
localStorage.removeItem('spectra_collections')
```

**Check browser settings**:
- Ensure localStorage is enabled
- Check storage quota
- Disable private/incognito mode

### Import Fails

**Validate JSON**:
```bash
# Use online validator
https://jsonlint.com/

# Or jq command
jq . spectra-collections.json
```

**Check format**:
- Ensure valid JSON
- Verify required fields
- Check data types

**Review errors**:
- Import provides error messages
- Fix issues and retry

### Collections Disappeared

**Browser data cleared**:
- localStorage cleared
- Browser cache cleared
- Privacy settings

**Solution**:
- Import from backup
- Restore from version control
- Recreate from documentation

## Security Considerations

### Sensitive Data

Collections may contain:
- API tokens
- User credentials
- Internal URLs
- Test data

### Best Practices

1. **Don't commit sensitive data**:
   ```bash
   # .gitignore
   spectra-collections-local.json
   ```

2. **Use placeholders**:
   ```json
   {
     "authValue": "YOUR_TOKEN_HERE"
   }
   ```

3. **Separate environments**:
   - Development collections
   - Staging collections
   - Never production

4. **Regular audits**:
   - Review collections
   - Remove sensitive data
   - Update credentials

## API Integration

### Programmatic Access

```javascript
// Get collections
const collections = JSON.parse(
    localStorage.getItem('spectra_collections') || '[]'
);

// Add collection
collections.push(newCollection);
localStorage.setItem('spectra_collections', JSON.stringify(collections));

// Update collection
const index = collections.findIndex(c => c.id === id);
collections[index] = updatedCollection;
localStorage.setItem('spectra_collections', JSON.stringify(collections));

// Delete collection
const filtered = collections.filter(c => c.id !== id);
localStorage.setItem('spectra_collections', JSON.stringify(filtered));
```

### Custom Storage

Implement custom storage backend:

```typescript
interface StorageAdapter {
    get(): Promise<Collection[]>;
    set(collections: Collection[]): Promise<void>;
    export(): Promise<Blob>;
    import(file: File): Promise<Collection[]>;
}

class DatabaseStorageAdapter implements StorageAdapter {
    async get(): Promise<Collection[]> {
        const response = await fetch('/api/collections');
        return response.json();
    }
    
    async set(collections: Collection[]): Promise<void> {
        await fetch('/api/collections', {
            method: 'POST',
            body: JSON.stringify(collections),
        });
    }
}
```

## Next Steps

- [Request Builder](../ui/request-builder.md) - Building requests
- [Request Execution](request-execution.md) - Executing saved requests
- [Quick Start](../quick-start.md) - Getting started guide
