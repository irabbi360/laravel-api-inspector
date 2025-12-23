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

```bash
composer require irabbi360/laravel-api-inspector
```

Publish the config file:

```bash
php artisan vendor:publish --tag="api-inspector-config"
```

Publish the views file:

```bash
php artisan vendor:publish --tag="api-inspector-views"
```

## Quick Start

### 1. Generate Documentation

Run the command to generate all documentation formats:

```bash
php artisan api-inspector:generate
```

Or generate a specific format:

```bash
php artisan api-inspector:generate --format=postman
php artisan api-inspector:generate --format=openapi
php artisan api-inspector:generate --format=html
```

### 2. View Documentation in Browser

After generating documentation, visit:

```
http://localhost:8000/api/docs
```

You'll see a beautiful HTML documentation page with all your API endpoints!

You can also access:
- **Postman Collection**: `http://localhost:8000/api/docs/postman` (download)
- **OpenAPI Spec**: `http://localhost:8000/api/docs/openapi` (download)

### 3. Create a FormRequest with Validation Rules

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

### 4. Use FormRequest in Your Controller

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

### 5. View in Browser

After generating, automatically view your documentation:

```
http://localhost:8000/api/docs
```

That's it! 🎉 Your API is now documented and accessible via browser.

## Generated Output Files

The command generates documentation in three formats:

- **HTML Docs** - `http://localhost:8000/api/docs` (view in browser)
- **Postman Collection** - `http://localhost:8000/api/docs/postman` (download for Postman)
- **OpenAPI Spec** - `http://localhost:8000/api/docs/openapi` (use with Swagger UI, etc.)

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

    'middleware_capture' => true,    // Capture real responses (experimental)

    'auth' => [
        'type' => 'bearer',
        'header' => 'Authorization'
    ],

    'response_path' => storage_path('api-docs'),
];
```

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
- 🔄 API Resource parsing
- 🔄 Runtime response capture middleware
- 🔄 Response caching

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
