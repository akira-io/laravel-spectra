# Troubleshooting

Common issues and solutions when using Laravel Spectra.

## Installation Issues

### Package Not Found

**Error**: `Package akira/laravel-spectra not found`

**Solutions**:
1. Check package name spelling
2. Verify Composer repository access
3. Clear Composer cache:
   ```bash
   composer clear-cache
   composer require --dev akira/laravel-spectra
   ```

### Service Provider Not Registered

**Error**: `Class 'Akira\Spectra\SpectraServiceProvider' not found`

**Solutions**:
1. Run package discovery:
   ```bash
   composer dump-autoload
   php artisan package:discover
   ```
2. Clear config cache:
   ```bash
   php artisan config:clear
   ```

## Access Issues

### Cannot Access /spectra

**Error**: 404 Not Found

**Solutions**:
1. Verify Spectra is enabled:
   ```env
   SPECTRA_ENABLED=true
   ```

2. Check environment:
   ```env
   SPECTRA_ONLY_LOCAL=true
   APP_ENV=local
   ```

3. Clear route cache:
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

4. Verify route exists:
   ```bash
   php artisan route:list | grep spectra
   ```

### 403 Forbidden

**Error**: This action is unauthorized

**Solutions**:
1. Check authentication:
   ```php
   auth()->check()  // Should return true
   ```

2. Verify gate definition:
   ```php
   Gate::define('use-spectra', function ($user) {
       return $user->hasRole('developer');
   });
   ```

3. Test gate:
   ```bash
   php artisan tinker
   >>> Gate::allows('use-spectra')
   ```

4. Check user permissions:
   ```php
   $user->hasRole('developer')
   ```

## Route Discovery Issues

### Routes Not Appearing

**Problem**: Endpoints not showing in tree

**Solutions**:
1. Clear route cache:
   ```bash
   php artisan route:clear
   ```

2. Verify routes exist:
   ```bash
   php artisan route:list
   ```

3. Check route filters in Spectra
4. Refresh browser

### Wrong Route Count

**Problem**: Missing some routes

**Solutions**:
1. Check route exclusions
2. Verify route registration
3. Clear all caches:
   ```bash
   php artisan optimize:clear
   ```

## Schema Generation Issues

### Schema Not Generated

**Problem**: No schema for endpoint

**Solutions**:
1. Verify FormRequest exists:
   ```php
   public function store(StoreUserRequest $request)
   ```

2. Check rules method:
   ```php
   public function rules()
   {
       return ['name' => 'required|string'];
   }
   ```

3. Clear compiled views:
   ```bash
   php artisan view:clear
   ```

### Invalid Schema

**Problem**: Schema doesn't match validation

**Solutions**:
1. Verify rule syntax:
   ```php
   // Invalid
   'age' => 'min:18'  // Missing type
   
   // Valid
   'age' => 'integer|min:18'
   ```

2. Check for custom rules
3. Review rule mappings

## Request Execution Issues

### 500 Server Error

**Problem**: Request fails with 500 error

**Solutions**:
1. Check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. Enable debug mode:
   ```env
   APP_DEBUG=true
   ```

3. Verify endpoint works outside Spectra
4. Check middleware requirements

### 422 Validation Error

**Problem**: Validation failing unexpectedly

**Solutions**:
1. Review validation rules
2. Check parameter types
3. Verify required fields
4. Test with Postman/curl

### Authentication Fails

**Problem**: 401 Unauthorized

**Solutions**:
1. Verify auth mode selection
2. Check credentials
3. Test authentication:
   ```bash
   php artisan tinker
   >>> auth()->check()
   >>> auth()->user()
   ```

4. Check guard configuration:
   ```env
   SPECTRA_GUARD=web
   ```

## Performance Issues

### Slow Page Load

**Problem**: Spectra loads slowly

**Solutions**:
1. Cache routes:
   ```bash
   php artisan route:cache
   ```

2. Cache configuration:
   ```bash
   php artisan config:cache
   ```

3. Enable OPcache in PHP
4. Optimize autoloader:
   ```bash
   composer dump-autoload --optimize
   ```

### Slow Request Execution

**Problem**: Requests take long time

**Solutions**:
1. Profile with Laravel Debugbar
2. Check database queries
3. Add database indexes
4. Use eager loading
5. Cache API responses

## UI Issues

### Dark Mode Not Working

**Problem**: Theme toggle not working

**Solutions**:
1. Clear browser cache
2. Check localStorage
3. Try different browser
4. Reset preferences

### Search Not Working

**Problem**: Endpoint search not finding routes

**Solutions**:
1. Check search input
2. Verify routes are loaded
3. Clear browser cache
4. Check JavaScript console for errors

### Request Builder Not Updating

**Problem**: Form not populating

**Solutions**:
1. Refresh page
2. Clear browser cache
3. Check JavaScript console
4. Verify schema generation

## Cookie Inspector Issues

### Cookies Not Showing

**Problem**: Cookie list is empty

**Solutions**:
1. Verify cookies exist in browser
2. Check cookie domain
3. Click "Refresh Cookies"
4. Check browser cookie settings

### Decryption Fails

**Problem**: Cannot decrypt cookies

**Solutions**:
1. Verify APP_KEY:
   ```bash
   php artisan key:generate
   ```

2. Check encryption configuration:
   ```php
   // config/session.php
   'encrypt' => true,
   ```

3. Verify cookie is encrypted

## Collections Issues

### Collections Not Saving

**Problem**: Collections not persisting

**Solutions**:
1. Check browser localStorage:
   ```javascript
   localStorage.getItem('spectra_collections')
   ```

2. Verify localStorage is enabled
3. Check storage quota
4. Try different browser

### Import Fails

**Problem**: Cannot import collections

**Solutions**:
1. Validate JSON:
   ```bash
   jq . spectra-collections.json
   ```

2. Check file format
3. Verify required fields
4. Try smaller file

## Build Issues

### Frontend Build Fails

**Problem**: npm run build errors

**Solutions**:
1. Clear node_modules:
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   ```

2. Check Node version:
   ```bash
   node --version  # Should be 18+
   ```

3. Clear build cache:
   ```bash
   rm -rf build/
   npm run build
   ```

### TypeScript Errors

**Problem**: Type checking fails

**Solutions**:
1. Update types:
   ```bash
   npm install --save-dev @types/react @types/node
   ```

2. Check tsconfig.json
3. Clear TypeScript cache

## Common Error Messages

### "CSRF token mismatch"

**Solutions**:
1. Refresh page
2. Clear cookies
3. Check CSRF middleware
4. Verify session configuration

### "Too Many Attempts"

**Solutions**:
1. Wait for rate limit reset
2. Increase rate limits (development only):
   ```php
   'rate_limit' => ['max' => 120, 'per_minutes' => 1]
   ```

### "Method Not Allowed"

**Solutions**:
1. Verify HTTP method
2. Check route definition
3. Clear route cache

### "Class not found"

**Solutions**:
1. Run autoload:
   ```bash
   composer dump-autoload
   ```

2. Check class namespace
3. Verify file exists

## Debugging Tips

### Enable Debug Mode

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Check Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# PHP error logs
tail -f /var/log/php/error.log

# Web server logs
tail -f /var/log/nginx/error.log
```

### Use Tinker

```bash
php artisan tinker

>>> app()->environment()
>>> config('spectra')
>>> Gate::allows('use-spectra')
>>> auth()->user()
```

### Browser DevTools

1. Open DevTools (F12)
2. Check Console for errors
3. Check Network tab for failed requests
4. Check Application → localStorage

### Clear All Caches

```bash
php artisan optimize:clear
composer dump-autoload
npm run build
```

## Getting Help

If issues persist:

1. Check [GitHub Issues](https://github.com/akira-io/laravel-spectra/issues)
2. Search existing issues
3. Create new issue with:
   - Laravel version
   - PHP version
   - Error messages
   - Steps to reproduce
   - Expected vs actual behavior

## Next Steps

- [Configuration](../configuration.md) - Review configuration
- [Security](../security.md) - Security considerations
- [Architecture](../architecture.md) - Understand internals
