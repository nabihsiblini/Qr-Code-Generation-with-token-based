# Docker Installation and Deployment Guide

## Table of Contents
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Detailed Installation](#detailed-installation)
- [Configuration](#configuration)
- [Docker Architecture](#docker-architecture)
- [Management Commands](#management-commands)
- [Troubleshooting](#troubleshooting)
- [Production Tips](#production-tips)
- [Security Considerations](#security-considerations)

## Prerequisites

### System Requirements
- **Docker Engine**: 20.10 or higher
- **Docker Compose**: 2.0 or higher
- **RAM**: Minimum 2GB (4GB recommended)
- **Disk Space**: Minimum 10GB free space
- **OS**: Linux, macOS, or Windows with WSL2

### Installing Docker

#### Linux (Ubuntu/Debian)
```bash
# Update package index
sudo apt-get update

# Install prerequisites
sudo apt-get install -y \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# Add Docker's official GPG key
sudo mkdir -m 0755 -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Set up repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine
sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Verify installation
docker --version
docker compose version
```

#### macOS
```bash
# Install Docker Desktop from:
# https://www.docker.com/products/docker-desktop/

# Or using Homebrew
brew install --cask docker

# Start Docker Desktop from Applications
# Verify installation
docker --version
docker compose version
```

#### Windows
```powershell
# Install Docker Desktop from:
# https://www.docker.com/products/docker-desktop/

# Ensure WSL2 is enabled
wsl --install

# Start Docker Desktop
# Verify installation in PowerShell or WSL2
docker --version
docker compose version
```

## Quick Start

### 1. Download and Extract Project
```bash
# Extract from zip
unzip QrCodeGeneration.zip
cd QrCodeGeneration
```

### 2. Initial Setup
```bash
# Copy environment file
cp .env.example .env

# Edit environment variables (see Configuration section)
nano .env

# Build and start all services
docker compose up -d --build

# Generate application key
docker compose exec app php artisan key:generate

# Run database migrations
docker compose exec app php artisan migrate --force

# Generate API documentation
docker compose exec app php artisan l5-swagger:generate

# Set permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### 3. Verify Installation
```bash
# Check if containers are running
docker compose ps

# Test API endpoint
curl http://localhost/api/documentation

# Access services
# API: http://localhost
# phpMyAdmin: http://localhost:8080
# Swagger: http://localhost/api/documentation
```

## Detailed Installation

### Step 1: Project Setup
```bash
# Navigate to project directory
cd /path/to/QrCodeGeneration

# Create required directories if they don't exist
mkdir -p storage/logs
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p public/qr-codes
```

### Step 2: Environment Configuration
Create `.env` file with production values:

```env
# Application Settings
APP_NAME="QR Code API"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://qr.cma.gov.ae

# Docker Ports Configuration
APP_PORT=80          # Main application port
DB_PORT=3306        # MySQL port
REDIS_PORT=6379     # Redis port
PMA_PORT=8080       # phpMyAdmin port

# Database Configuration (Docker)
DB_CONNECTION=mysql
DB_HOST=mysql       # Docker service name
DB_PORT=3306
DB_DATABASE=qrcode_db
DB_USERNAME=qrcode_user
DB_PASSWORD=StrongPassword123!
DB_ROOT_PASSWORD=RootPassword456!

# Redis Configuration (Docker)
REDIS_HOST=redis    # Docker service name
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache and Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Sanctum Settings
SANCTUM_STATEFUL_DOMAINS=

# Swagger Settings
L5_SWAGGER_GENERATE_ALWAYS=false
L5_SWAGGER_CONST_HOST=https://qr.cma.gov.ae
```

### Step 3: Build Docker Images
```bash
# Build images without cache (clean build)
docker compose build --no-cache

# Or build with cache (faster)
docker compose build
```

### Step 4: Start Services
```bash
# Start in detached mode
docker compose up -d

# Or start with logs visible
docker compose up
```

### Step 5: Initialize Application
```bash
# Generate application key
docker compose exec app php artisan key:generate

# Clear and cache configurations
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# Run migrations
docker compose exec app php artisan migrate --force

# Seed database (optional)
docker compose exec app php artisan db:seed

# Generate Swagger documentation
docker compose exec app php artisan l5-swagger:generate

# Set correct permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

## Configuration

### Docker Services Configuration

#### Application Service (app)
- **Base Image**: PHP 8.2-FPM Alpine
- **Web Server**: Nginx
- **Process Manager**: Supervisor
- **Workers**: 2 Laravel queue workers
- **Exposed Port**: 80

#### MySQL Service (mysql)
- **Version**: MySQL 8.0
- **Default Database**: qrcode_db
- **Default User**: qrcode_user
- **Data Persistence**: Volume mounted

#### Redis Service (redis)
- **Version**: Redis 7 Alpine
- **Purpose**: Cache, Sessions, Queues
- **Data Persistence**: Volume mounted

#### phpMyAdmin Service (phpmyadmin)
- **Version**: Latest
- **Purpose**: Database management UI
- **Access**: http://localhost:8080

### Volume Mappings

```yaml
# Application volumes
./storage:/var/www/html/storage          # Laravel storage
./public/qr-codes:/var/www/html/public/qr-codes  # Generated QR codes

# Database volume
mysql-data:/var/lib/mysql                # MySQL data persistence

# Redis volume  
redis-data:/data                         # Redis data persistence
```

### Network Configuration

All services communicate through a bridge network named `qrcode-network`:
- Internal DNS resolution using service names
- Isolated from host network
- Services can communicate using service names (mysql, redis, app)

## Docker Architecture

### Directory Structure
```
QrCodeGeneration/
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf         # Main Nginx configuration
│   │   └── default.conf       # Server block configuration
│   ├── php/
│   │   ├── php.ini           # PHP configuration
│   │   └── php-fpm.conf      # PHP-FPM pool configuration
│   ├── supervisor/
│   │   └── supervisord.conf  # Process management
│   └── mysql/
│       └── init.sql          # Database initialization
├── docker-compose.yml         # Docker Compose configuration
├── Dockerfile                # Application container definition
├── .dockerignore            # Files to exclude from build
└── .env                     # Environment variables
```

### Container Build Process

1. **Multi-stage Build**: Optimizes image size
2. **Composer Installation**: Installs PHP dependencies
3. **Node.js Build**: Compiles frontend assets (if needed)
4. **Configuration Copy**: Adds custom configs
5. **Permission Setup**: Sets proper ownership

## Management Commands

### Container Management
```bash
# Start all services
docker compose up -d

# Stop all services
docker compose down

# Restart all services
docker compose restart

# Rebuild and restart
docker compose up -d --build

# Stop and remove everything (including volumes)
docker compose down -v

# View running containers
docker compose ps

# View container logs
docker compose logs -f app
docker compose logs -f mysql
docker compose logs -f redis
```

### Application Commands
```bash
# Run Artisan commands
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:status

# Run Composer commands
docker compose exec app composer install
docker compose exec app composer update
docker compose exec app composer dump-autoload

# Access application shell
docker compose exec app sh

# Access MySQL shell
docker compose exec mysql mysql -u root -p

# Access Redis CLI
docker compose exec redis redis-cli
```

### Maintenance Commands
```bash
# Backup database
docker compose exec mysql mysqldump -u root -p qrcode_db > backup.sql

# Restore database
docker compose exec -T mysql mysql -u root -p qrcode_db < backup.sql

# View application logs
docker compose exec app tail -f storage/logs/laravel.log

# Clear all caches
docker compose exec app php artisan optimize:clear

# Check disk usage
docker system df

# Clean up unused resources
docker system prune -a
```

## Troubleshooting

### Common Issues and Solutions

#### 1. Port Already in Use
```bash
# Error: bind: address already in use

# Solution 1: Change port in .env
APP_PORT=8080
DB_PORT=3307

# Solution 2: Stop conflicting service
sudo systemctl stop nginx
sudo systemctl stop mysql
```

#### 2. Permission Denied Errors
```bash
# Fix storage permissions
docker compose exec app chown -R www-data:www-data storage
docker compose exec app chmod -R 775 storage

# Fix bootstrap cache permissions
docker compose exec app chown -R www-data:www-data bootstrap/cache
docker compose exec app chmod -R 775 bootstrap/cache
```

#### 3. Database Connection Failed
```bash
# Check if MySQL is running
docker compose ps mysql

# Check MySQL logs
docker compose logs mysql

# Test connection
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();

# Reset database
docker compose down -v
docker compose up -d
docker compose exec app php artisan migrate --force
```

#### 4. Redis Connection Failed
```bash
# Check if Redis is running
docker compose ps redis

# Test Redis connection
docker compose exec redis redis-cli ping

# Clear Redis cache
docker compose exec redis redis-cli FLUSHALL
```

#### 5. Container Won't Start
```bash
# Check logs for specific container
docker compose logs app

# Rebuild container
docker compose build --no-cache app
docker compose up -d

# Check Docker daemon
sudo systemctl status docker
```

#### 6. Slow Performance
```bash
# Increase Docker resources (Docker Desktop)
# Settings > Resources > Advanced
# - CPUs: 4
# - Memory: 4GB
# - Swap: 2GB

# Optimize Dockerfile
# Use multi-stage builds
# Minimize layers
# Use Alpine images
```

### Debug Mode
```bash
# Enable debug mode temporarily
docker compose exec app sed -i 's/APP_DEBUG=false/APP_DEBUG=true/' .env
docker compose exec app php artisan config:cache

# View detailed logs
docker compose logs -f --tail=100 app

# Disable debug mode
docker compose exec app sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
docker compose exec app php artisan config:cache
```

## Production Tips

### 1. Security Hardening
```bash
# Use secrets for sensitive data
docker secret create db_password ./db_password.txt

# Limit container capabilities
# Add to docker-compose.yml:
security_opt:
  - no-new-privileges:true
cap_drop:
  - ALL
cap_add:
  - CHOWN
  - SETUID
  - SETGID
```

### 2. Performance Optimization
```yaml
# Add to docker-compose.yml for better performance
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
```

### 3. Health Checks
```yaml
# Add health checks to docker-compose.yml
services:
  app:
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/up"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
```

### 4. Automated Backups
```bash
# Create backup script
cat > backup.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker compose exec mysql mysqldump -u root -p${DB_ROOT_PASSWORD} qrcode_db > backups/db_${DATE}.sql
docker compose exec app tar -czf - storage > backups/storage_${DATE}.tar.gz
EOF

chmod +x backup.sh

# Add to crontab
0 2 * * * /path/to/backup.sh
```

### 5. Monitoring
```bash
# Install monitoring stack
docker run -d \
  --name=netdata \
  -p 19999:19999 \
  -v /proc:/host/proc:ro \
  -v /sys:/host/sys:ro \
  -v /var/run/docker.sock:/var/run/docker.sock:ro \
  --cap-add SYS_PTRACE \
  netdata/netdata

# Access monitoring at http://localhost:19999
```

## Security Considerations

### 1. Environment Variables
- Never commit `.env` file to version control
- Use strong passwords (minimum 16 characters)
- Rotate credentials regularly
- Use Docker secrets for sensitive data

### 2. Network Security
```yaml
# Restrict network access
services:
  mysql:
    ports:
      - "127.0.0.1:3306:3306"  # Only localhost access
```

### 3. Image Security
```bash
# Scan images for vulnerabilities
docker scan qrcode-app:latest

# Use specific versions instead of 'latest'
FROM php:8.2.15-fpm-alpine3.19
```

### 4. Container Security
- Run containers as non-root user
- Use read-only filesystems where possible
- Limit container capabilities
- Enable AppArmor/SELinux

### 5. SSL/TLS Configuration
```nginx
# Add SSL configuration to nginx
server {
    listen 443 ssl http2;
    ssl_certificate /etc/ssl/certs/cert.pem;
    ssl_certificate_key /etc/ssl/private/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
}
```

## Updating the Application

### 1. Code Updates
```bash
# After uploading updated code files

# Rebuild containers
docker compose build --no-cache app

# Restart services
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --force

# Clear caches
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan optimize
```

### 2. Docker Image Updates
```bash
# Update base images
docker compose pull

# Rebuild with new images
docker compose up -d --build

# Remove old images
docker image prune -a
```

### 3. Database Updates
```bash
# Backup before updating
docker compose exec mysql mysqldump -u root -p qrcode_db > backup_before_update.sql

# Run migrations
docker compose exec app php artisan migrate --force

# If rollback needed
docker compose exec app php artisan migrate:rollback
```

## Support and Resources

### Useful Links
- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Documentation](https://laravel.com/docs)
- [Nginx Documentation](https://nginx.org/en/docs/)

### Getting Help
1. Check container logs: `docker compose logs -f app`
2. Verify environment variables: `docker compose exec app php artisan config:show`
3. Test database connection: `docker compose exec app php artisan tinker`
4. Review this documentation's Troubleshooting section

### Performance Monitoring
```bash
# Monitor resource usage
docker stats

# Check container processes
docker compose top

# View detailed container info
docker compose exec app ps aux
```

## License
This Docker configuration is provided as part of the QR Code Generation API project.