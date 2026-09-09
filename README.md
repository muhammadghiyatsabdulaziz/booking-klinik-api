# Booking Klinik API

API backend untuk aplikasi booking klinik dengan Laravel dan Supabase PostgreSQL.

## Tech Stack

- **Backend:** Laravel 13
- **Database:** PostgreSQL (Supabase)
- **Authentication:** Laravel Sanctum
- **Payment Gateway:** Midtrans
- **Frontend:** Next.js (repository terpisah)

## Features

- Autentikasi user (register, login, logout)
- Manajemen data dokter dan spesialisasi
- Jadwal dokter dan exception schedule
- Booking appointment
- Payment integration dengan Midtrans
- Email confirmation untuk booking

## Setup Development

### Prerequisites

- PHP >= 8.3
- Composer
- Node.js & npm
- Akun Supabase (untuk PostgreSQL database)

### Installation

1. Clone repository dan install dependencies

```bash
composer install
npm install
```

2. Setup environment

```bash
copy .env.example .env
php artisan key:generate
```

### Database Setup dengan Supabase

1. **Buat project di Supabase:**
   - Kunjungi [supabase.com](https://supabase.com)
   - Buat project baru
   - Pilih region terdekat (Southeast Asia / Singapore)
   - Catat password database

2. **Dapatkan connection string:**
   - Buka project Supabase → **Settings → Database**
   - Copy detail connection:
     - **Host:** `db.<your-project-ref>.supabase.co`
     - **Port:** `5432`
     - **Database:** `postgres`
     - **User:** `postgres`

3. **Update file `.env`:**

```env
DB_CONNECTION=pgsql
DB_HOST=db.your-project-ref.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password
DB_SSLMODE=require
```

4. **Jalankan migrasi:**

```bash
php artisan migrate
php artisan db:seed  # optional
```

### Run Development Server

```bash
php artisan serve
```

API berjalan di `http://localhost:8000`

## API Endpoints

### Authentication
- `POST /api/register` - Register user
- `POST /api/login` - Login
- `POST /api/logout` - Logout

### Doctors
- `GET /api/doctors` - List dokter
- `GET /api/doctors/{id}` - Detail dokter
- `GET /api/doctors/{id}/schedules` - Jadwal dokter

### Bookings
- `POST /api/bookings` - Buat booking
- `GET /api/bookings` - List booking user
- `PATCH /api/bookings/{id}/cancel` - Cancel booking

### Payments
- `POST /api/payments/{booking}` - Buat pembayaran
- `POST /api/payments/notification` - Webhook Midtrans

## Troubleshooting

### Connection Refused ke Supabase
- Pastikan `DB_SSLMODE=require` di `.env`
- Cek connection string dari dashboard Supabase
- Pastikan project Supabase tidak di-pause

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
```

## License

MIT
