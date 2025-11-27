# Schema Generation

Spectra automatically generates JSON Schema (draft 2020-12) from Laravel FormRequest validation rules, enabling dynamic form generation and validation.

## Overview

The schema generation system:
- Converts Laravel validation rules to JSON Schema
- Supports comprehensive rule types
- Handles nested objects and arrays
- Generates example values
- Validates input against schemas

## JSON Schema

### What is JSON Schema?

JSON Schema is a vocabulary for validating and annotating JSON documents. Spectra uses [JSON Schema 2020-12](https://json-schema.org/draft/2020-12/json-schema-core.html).

### Schema Structure

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "properties": {
    "name": {
      "type": "string",
      "maxLength": 255
    },
    "email": {
      "type": "string",
      "format": "email"
    }
  },
  "required": ["name", "email"]
}
```

## How It Works

### 1. Rule Extraction

From a FormRequest:

```php
class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'age' => 'integer|min:18|max:120',
            'is_active' => 'boolean',
        ];
    }
}
```

### 2. Schema Generation

SchemaBuilder converts rules to schema:

```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
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
    },
    "is_active": {
      "type": "boolean"
    }
  },
  "required": ["name", "email"]
}
```

### 3. Form Generation

React generates form fields from schema:
- Text inputs for strings
- Number inputs for integers
- Checkboxes for booleans
- Selects for enums
- File inputs for files

## Supported Validation Rules

### Type Rules

| Laravel Rule | JSON Schema Type | Example |
|-------------|------------------|---------|
| `string` | `string` | `"John"` |
| `integer` | `integer` | `42` |
| `numeric` | `number` | `3.14` |
| `boolean` | `boolean` | `true` |
| `array` | `array` | `[1, 2, 3]` |
| `object` | `object` | `{"key": "value"}` |

**Example**:
```php
'name' => 'string'  // {"type": "string"}
'age' => 'integer'  // {"type": "integer"}
'price' => 'numeric' // {"type": "number"}
```

### Format Rules

| Laravel Rule | JSON Schema Format | Validation |
|-------------|-------------------|------------|
| `email` | `email` | Valid email address |
| `url` | `uri` | Valid URL |
| `date` | `date` | ISO 8601 date |
| `uuid` | `uuid` | Valid UUID |
| `ip` | `ipv4` or `ipv6` | Valid IP address |
| `json` | N/A | Valid JSON |

**Example**:
```php
'email' => 'email'  // {"type": "string", "format": "email"}
'website' => 'url'  // {"type": "string", "format": "uri"}
'created_at' => 'date' // {"type": "string", "format": "date"}
```

### Constraint Rules

| Laravel Rule | JSON Schema | Description |
|-------------|-------------|-------------|
| `min:n` | `minimum`, `minLength` | Minimum value/length |
| `max:n` | `maximum`, `maxLength` | Maximum value/length |
| `between:min,max` | `minimum` + `maximum` | Range constraint |
| `size:n` | `minLength` + `maxLength` | Exact size |
| `in:a,b,c` | `enum` | Allowed values |
| `regex:pattern` | `pattern` | Regular expression |

**Examples**:

```php
// Numeric constraints
'age' => 'integer|min:18|max:120'
// {"type": "integer", "minimum": 18, "maximum": 120}

// String length
'name' => 'string|min:3|max:50'
// {"type": "string", "minLength": 3, "maxLength": 50}

// Enum
'status' => 'in:active,inactive,pending'
// {"type": "string", "enum": ["active", "inactive", "pending"]}

// Pattern
'username' => 'regex:/^[a-z0-9_]+$/'
// {"type": "string", "pattern": "^[a-z0-9_]+$"}
```

### File Rules

| Laravel Rule | JSON Schema | Description |
|-------------|-------------|-------------|
| `file` | `string` + `contentMediaType` | Generic file |
| `image` | `string` + `contentMediaType` | Image file |
| `mimes:jpg,png` | `enum` of mime types | Allowed types |
| `mimetypes:image/*` | Pattern of mime types | Allowed patterns |

**Example**:
```php
'avatar' => 'image|mimes:jpg,png|max:2048'
// {
//   "type": "string",
//   "contentMediaType": "image/*",
//   "contentEncoding": "base64"
// }
```

### Modifier Rules

| Laravel Rule | Effect | JSON Schema |
|-------------|--------|-------------|
| `required` | Field must be present | Added to `required` array |
| `nullable` | Field can be null | `type: ["string", "null"]` |
| `sometimes` | Field is optional | Not in `required` |

**Examples**:

```php
// Required
'name' => 'required|string'
// In required array

// Nullable
'middle_name' => 'nullable|string'
// {"type": ["string", "null"]}

// Optional
'nickname' => 'sometimes|string'
// Not in required array
```

## Complex Schemas

### Nested Objects

```php
public function rules()
{
    return [
        'user.name' => 'required|string',
        'user.email' => 'required|email',
        'user.address.street' => 'string',
        'user.address.city' => 'string',
    ];
}
```

**Generated Schema**:
```json
{
  "type": "object",
  "properties": {
    "user": {
      "type": "object",
      "properties": {
        "name": {"type": "string"},
        "email": {"type": "string", "format": "email"},
        "address": {
          "type": "object",
          "properties": {
            "street": {"type": "string"},
            "city": {"type": "string"}
          }
        }
      },
      "required": ["name", "email"]
    }
  },
  "required": ["user"]
}
```

### Arrays

```php
public function rules()
{
    return [
        'tags' => 'array',
        'tags.*' => 'string|max:50',
    ];
}
```

**Generated Schema**:
```json
{
  "type": "object",
  "properties": {
    "tags": {
      "type": "array",
      "items": {
        "type": "string",
        "maxLength": 50
      }
    }
  }
}
```

### Array of Objects

```php
public function rules()
{
    return [
        'users' => 'array',
        'users.*.name' => 'required|string',
        'users.*.email' => 'required|email',
        'users.*.age' => 'integer|min:18',
    ];
}
```

**Generated Schema**:
```json
{
  "type": "object",
  "properties": {
    "users": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "name": {"type": "string"},
          "email": {"type": "string", "format": "email"},
          "age": {"type": "integer", "minimum": 18}
        },
        "required": ["name", "email"]
      }
    }
  }
}
```

## Example Value Generation

Spectra generates realistic example values using Faker:

```php
// Schema
'name' => 'string'          // "John Doe"
'email' => 'email'          // "john@example.com"
'age' => 'integer|min:18'   // 25
'price' => 'numeric'        // 99.99
'is_active' => 'boolean'    // true
'status' => 'in:active,inactive' // "active"
'created_at' => 'date'      // "2024-01-15"
'url' => 'url'              // "https://example.com"
'uuid' => 'uuid'            // "550e8400-e29b-41d4-a716-446655440000"
```

### Custom Examples

Override default examples:

```php
class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
        ];
    }
    
    public function examples()
    {
        return [
            'name' => 'Jane Smith',
        ];
    }
}
```

## Schema API

### Generate Schema

**Endpoint**: `GET /spectra/schema/{method}/{uri}`

**Example Request**:
```
GET /spectra/schema/POST/api/users
```

**Response**:
```json
{
  "schema": "https://json-schema.org/draft/2020-12/schema",
  "properties": {
    "name": {"type": "string", "maxLength": 255},
    "email": {"type": "string", "format": "email"}
  },
  "required": ["name", "email"]
}
```

### Using BuildSchemaAction

```php
$action = app(BuildSchemaAction::class);

$rules = [
    'name' => 'required|string|max:255',
    'email' => 'required|email',
];

$schema = $action->execute($rules);
```

## Form Generation

### Dynamic Forms

The RequestBuilder component generates forms from schemas:

1. **Text Inputs**: For string types
2. **Number Inputs**: For integer/numeric types
3. **Checkboxes**: For boolean types
4. **Select Dropdowns**: For enum types
5. **Textareas**: For long strings
6. **File Inputs**: For file types

### Validation

Client-side validation based on schema:
- Required fields
- Min/max constraints
- Format validation (email, URL, etc.)
- Pattern matching
- Type checking

### Error Display

Validation errors are displayed inline:
```jsx
<Input 
  error={errors.email}
  helperText="Must be a valid email address"
/>
```

## Advanced Usage

### Custom Rule Conversion

Extend SchemaBuilder to handle custom rules:

```php
class CustomSchemaBuilder extends SchemaBuilder
{
    protected function convertRule(string $rule): array
    {
        if ($rule === 'phone') {
            return [
                'type' => 'string',
                'pattern' => '^\+?[1-9]\d{1,14}$',
            ];
        }
        
        return parent::convertRule($rule);
    }
}
```

Bind in service provider:

```php
$this->app->bind(SchemaBuilder::class, CustomSchemaBuilder::class);
```

### Schema Caching

Cache schemas for performance:

```php
$cacheKey = "schema:{$method}:{$uri}";

$schema = Cache::remember($cacheKey, 3600, function () use ($rules) {
    return app(BuildSchemaAction::class)->execute($rules);
});
```

## Troubleshooting

### Schema Not Generated

**Check FormRequest**:
- Ensure `rules()` method exists
- Verify rules are valid
- Check controller type-hints FormRequest

### Invalid Schema

**Validate schema**:
```bash
# Use online validator
https://www.jsonschemavalidator.net/
```

**Check rule syntax**:
```php
// Invalid
'age' => 'min:18|max:120'  // Missing type

// Valid
'age' => 'integer|min:18|max:120'
```

### Missing Properties

**Check nested rules**:
```php
// Won't generate nested object
'user_name' => 'string'

// Generates nested object
'user.name' => 'string'
```

## Limitations

### Unsupported Rules

Some Laravel rules don't map directly to JSON Schema:
- `exists:table,column` - Database-specific
- `unique:table,column` - Database-specific
- `confirmed` - Relies on field naming
- `distinct` - Array-specific logic

### Custom Rules

Custom rule objects require manual schema definition.

### Conditional Rules

Rules with `when()`, `sometimes()`, or closures have limited support.

## Best Practices

1. **Use explicit types**: Always specify `string`, `integer`, etc.
2. **Define constraints**: Add `min`, `max`, `in` for better validation
3. **Document rules**: Use clear, descriptive rules
4. **Test schemas**: Verify generated schemas match expectations
5. **Cache schemas**: Cache in production for performance

## Next Steps

- [Request Builder](../ui/request-builder.md) - Using generated forms
- [Request Execution](request-execution.md) - Sending validated requests
- [Architecture](../architecture.md) - SchemaBuilder internals
