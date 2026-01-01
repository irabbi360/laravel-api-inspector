# Laravel API Inspector - Auto-Generate API Documentation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/irabbi360/laravel-api-inspector.svg?style=flat-square)](https://packagist.org/packages/irabbi360/laravel-api-inspector)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/irabbi360/laravel-api-inspector/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/irabbi360/laravel-api-inspector/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/irabbi360/laravel-api-inspector/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/irabbi360/laravel-api-inspector/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/irabbi360/laravel-api-inspector.svg?style=flat-square)](https://packagist.org/packages/irabbi360/laravel-api-inspector)

**Laravel API Inspector** automatically generates API documentation from your Laravel routes, FormRequest validation rules, and API Resources. It's like Postman + Swagger combined, but deeply integrated with Laravel.

## Features

✨ **Auto-Parse FormRequest Rules** - Converts Laravel validation rules into comprehensive documentation  
📮 **Generate Postman Collections** - Create ready-to-use Postman collections with examples  
📖 **OpenAPI/Swagger Specs** - Export to OpenAPI 3.0 format for tools like Swagger UI and Redoc  
📄 **HTML Documentation** - Beautiful, auto-generated HTML documentation with examples  
🔍 **API Resource Detection** - Extract response structures from your API Resources  
💾 **Save Response Examples** - Automatically save JSON responses to files  
🔐 **Authentication Support** - Automatically detect protected routes and add auth headers  

## Installation

## Requirements

- **PHP:** 8.1 or higher
- **Laravel:** 10.0 or higher
- **Database:** MySQL 5.7+ / PostgreSQL 10+ / SQLite 3.8+


Get up and running in just two commands:

```bash
composer require irabbi360/laravel-api-inspector
```

## Run the interactive installer

```bash
php artisan api-inspector:install
```

## Quick Start


### View Documentation in Browser

After generating documentation, visit:

```
http://localhost:8000/api-docs
```

You'll see a beautiful HTML documentation page with all your API endpoints!

You can also access:
- **Postman Collection**: `http://localhost:8000/api-docs/postman` (download)
- **OpenAPI Spec**: `http://localhost:8000/api-docs/openapi` (download)

### Create a FormRequest with Validation Rules

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'age' => 'integer|min:18|max:100',
        ];
    }
}
```

### Use FormRequest in Your Controller

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;

class UserController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => User::create($request->validated()),
        ], 201);
    }
}
```

### Auto Response Schema generate. You can now add the annotation to your controller methods:

```php
<?php
    /**
     * Get user profile
     * @LAPIresponsesSchema ProfileResource
     */
    public function show(User $user)
    {
        return new ProfileResource($user);
    }
```

Or without the annotation, it will auto-detect from the return type:

```php
<?php
    public function show(User $user): ProfileResource
    {
        return new ProfileResource($user);
    }
```

- ✅ Parse @LAPIresponsesSchema ResourceName from docblocks
- ✅ Display response schema as JSON format
- ✅ Recursively handle nested resources
- ✅ Support unqualified resource names with auto-namespace resolution
- ✅ Prevent infinite recursion with depth limit

### 6. View in Browser

After generating, automatically view your documentation:

```
http://localhost:8000/api-docs
```

That's it! 🎉 Your API is now documented and accessible via browser.

## Generated Output Files

The command generates documentation in three formats:

- **HTML Docs** - `http://localhost:8000/api-docs` (view in browser)
- **Postman Collection** - `http://localhost:8000/api-docs/postman` (download for Postman)
- **OpenAPI Spec** - `http://localhost:8000/api-docs/openapi` (use with Swagger UI, etc.)

Files are also saved to `storage/api-docs/` directory for backup.

## Configuration

Edit `config/api-inspector.php`:

```php
return [
    'enabled' => true,

    'output' => [
        'openapi' => true,    // Generate OpenAPI spec
        'postman' => true,    // Generate Postman collection
        'html' => true,       // Generate HTML documentation
    ],

    'save_responses' => true,        // Save example responses to JSON

    'save_responses_driver' => 'json', // 'cache' or 'json' - Phase 2

    'middleware_capture' => true,    // Capture real responses - Phase 2

    'response_ttl' => 3600,          // Cache TTL in seconds (1 hour) - Phase 2

    'auth' => [
        'type' => 'bearer',
        'header' => 'Authorization'
    ],

    'response_path' => storage_path('api-docs'),
];
```

## Phase 2: Runtime Response Capture & Caching

### Middleware Response Capture

Automatically capture real API responses from your running endpoints:

```php
// The middleware automatically captures successful (2xx) JSON responses
// Configuration in config/api-inspector.php:

'middleware_capture' => true,  // Enable response capture
'save_responses_driver' => 'json', // Store in files or cache
'response_ttl' => 3600,       // Cache expiration time
```

**Features:**
- ✅ Captures real API responses automatically
- ✅ Only captures successful (2xx) responses
- ✅ Only targets API routes (configurable prefix)
- ✅ Saves responses to JSON files or Laravel cache
- ✅ Includes capture timestamp for freshness tracking
- ✅ Graceful error handling (doesn't break your API)

### Using ResponseCache Class

Programmatically manage cached responses:

```php
use Irabbi360\LaravelApiInspector\Support\ResponseCache;

class YourController extends Controller
{
    public function someAction(ResponseCache $responseCache)
    {
        // Store a response
        $responseCache->store('api/users/index', 200, [
            'data' => [/* ... */],
            'success' => true
        ]);

        // Retrieve a cached response
        $cached = $responseCache->get('api/users/index', 200);

        // Check if response is cached
        if ($responseCache->has('api/users/index', 200)) {
            // Use cached response
        }

        // Get all responses for a route
        $allResponses = $responseCache->getForRoute('api/users');

        // Clear responses for a route
        $responseCache->clearForRoute('api/users');

        // Clear all cached responses
        $responseCache->clearAll();
    }
}
```

### Storage Drivers

**JSON Driver** (recommended for development/production)
```php
'save_responses_driver' => 'json'
// Stores in storage/api-docs/cached-responses/
// Persists across cache flushes
// Better for team sharing
```

**Cache Driver** (recommended for performance)
```php
'save_responses_driver' => 'cache'
// Uses your configured cache backend (redis, memcached, etc.)
// Faster access
// Configurable TTL per response
```

### Example Workflow

1. **Enable middleware capture:**
```php
// config/api-inspector.php
'middleware_capture' => true,
```

2. **Make requests to your API:**
```bash
curl -X GET http://localhost:8000/api/users
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'
```

3. **Responses are automatically captured:**
```
storage/api-docs/cached-responses/
├── api_response:api_users:200.json
├── api_response:api_users:201.json
└── ...
```

4. **Use captured responses in documentation:**
- Postman collection now includes real example responses
- OpenAPI spec contains actual response schemas
- HTML docs show real data from your API

## Configuration

## How It Works

### Route Extraction
The package scans all your routes with the `api` middleware and extracts:
- HTTP method
- URI path
- Controller action
- Route middleware
- Authentication requirements

### Request Rule Parsing
From your FormRequest classes, it automatically:
- Extracts validation rules
- Converts rules to OpenAPI types (e.g., `email` → `string, format: email`)
- Generates example values
- Documents required vs optional fields

### Response Generation
The package infers response structures based on:
- Method name patterns (e.g., `index`, `show`, `store`, `update`, `delete`)
- API Resource definitions
- Controller return types

### Output Formats

#### Postman Collection
Import into Postman to:
- Test all endpoints with pre-filled request bodies
- Use environment variables for base URL and tokens
- Share with team members

#### OpenAPI Specification
Use with:
- Swagger UI for interactive documentation
- Redoc for beautiful documentation
- Code generation tools

#### HTML Documentation
A beautiful, responsive documentation site with:
- Request/response examples
- Parameter descriptions
- Authentication indicators
- Search and navigation

## Examples

### FormRequest to Documentation

**Your FormRequest:**
```php
'email' => 'required|email',
'age' => 'integer|min:18|max:100'
```

**Generated Documentation:**
```json
{
  "email": {
    "name": "email",
    "type": "string",
    "format": "email",
    "required": true,
    "example": "user@example.com",
    "description": "Email"
  },
  "age": {
    "name": "age",
    "type": "integer",
    "required": false,
    "min": 18,
    "max": 100,
    "example": 25,
    "description": "Age"
  }
}
```

## Validation Rules Mapping

| Laravel Rule | Generated Type | Format |
|---|---|---|
| `email` | string | email |
| `date` | string | date |
| `url` | string | uri |
| `numeric` | integer | - |
| `boolean` | boolean | - |
| `array` | array | - |
| `file` | string | binary |
| `image` | string | binary |
| `min:N` | - | minLength: N |
| `max:N` | - | maxLength: N |

## Testing

```bash
composer test          # Run all tests
composer test-coverage # With code coverage
composer analyse       # PHPStan static analysis
composer format        # Format code with Pint
```

## Roadmap

### Phase 1 (Completed ✅)
- ✅ Route scanning
- ✅ FormRequest rule extraction
- ✅ Postman collection generation
- ✅ OpenAPI specification generation
- ✅ HTML documentation generation
- ✅ JSON response saving

### Phase 2 (In Progress)
- ✅ API Resource parsing
- ✅ Runtime response capture middleware - Automatically capture real API responses
- ✅ Response caching - Cache responses with both file and cache driver support

### Phase 3 (Planned)
- 📋 Web UI dashboard (Telescope-like)
- 🔐 Advanced authentication testing
- 🪝 Webhook documentation support
- 📊 API analytics and monitoring

## Troubleshooting

### Routes not appearing
- Ensure routes have the `api` middleware
- Check that `API_INSPECTOR_ENABLED=true` in your `.env`

### Validation rules not extracted
- Make sure you're using FormRequest in the controller method
- Validate that the method name matches `rules()`

### Files not generating
- Check that `storage/api-docs` directory is writable
- Verify config settings in `config/api-inspector.php`

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Fazle Rabbi](https://github.com/irabbi360)
- [All Contributors](../../contributors)
