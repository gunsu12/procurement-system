# Panduan Deployment Produksi (Docker) - BROS Procurement

Dokumen ini menjelaskan langkah-langkah untuk melakukan deployment **Procurement System** ke server produksi menggunakan Docker.

## 1. Persiapan di Local Machine (Laptop)

Setiap ada perubahan code, lakukan build dan push image baru ke Docker Hub.

```bash
# 1. Compile assets untuk produksi (Opsional jika sudah masuk Dockerfile)
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

### A. Struktur Folder
Buat direktori proyek di server:
```bash
mkdir -p ~/docker/bros_procurement
cd ~/docker/bros_procurement
```

### B. Konfigurasi File
1. Salin file `docker-compose.yml` dan folder `docker/nginx/` dari lokal ke server.
2. Buat file `.env` di server:
   ```bash
   nano .env
   ```
   Sesuaikan nilai (DB, APP_URL, dll) sesuai kebutuhan produksi.

3. **Siapkan folder storage** di host agar tidak error saat mounting:
   ```bash
   mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
   ```

4. **Set Permission** (SANGAT PENTING agar tidak Error 500):
   ```bash
   sudo chmod -R 777 storage bootstrap/cache
   ```

---

## 3. Langkah Instalasi (First Time Only)

1. **Jalankan aplikasi:**
   ```bash
   # Tarik image terbaru dari Docker Hub
   docker-compose pull

   # Jalankan kontainer
   docker-compose up -d
   ```

2. **Setup Awal Laravel:**
   ```bash
   # Generate App Key (jika belum ada di .env)
   docker-compose exec app php artisan key:generate

   # Jalankan Migrasi Database
   docker-compose exec app php artisan migrate --force
   ```

---

## 4. Cara Update Aplikasi (Jika ada perubahan code)

Setiap kali Anda selesai melakukan push image baru dari lokal:

```bash
cd ~/docker/bros_procurement
docker-compose pull
docker-compose up -d
# Jika ada perubahan database:
docker-compose exec app php artisan migrate --force
# Optimasi performa:
docker-compose exec app php artisan optimize
```

---

## Troubleshooting

* **Cek Log Aplikasi:** `docker-compose logs -f app`
* **Error 500:** Biasanya karena permission folder storage. Pastikan sudah menjalankan `chmod -R 777 storage`.
* **Masuk ke Kontainer:** `docker-compose exec app bash`
