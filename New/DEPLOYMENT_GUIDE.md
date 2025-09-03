# Deployment Guide for QR Code Generation API

## Server Requirements
- PHP >= 8.2
- Composer
- Apache with mod_rewrite enabled
- MySQL/MariaDB (if using database)

## Deployment Steps

### 1. Upload Files
Upload all files to your server, maintaining the directory structure.

### 2. Configure Web Server

#### For Apache (Recommended)
The project includes two .htaccess files:

- **Root .htaccess**: Redirects all requests to the public directory
- **public/.htaccess**: Handles Laravel routing

Make sure your Apache virtual host points to the project root directory (NOT the public directory).

Example Apache Virtual Host Configuration:
```apache
<VirtualHost *:80>
    ServerName qr.cma.gov.ae
    DocumentRoot /path/to/QrCodeGeneration
    
    <Directory /path/to/QrCodeGeneration>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

#### Alternative: Point to Public Directory
If you can't use the root .htaccess approach, configure your web server to point directly to the `public` directory:

```apache
<VirtualHost *:80>
    ServerName qr.cma.gov.ae
    DocumentRoot /path/to/QrCodeGeneration/public
    
    <Directory /path/to/QrCodeGeneration/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 3. Set Permissions
```bash
cd /path/to/QrCodeGeneration

# Set proper ownership (replace www-data with your web server user)
sudo chown -R www-data:www-data .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Make storage and cache writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 4. Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

### 5. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file and update:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qr.cma.gov.ae

# Database settings (if needed)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 6. Optimize Laravel
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. Generate API Documentation
```bash
php artisan l5-swagger:generate
```

## Troubleshooting

### "No input file specified" Error
This usually means the web server can't find `index.php`. Check:

1. **Document Root**: Ensure your web server points to the correct directory
2. **File Permissions**: Web server user must have read access
3. **.htaccess Files**: Ensure both .htaccess files are present and readable
4. **mod_rewrite**: Ensure Apache mod_rewrite is enabled:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

### 500 Internal Server Error
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Apache error logs
3. Verify all required PHP extensions are installed
4. Ensure storage directories are writable

### API Not Working
1. Test the API endpoint:
   ```bash
   curl https://qr.cma.gov.ae/api/generate-qr?url=https://circle.cma.gov.ae/DocumentVerification?docId=00008331-NG-vZox
   ```

2. Check if URL rewriting is working:
   - Navigate to `https://qr.cma.gov.ae/api/generate-qr?url=https://circle.cma.gov.ae/DocumentVerification?docId=00008331-NG-vZox`
   - Should return a QR code image

## API Usage

The QR Code Generation API is now accessible without authentication:

```
GET https://qr.cma.gov.ae/api/generate-qr?url=<URL>&size=<SIZE>
```

Parameters:
- `url` (required): The URL to encode in the QR code
- `size` (optional): Size of the QR code in pixels (default: 300, range: 50-1000)

Example:
```bash
curl "https://qr.cma.gov.ae/api/generate-qr?url=https://circle.cma.gov.ae/DocumentVerification?docId=00008331-NG-vZox&size=100" --output qr.png
```

## Security Notes
- Authentication has been temporarily disabled
- To re-enable authentication, uncomment the code in:
  - `routes/api.php`
  - `app/Http/Controllers/Api/QrCodeController.php`
  - `app/Http/Controllers/AuthController.php`
  - `bootstrap/app.php`