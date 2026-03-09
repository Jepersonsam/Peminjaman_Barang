# Dokumentasi Cara Menjalankan Program
## Sistem Peminjaman Kantor

---

## 📋 Persyaratan Sistem

Sebelum menjalankan program, pastikan sistem Anda telah terinstall:

1. **PHP** versi 8.2 atau lebih tinggi
2. **Composer** (PHP Package Manager)
3. **Node.js** dan **NPM** (untuk asset compilation)
4. **Database** (SQLite sudah disertakan, atau MySQL/PostgreSQL)
5. **Git** (opsional)

---

## 🚀 Langkah-langkah Instalasi

### 1. Clone atau Download Project

Jika menggunakan Git:
```bash
git clone <repository-url>
cd kantor-peminjaman
```

Atau extract file project ke direktori yang diinginkan.

### 2. Install Dependencies PHP

Jalankan perintah berikut untuk menginstall semua dependency PHP:

```bash
composer install
```

### 3. Install Dependencies JavaScript

Jalankan perintah berikut untuk menginstall dependency frontend:

```bash
npm install
```

---

## ⚙️ Konfigurasi

### 1. Setup File Environment

Buat file `.env` dari template (jika belum ada):

```bash
cp .env.example .env
```

Atau buat file `.env` baru dengan konfigurasi berikut:

```env
APP_NAME="Sistem Peminjaman Kantor"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Untuk MySQL/PostgreSQL, gunakan konfigurasi berikut:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=kantor_peminjaman
# DB_USERNAME=root
# DB_PASSWORD=

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Frontend URL untuk reset password
FRONTEND_URL=http://localhost:3000
```

### 2. Generate Application Key

Jalankan perintah berikut untuk generate application key:

```bash
php artisan key:generate
```

### 3. Setup Database

**Untuk SQLite (Default):**

Pastikan file database sudah ada:
```bash
touch database/database.sqlite
```

**Untuk MySQL/PostgreSQL:**

Buat database baru dengan nama `kantor_peminjaman` (atau sesuai konfigurasi di `.env`).

### 4. Jalankan Migration

Jalankan migration untuk membuat tabel-tabel database:

```bash
php artisan migrate
```

### 5. Jalankan Seeder

Jalankan seeder untuk mengisi data awal (roles, permissions, dan super admin):

```bash
php artisan db:seed
```

Atau jalankan seeder secara terpisah:

```bash
# Seeder untuk roles dan permissions
php artisan db:seed --class=RolePermissionSeeder

# Seeder untuk super admin
php artisan db:seed --class=SuperAdminSeeder

# Seeder untuk user (opsional)
php artisan db:seed --class=UserSeeder
```

### 6. Setup Laravel Passport

Karena aplikasi menggunakan Laravel Passport untuk autentikasi API, jalankan perintah berikut:

```bash
php artisan passport:install
```

Perintah ini akan membuat encryption keys dan client credentials untuk OAuth2.

---

## ▶️ Menjalankan Aplikasi

### 1. Menjalankan Development Server

Jalankan server Laravel dengan perintah:

```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

### 2. Menjalankan dengan Semua Service (Recommended)

Untuk menjalankan server, queue, logs, dan Vite secara bersamaan:

```bash
composer run dev
```

Atau:

```bash
php artisan serve
```

Di terminal terpisah, jalankan:

```bash
# Queue Worker (jika menggunakan queue)
php artisan queue:work

# Vite Dev Server (untuk asset compilation)
npm run dev
```

### 3. Build Assets untuk Production

Jika ingin build assets untuk production:

```bash
npm run build
```

---

## 🔐 Kredensial Default

Setelah menjalankan seeder, Anda dapat login dengan kredensial berikut:

**Super Admin:**
- Email: `superadmin@example.com`
- Password: `password123`
- Code: `SA001`

**Catatan:** Disarankan untuk mengubah password setelah login pertama kali.

---

## 📡 API Endpoints

Aplikasi ini menggunakan API RESTful. Base URL API:

```
http://localhost:8000/api
```

### Endpoint Autentikasi

- `POST /api/register` - Registrasi user baru
- `POST /api/login` - Login user
- `GET /api/me` - Get user yang sedang login (perlu autentikasi)
- `POST /api/forgot-password` - Request reset password
- `POST /api/reset-password` - Reset password

### Endpoint Publik (Tanpa Autentikasi)

- `GET /api/public/items` - Daftar item yang tersedia
- `POST /api/public/borrowings` - Buat peminjaman (tanpa login)
- `POST /api/public/return-item` - Kembalikan item
- `GET /api/room-loans/check-availability` - Cek ketersediaan ruangan

### Endpoint yang Memerlukan Autentikasi

Semua endpoint di bawah ini memerlukan token Bearer dari Laravel Passport:

- User Management: `/api/users`
- Item Management: `/api/items`
- Borrowing Management: `/api/borrowings`
- Room Management: `/api/rooms`
- Room Loan Management: `/api/room-loans`
- Role & Permission Management: `/api/roles`, `/api/permissions`

---

## 🧪 Testing

Untuk menjalankan test:

```bash
php artisan test
```

Atau:

```bash
composer run test
```

---

## 🔧 Troubleshooting

### Masalah: "Class 'PDO' not found"

**Solusi:** Install extension PDO untuk PHP:
```bash
# Ubuntu/Debian
sudo apt-get install php-pdo php-sqlite3

# Windows
# Aktifkan extension di php.ini:
# extension=pdo_sqlite
```

### Masalah: "SQLSTATE[HY000] [14] unable to open database file"

**Solusi:** Pastikan file `database/database.sqlite` ada dan memiliki permission write:
```bash
touch database/database.sqlite
chmod 664 database/database.sqlite
```

### Masalah: "Passport keys not found"

**Solusi:** Jalankan ulang:
```bash
php artisan passport:install
```

### Masalah: "The stream or file could not be opened"

**Solusi:** Pastikan folder storage memiliki permission write:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### Masalah: "Vite manifest not found"

**Solusi:** Build assets terlebih dahulu:
```bash
npm run build
```

---

## 📝 Catatan Penting

1. **Environment File:** Jangan commit file `.env` ke repository. File ini berisi informasi sensitif.

2. **Database:** Untuk production, gunakan MySQL atau PostgreSQL, bukan SQLite.

3. **Security:** Pastikan `APP_DEBUG=false` dan `APP_ENV=production` di production.

4. **Passport Keys:** Simpan passport keys dengan aman. Jangan commit ke repository.

5. **Queue:** Jika menggunakan queue, pastikan queue worker berjalan:
   ```bash
   php artisan queue:work
   ```

---

## 🎯 Quick Start (Ringkasan)

```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Setup database
touch database/database.sqlite
php artisan migrate
php artisan db:seed

# 4. Setup Passport
php artisan passport:install

# 5. Jalankan aplikasi
php artisan serve
# atau
composer run dev
```

---

## 📞 Support

Jika mengalami masalah, periksa:
1. Log file di `storage/logs/laravel.log`
2. Pastikan semua requirement terpenuhi
3. Pastikan konfigurasi `.env` sudah benar

---

**Dokumentasi ini dibuat untuk membantu proses instalasi dan menjalankan aplikasi Sistem Peminjaman Kantor.**

