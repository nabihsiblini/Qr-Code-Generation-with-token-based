# QR Code Generation API

A secure Laravel-based REST API for generating QR codes with token-based authentication using Laravel Sanctum.

## 📦 Project Package Contents
This is a complete Laravel project packaged as a zip file. All source code is included and ready for deployment.

## 🔐 Security Features
- **Token-only authentication** - No session/cookie authentication
- **No CSRF tokens required** for API endpoints
- **Bearer token validation** on all protected routes
- **No caching** of sensitive responses
- **Proper error handling** with JSON responses

## Table of Contents
- [Requirements](#requirements)
- [API Endpoints](#api-endpoints)
- [Authentication Guide](#authentication-guide)
- [Testing with Swagger](#testing-with-swagger)
- [Production Deployment](#production-deployment)
- [Troubleshooting](#troubleshooting)
- [Security Best Practices](#security-best-practices)

## Requirements

### Production Server Requirements
- PHP >= 8.2
- Composer
- MySQL 5.7+ or PostgreSQL 9.6+
- Nginx or Apache web server
- SSL Certificate (required for production)
- Required PHP Extensions:
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML
  - Ctype
  - JSON
  - BCMath
  - GD or ImageMagick (for QR code generation)
  - fileinfo
  - curl

## API Endpoints

### Public Endpoints (No Authentication Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register a new user |
| POST | `/api/login` | Login and get token |

### Protected Endpoints (Bearer Token Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/user` | Get current user info |
| POST | `/api/logout` | Logout and invalidate token |
| GET | `/api/generate-qr` | Generate QR code |

### Swagger Documentation

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/documentation` | Swagger UI interface |
| GET | `/docs` | Raw OpenAPI specification |

## Authentication Guide

### 1. Register a New User
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "securepassword123"
  }'
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-01-01T10:00:00.000000Z"
  },
  "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ1234567890"
}
```

### 2. Login Existing User
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "securepassword123"
  }'
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "token": "2|zYxWvUtSrQpOnMlKjIhGfEdCbA0987654321"
}
```

### 3. Using the Token
Include the token in the Authorization header for all protected endpoints:
```bash
Authorization: Bearer YOUR_TOKEN_HERE
```

### 4. Generate QR Code
```bash
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
     -H "Accept: image/png" \
     "http://127.0.0.1:8000/api/generate-qr?url=https://circle.cma.gov.ae/DocumentVerification?docId=00008331-NG-vZox&size=100" \
     --output qrcode.png
```

**Parameters:**
- `url` (required): The URL to encode in the QR code
- `size` (optional): Size in pixels (50-1000, default: 300)

### 5. Logout
```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

This will invalidate the token immediately.

## Testing with Swagger

### Access Swagger UI
1. Open browser and navigate to: `http://127.0.0.1:8000/api/documentation`

### Authenticate in Swagger
1. Click the **"Authorize"** button at the top of the page
2. Enter your token in the format: `Bearer YOUR_TOKEN_HERE`
3. Click **"Authorize"**
4. Click **"Close"**

### Important Swagger Notes
- **Swagger UI may cache tokens** - After logout, do a hard refresh (Ctrl+Shift+R) or close the tab
- **Always verify authentication** - Check the lock icon on endpoints
- **Token format** - Must include "Bearer " prefix

### Test Endpoints
1. Expand any endpoint
2. Click **"Try it out"**
3. Fill in required parameters
4. Click **"Execute"**
5. View the response

## Error Responses

All API errors return consistent JSON responses:

### Authentication Error (401)
```json
{
  "success": false,
  "message": "Unauthenticated. Please provide a valid bearer token.",
  "error": "Invalid or missing authentication token"
}
```

### Validation Error (422)
```json
{
  "success": false,
  "message": "Invalid URL"
}
```

### Method Not Allowed (405)
```json
{
  "success": false,
  "message": "The POST method is not supported for route api/generate-qr. Supported methods: GET, HEAD, OPTIONS.",
  "error": "Method not allowed"
}
```

## Example Usage in Different Languages

### JavaScript (Fetch API)
```javascript
// Login
const loginResponse = await fetch('http://127.0.0.1:8000/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    email: 'john@example.com',
    password: 'securepassword123'
  })
});
const { token } = await loginResponse.json();

// Generate QR Code
const qrResponse = await fetch('http://127.0.0.1:8000/api/generate-qr?url=https://circle.cma.gov.ae/DocumentVerification?docId=00008331-NG-vZox&size=100', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'image/png'
  }
});
const blob = await qrResponse.blob();
const imageUrl = URL.createObjectURL(blob);
```

### Python
```python
import requests

# Login
login_response = requests.post('http://127.0.0.1:8000/api/login', 
    json={
        'email': 'john@example.com',
        'password': 'securepassword123'
    })
token = login_response.json()['token']

# Generate QR Code
headers = {'Authorization': f'Bearer {token}'}
params = {'url': 'https://circle.cma.gov.ae/DocumentVerification?docId=00008331-NG-vZox', 'size': 100}
qr_response = requests.get('http://127.0.0.1:8000/api/generate-qr', 
    headers=headers, 
    params=params)

# Save QR code image
with open('qrcode.png', 'wb') as f:
    f.write(qr_response.content)
```

### PHP (cURL)
```php
// Login
$ch = curl_init('http://127.0.0.1:8000/api/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'john@example.com',
    'password' => 'securepassword123'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
$response = curl_exec($ch);
$data = json_decode($response, true);
$token = $data['token'];
curl_close($ch);

// Generate QR Code
$ch = curl_init('http://127.0.0.1:8000/api/generate-qr?url=https://circle.cma.gov.ae/DocumentVerification?docId=00008331-NG-vZox&size=100');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: image/png'
]);
$qrCode = curl_exec($ch);
file_put_contents('qrcode.png', $qrCode);
curl_close($ch);
```

### Postman Collection
1. Create a new request
2. Set method to GET
3. URL: `http://127.0.0.1:8000/api/generate-qr?url=https://circle.cma.gov.ae/DocumentVerification?docId=00008331-NG-vZox`
4. Headers:
   - Authorization: `Bearer YOUR_TOKEN`
   - Accept: `image/png`
5. Send request

## Production Deployment

### Option 1: Docker Deployment (Recommended)

#### Prerequisites
- Docker Engine 20.10+
- Docker Compose 2.0+
- 2GB RAM minimum
- 10GB disk space

#### Quick Start with Docker

1. **Clone or upload the project**
```bash
cd /path/to/your/project
```

2. **Create environment file**
```bash
cp .env.example .env
# Edit .env with your production values
```

3. **Build and start containers**
```bash
docker-compose up -d --build
```

4. **Run initial setup**
```bash
# Generate application key
docker-compose exec app php artisan key:generate

# Run database migrations
docker-compose exec app php artisan migrate --force

# Generate Swagger documentation
docker-compose exec app php artisan l5-swagger:generate

# Set proper permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

5. **Access the application**
- API: `http://localhost` (or your configured APP_PORT)
- phpMyAdmin: `http://localhost:8080` (or your configured PMA_PORT)
- Swagger Documentation: `http://localhost/api/documentation`

#### Docker Services

The Docker stack includes:
- **app**: Laravel application with PHP-FPM and Nginx
- **mysql**: MySQL 8.0 database
- **redis**: Redis cache server
- **phpmyadmin**: Database management interface

#### Docker Environment Variables

Create a `.env` file with these Docker-specific variables:

```env
# Docker ports
APP_PORT=80
DB_PORT=3306
REDIS_PORT=6379
PMA_PORT=8080

# Database settings for Docker
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=qrcode_db
DB_USERNAME=qrcode_user
DB_PASSWORD=your_secure_password
DB_ROOT_PASSWORD=your_root_password

# Redis settings for Docker
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

#### Docker Management Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Execute commands in container
docker-compose exec app php artisan cache:clear

# Rebuild containers
docker-compose up -d --build

# Remove everything including volumes
docker-compose down -v
```

### Option 2: Traditional Server Deployment

#### Server Requirements
- PHP 8.2+ with required extensions
- Composer
- MySQL 5.7+ or PostgreSQL 9.6+
- Nginx or Apache web server

#### Deployment Steps

#### 1. Upload Files
Upload all project files to your server, typically to `/var/www/qrcode-api`

#### 2. Install Dependencies
```bash
cd /var/www/qrcode-api
composer install --optimize-autoloader --no-dev
```

#### 3. Set Permissions
```bash
# Storage and cache directories need to be writable
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 4. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate

# Edit .env file with production settings
nano .env
```

**IMPORTANT: Production `.env` Variables to Configure:**

```env
# Application Settings (MUST CHANGE)
APP_NAME="QR Code API"
APP_ENV=production                    # MUST be 'production'
APP_KEY=                              # Generated by php artisan key:generate
APP_DEBUG=false                       # MUST be false in production
APP_TIMEZONE=UTC                      # Change to your timezone if needed
APP_URL=https://qr.cma.gov.ae       # MUST match your actual domain

# Localization
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# Maintenance Mode Driver
APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

# Encryption Rounds
BCRYPT_ROUNDS=12                      # Increase for better security (default: 12)

# Logging Configuration
LOG_CHANNEL=stack                     # Options: single, daily, stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error                       # Set to 'error' in production

# Database Configuration (MUST CHANGE)
DB_CONNECTION=mysql                   # Options: mysql, pgsql, sqlite, sqlsrv
DB_HOST=127.0.0.1                    # Database server host
DB_PORT=3306                         # Database port
DB_DATABASE=your_production_db       # Your database name
DB_USERNAME=your_db_user             # Database username
DB_PASSWORD=your_secure_password     # Database password (USE STRONG PASSWORD!)

# Session Configuration
SESSION_DRIVER=database              # Options: file, database, redis
SESSION_LIFETIME=120                  # Session lifetime in minutes
SESSION_ENCRYPT=false                # Set to true for encrypted sessions
SESSION_PATH=/
SESSION_DOMAIN=null                  # Set to your domain for subdomain sessions

# Cache Configuration
CACHE_STORE=database                 # Options: file, database, redis, memcached
CACHE_PREFIX=qrcode_cache_           # Unique prefix if sharing cache

# Queue Configuration
QUEUE_CONNECTION=database            # Options: sync, database, redis
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

# Redis Configuration (if using Redis)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null                  # Set if Redis requires password
REDIS_PORT=6379

# Mail Configuration (if needed)
MAIL_MAILER=smtp                     # Options: smtp, sendmail, mailgun, ses
MAIL_HOST=smtp.your-mail-server.com  # Your SMTP server
MAIL_PORT=587                        # SMTP port (587 for TLS, 465 for SSL)
MAIL_USERNAME=your_email@domain.com  # SMTP username
MAIL_PASSWORD=your_email_password    # SMTP password
MAIL_ENCRYPTION=tls                  # Options: tls, ssl, null
MAIL_FROM_ADDRESS=noreply@qr.cma.gov.ae
MAIL_FROM_NAME="${APP_NAME}"

# AWS Configuration (if using AWS services)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Pusher Configuration (if using real-time features)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# Vite Configuration (for frontend assets)
VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Laravel Sanctum Configuration (CRITICAL FOR SECURITY!)
SANCTUM_STATEFUL_DOMAINS=            # LEAVE EMPTY for token-only auth

# L5-Swagger Configuration
L5_SWAGGER_GENERATE_ALWAYS=false     # MUST be false in production
L5_SWAGGER_GENERATE_YAML_COPY=false
L5_SWAGGER_USE_ABSOLUTE_PATH=true
L5_FORMAT_TO_USE_FOR_DOCS=json
L5_SWAGGER_CONST_HOST=https://qr.cma.gov.ae  # Match your domain
L5_SWAGGER_UI_DARK_MODE=false
L5_SWAGGER_UI_DOC_EXPANSION=none
L5_SWAGGER_UI_FILTERS=true
L5_SWAGGER_UI_PERSIST_AUTHORIZATION=false
L5_SWAGGER_OPEN_API_SPEC_VERSION=3.0.0
```

**Critical Variables to Change:**
1. `APP_ENV` → Must be `production`
2. `APP_DEBUG` → Must be `false`
3. `APP_URL` → Your actual domain
4. `DB_*` → All database credentials
5. `SANCTUM_STATEFUL_DOMAINS` → Leave empty for token-only auth
6. `L5_SWAGGER_GENERATE_ALWAYS` → Must be `false` in production
7. `L5_SWAGGER_CONST_HOST` → Your actual domain
8. `LOG_LEVEL` → Set to `error` for production

#### 5. Run Migrations
```bash
php artisan migrate --force
```

#### 6. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

#### 7. Web Server Configuration

**Nginx Configuration:**
```nginx
server {
    listen 80;
    server_name qr.cma.gov.ae;
    root /var/www/qrcode-api/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Apache Configuration (.htaccess is included):**
Ensure `mod_rewrite` is enabled:
```bash
a2enmod rewrite
systemctl restart apache2
```

#### 8. SSL Certificate (Recommended)
```bash
# Using Let's Encrypt
apt install certbot python3-certbot-nginx
certbot --nginx -d qr.cma.gov.ae
```

## Troubleshooting

### Common Issues and Solutions

#### 1. "Route [login] not defined" Error
**Problem:** Getting this error when accessing protected routes without token.
**Solution:** This has been fixed. The API now returns proper JSON error responses.

#### 2. CSRF Token Mismatch
**Problem:** Getting CSRF token errors.
**Solution:** This has been fixed. API routes don't require CSRF tokens.

#### 3. QR Code Generated Without Token in Swagger
**Problem:** Swagger UI seems to bypass authentication.
**Solution:** 
- Swagger UI may cache tokens even after logout
- Do a hard refresh (Ctrl+Shift+R) after logout
- Close the browser tab completely and reopen
- Clear browser cache if needed

#### 4. 500 Internal Server Error
**Solution:**
- Check Laravel logs: `storage/logs/laravel.log`
- Ensure proper file permissions
- Verify PHP version and extensions
- Check `.env` configuration

#### 5. 419 Page Expired
**Solution:**
- Clear application cache: `php artisan cache:clear`
- Clear config cache: `php artisan config:clear`
- Regenerate application key: `php artisan key:generate`

#### 6. Database Connection Error
**Solution:**
- Verify database credentials in `.env`
- Ensure database server is running
- Check if database exists
- For SQLite, ensure database file exists and is writable

#### 7. Token Authentication Issues
**Solution:**
- Ensure token format is: `Bearer YOUR_TOKEN`
- Check if token hasn't expired
- Verify token exists in database (personal_access_tokens table)
- Try generating a new token

#### 8. Swagger UI Not Loading
**Solution:**
- Regenerate documentation: `php artisan l5-swagger:generate`
- Clear route cache: `php artisan route:clear`
- Check if `/api/documentation` route exists: `php artisan route:list`

### Debugging Commands

```bash
# View all routes
php artisan route:list

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan optimize

# Regenerate Swagger documentation
php artisan l5-swagger:generate

# Check Laravel logs
tail -f storage/logs/laravel.log

# Create a new user via tinker
php artisan tinker
>>> $user = App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password')]);
>>> $token = $user->createToken('admin-token')->plainTextToken;
>>> echo $token;
```

## Security Best Practices

### API Security Features
1. **Token-Only Authentication** - No session cookies
2. **No CSRF Required** - Pure stateless API
3. **Bearer Token Validation** - Every request validated
4. **No Response Caching** - Prevents unauthorized access to cached data
5. **Proper Error Messages** - No sensitive information in errors

### Production Security Checklist
- [ ] Use HTTPS in production
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong database passwords
- [ ] Implement rate limiting
- [ ] Regular security updates
- [ ] Monitor application logs
- [ ] Backup database regularly
- [ ] Use environment variables for sensitive data
- [ ] Implement token expiration if needed
- [ ] Add API request logging for auditing

### Token Management
- Tokens don't expire by default (configurable in `config/sanctum.php`)
- Users can have multiple tokens
- Tokens are revoked on logout
- Store tokens securely in client applications
- Never expose tokens in URLs or logs

## Preparing for Distribution

If you need to prepare this project for zipping and distribution:

```bash
# Clean up development files
php artisan cache:clear
php artisan config:clear
php artisan route:clear
rm -rf storage/logs/*.log
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*

# Ensure .env.example exists
cp .env .env.example

# Remove .env file (contains sensitive data)
rm .env

# Create the zip file (from parent directory)
cd ..
zip -r QrCodeGeneration.zip QrCodeGeneration/ \
  -x "*/vendor/*" \
  "*/.env" \
  "*/node_modules/*" \
  "*/storage/logs/*" \
  "*/storage/framework/cache/*" \
  "*/storage/framework/sessions/*" \
  "*/storage/framework/views/*"
```
