# Contributing to Laravel Spectra

Thank you for considering contributing to Laravel Spectra! This document outlines the process and guidelines.

## Code of Conduct

Be respectful, inclusive, and professional. We're all here to build great software together.

## How to Contribute

### Reporting Bugs

1. **Check existing issues** - Someone may have already reported it
2. **Create a new issue** with:
   - Clear, descriptive title
   - Laravel version
   - PHP version
   - Steps to reproduce
   - Expected vs actual behavior
   - Error messages or screenshots

### Suggesting Features

1. **Check existing issues** - Feature may already be planned
2. **Create a feature request** with:
   - Clear description of the feature
   - Use cases and benefits
   - Potential implementation approach
   - Examples from other tools (if applicable)

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch**:
   ```bash
   git checkout -b feature/my-new-feature
   ```

3. **Make your changes**:
   - Write clear, focused commits
   - Follow coding standards
   - Add tests for new features
   - Update documentation

4. **Test your changes**:
   ```bash
   composer test
   composer analyse
   composer format
   npm run build
   ```

5. **Commit using conventional commits**:
   ```bash
   git commit -m "feat: add support for custom rule mapping"
   git commit -m "fix: resolve schema generation for nested arrays"
   git commit -m "docs: update authentication guide"
   ```

6. **Push and create PR**:
   ```bash
   git push origin feature/my-new-feature
   ```

## Development Setup

### Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+
- npm

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/akira-io/laravel-spectra.git
   cd laravel-spectra
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Run tests**:
   ```bash
   composer test
   ```

4. **Build frontend**:
   ```bash
   npm run build
   ```

## Coding Standards

### PHP

We follow **Laravel coding standards** using **Laravel Pint**:

```bash
# Check formatting
composer format

# Fix automatically
vendor/bin/pint
```

**Key standards**:
- PSR-12 compliance
- Type hints for all parameters and return types
- Strict types declaration
- PHPDoc for complex methods
- Readonly properties where possible

**Example**:
```php
<?php

declare(strict_types=1);

namespace Akira\Spectra\Services;

use Illuminate\Support\Collection;

final class RouteScanner
{
    public function __construct(
        private readonly Router $router,
    ) {}

    public function scanRoutes(): Collection
    {
        // Implementation
    }
}
```

### TypeScript/React

We follow **standard TypeScript conventions**:

```bash
# Type check
npm run type-check

# Build
npm run build
```

**Key standards**:
- Strict TypeScript mode
- Functional components with hooks
- Props interfaces for components
- Descriptive variable names
- Comments for complex logic

**Example**:
```typescript
interface RequestBuilderProps {
    endpoint: Endpoint;
    onSend: (command: ExecuteCommand) => void;
}

export function RequestBuilder({ endpoint, onSend }: RequestBuilderProps) {
    const [parameters, setParameters] = useState({});
    
    // Component implementation
}
```

## Testing

### PHP Tests

We use **Pest** for testing:

```bash
# Run all tests
composer test

# Run specific test
vendor/bin/pest tests/Feature/RouteDiscoveryTest.php

# Run with coverage
composer test-coverage
```

**Test structure**:
```php
it('discovers all routes', function () {
    $scanner = app(RouteScanner::class);
    $routes = $scanner->scanRoutes();
    
    expect($routes)->toBeCollection()
        ->and($routes)->not->toBeEmpty();
});
```

### Writing Tests

1. **Feature tests** for user-facing functionality
2. **Unit tests** for services and utilities
3. **Use descriptive test names**
4. **Test both success and failure cases**
5. **Mock external dependencies**

### Static Analysis

We use **Larastan** (PHPStan for Laravel):

```bash
composer analyse
```

Fix any issues before submitting PR.

## Commit Messages

We use **Conventional Commits**:

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks
- `ci`: CI/CD changes

### Examples

```bash
feat(schema): add support for array validation rules

Implement array rule conversion to JSON Schema with support for
nested arrays and array of objects.

Closes #123

fix(auth): resolve impersonation gate check

The gate check was not properly authorizing impersonation requests.
This fix ensures the gate is checked before allowing impersonation.

Fixes #456

docs(readme): update installation instructions

Added steps for publishing config and clearing caches.
```

### Scope

Use relevant scope:
- `schema`: Schema generation
- `routes`: Route discovery
- `auth`: Authentication
- `ui`: User interface
- `api`: API endpoints
- `config`: Configuration
- `docs`: Documentation

## Documentation

### Update Documentation

When adding features or changing behavior:

1. **Update relevant docs** in `/docs`
2. **Update README.md** if needed
3. **Add JSDoc/PHPDoc** comments
4. **Update CHANGELOG.md**

### Documentation Style

- Clear, concise language
- Code examples for features
- Screenshots for UI changes
- Link to related documentation

## Pull Request Process

1. **Ensure all tests pass**
2. **Update documentation**
3. **Add entry to CHANGELOG.md**
4. **Request review from maintainers**
5. **Address review feedback**
6. **Wait for approval and merge**

### PR Checklist

- [ ] Tests added/updated
- [ ] Documentation updated
- [ ] Code formatted (Pint)
- [ ] Static analysis passes
- [ ] Conventional commit messages
- [ ] CHANGELOG.md updated
- [ ] No breaking changes (or documented)

## Release Process

Maintainers handle releases:

1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Create git tag
4. Push to Packagist
5. Create GitHub release

We follow **Semantic Versioning**:
- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes

## Questions?

- Open a [Discussion](https://github.com/akira-io/laravel-spectra/discussions)
- Join our [Discord](#) (if available)
- Email: support@akira-io.com

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

Thank you for contributing! 🎉
