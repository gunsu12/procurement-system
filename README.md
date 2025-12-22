# Panduan Deployment Produksi (Docker)

Dokumen ini menjelaskan langkah-langkah untuk melakukan deployment **Procurement System** ke server produksi menggunakan Docker.

## 1. Persiapan di Local Machine (Laptop)

Sebelum deploy, pastikan image terbaru sudah di-build dan di-push ke Docker Hub.

```bash
# 1. Compile assets untuk produksi
npm install
npm run prod

# 2. Build image Docker
docker build -t gunsu13/bros_procurement:latest .

# 3. Login ke Docker Hub (jika belum)
docker login

# 4. Push image ke repository
docker push gunsu13/bros_procurement:latest
```

---

## 2. Persiapan di Server (Production)

Pastikan server sudah terinstall **Docker** dan **Docker Compose**.

### Langkah-langkah:

1. **Buat direktori proyek:**
   ```bash
   mkdir -p ~/docker/bros_procurement
   cd ~/docker/bros_procurement
   ```

2. **Salin file konfigurasi:**
   Salin file `docker-compose.yml` dan folder `docker/nginx/` dari lokal ke server di dalam folder tersebut.

3. **Buat file `.env` di server:**
   ```bash
   nano .env
   ```
   Sesuaikan nilai-nilai berikut dengan kondisi server:
   ```env
   APP_NAME="Procurement System"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://IP_SERVER_ATAU_DOMAIN

   DB_CONNECTION=pgsql
   DB_HOST=IP_DATABASE_ATAU_NAMA_SERVICE
   DB_PORT=5432
   DB_DATABASE=procurement_system
   DB_USERNAME=postgres
   DB_PASSWORD=your_secure_password
   ```

4. **Jalankan aplikasi:**
   ```bash
   # Tarik image terbaru dari Docker Hub
   docker-compose pull

   # Jalankan kontainer
   docker-compose up -d
   ```

---

## 3. Langkah Pasca-Install (First Time Only)

Jalankan perintah ini hanya saat pertama kali install atau jika ada perubahan database:

1. **Generate App Key:**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

2. **Jalankan Migrasi Database:**
   ```bash
   docker-compose exec app php artisan migrate --force
   ```

3. **Optimasi Laravel (Opsional tapi disarankan):**
   ```bash
   docker-compose exec app php artisan config:cache
   docker-compose exec app php artisan route:cache
   docker-compose exec app php artisan view:cache
   ```

---

## 4. Cara Update Aplikasi (Jika ada perubahan code)

Setiap kali Anda selesai melakukan coding dan ingin update server:

1. **Di Lokal:** Build & Push (`docker build ...` & `docker push ...`)
2. **Di Server:**
   ```bash
   cd ~/docker/bros_procurement
   docker-compose pull
   docker-compose up -d
   docker-compose exec app php artisan migrate --force
   docker-compose exec app php artisan optimize
   ```

---

## Troubleshooting

* **Cek Log Aplikasi:** `docker-compose logs -f app`
* **Masuk ke Kontainer:** `docker-compose exec app bash`
* **Restart Manual:** `docker-compose restart`
