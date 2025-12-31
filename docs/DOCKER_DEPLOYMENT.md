# Docker Deployment Guide - Laravel 10

## 📦 Production Deployment

### Prerequisites
- Docker Engine 20.10+
- Docker Compose 2.0+
- **External PostgreSQL database** (already configured)
- AWS S3 bucket configured
- Domain with SSL certificate

### Quick Start

#### 1. **Prepare Environment**
```bash
# Copy and configure production environment
cp .env.example .env.production
nano .env.production
```

Required environment variables:
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:... # Generate with: php artisan key:generate
APP_URL=https://your-domain.com

# External PostgreSQL Database
DB_CONNECTION=pgsql
DB_HOST=your-external-db-host
DB_PORT=5432
DB_DATABASE=procurement
DB_USERNAME=your-user
DB_PASSWORD=your-password

AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=your-region
AWS_BUCKET=your-bucket

SSO_BASE_URL=https://sso.example.com
SSO_CLIENT_ID=procurement
SSO_CLIENT_SECRET=your-secret
```

#### 2. **Build and Deploy**
```bash
# Build the Docker image
docker-compose -f docker-compose.prod.yml build

# Start the application
docker-compose -f docker-compose.prod.yml up -d

# Check status
docker-compose -f docker-compose.prod.yml ps

# View logs
docker-compose -f docker-compose.prod.yml logs -f app
```

#### 3. **Run Migrations** (First time only)
```bash
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

#### 4. **Setup SSL with Nginx** (Recommended)
Use a reverse proxy like Nginx with Let's Encrypt:
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    location / {
        proxy_pass http://localhost:9001;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

---

## 🧪 Local Development

### Using Docker for Development
```bash
# Start all services (including local PostgreSQL)
docker-compose up -d

# Access the application
http://localhost:9001

# Stop services
docker-compose down

# Rebuild after changes
docker-compose up -d --build
```

### Development with Hot Reload
For development, mount the code directory:
```yaml
# In docker-compose.yml
volumes:
  - ./:/var/www  # Uncomment this line
```

---

## 🔧 Maintenance Commands

### Update Application
```bash
# Pull latest code
git pull origin main

# Rebuild and restart
docker-compose -f docker-compose.prod.yml up -d --build

# Clear caches
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec app php artisan config:clear
docker-compose -f docker-compose.prod.yml exec app php artisan route:clear
docker-compose -f docker-compose.prod.yml exec app php artisan view:clear
```

### Database Backup
```bash
# Backup database (if using local PostgreSQL)
docker-compose exec db pg_dump -U postgres procurement > backup_$(date +%Y%m%d).sql

# Restore database
docker-compose exec -T db psql -U postgres procurement < backup.sql
```

### View Logs
```bash
# All services
docker-compose -f docker-compose.prod.yml logs -f

# Specific service
docker-compose -f docker-compose.prod.yml logs -f app
docker-compose -f docker-compose.prod.yml logs -f web
```

---

## 🚀 Scaling

### Scale Application Containers
```bash
# Scale to 3 app instances
docker-compose -f docker-compose.prod.yml up -d --scale app=3
```

### Health Checks
All services have health checks configured:
- **App**: `php-fpm -t` every 30s
- **Web**: HTTP check every 30s
- **DB**: `pg_isready` every 10s

---

## 🔒 Security Best Practices

1. **Never commit `.env.production`** - Use secrets management
2. **Use non-root user** - Already configured in Dockerfile
3. **Read-only volumes** - Nginx volumes are mounted as `:ro`
4. **Resource limits** - Set in `docker-compose.prod.yml`
5. **Health checks** - Monitor service availability
6. **Network isolation** - Services communicate via `app-network`

---

## 📊 Monitoring

### Check Container Health
```bash
docker ps --format "table {{.Names}}\t{{.Status}}"
```

### Resource Usage
```bash
docker stats
```

### Performance Tuning
Edit `Dockerfile` PHP settings:
```ini
memory_limit=512M         # Adjust based on needs
post_max_size=100M        # File upload limit
upload_max_filesize=100M  # File upload limit
max_execution_time=300    # Script timeout
```

---

## 🆘 Troubleshooting

### Container Won't Start
```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs app

# Inspect container
docker inspect procurement-app-prod
```

### Permission Issues
```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Failed
```bash
# Verify database is accessible
docker-compose exec app nc -zv $DB_HOST $DB_PORT

# Test database connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

---

## 📝 File Structure

```
📁 docker/
├── nginx/
│   └── laravel.conf       # Nginx configuration
└── entrypoint.sh          # Container startup script

📄 Dockerfile              # Multi-stage production build
📄 .dockerignore           # Exclude files from build
📄 docker-compose.yml      # Development setup
📄 docker-compose.prod.yml # Production setup
```
