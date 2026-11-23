# SIMASI-PARKIR Web

SIMASI-PARKIR adalah aplikasi web untuk mengelola parkir sepeda motor di Fakultas Ilmu Komputer Universitas Jember. Aplikasi ini dibangun dengan Laravel, Blade, Bootstrap, dan MySQL.

## Kebutuhan

- PHP 8.3 atau lebih baru
- Composer
- MySQL
- Node.js dan npm

## Menyiapkan database

Jalankan MySQL melalui Laragon, lalu buat database kosong:

```sql
CREATE DATABASE simasi_parkir
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Pastikan pengaturan database pada `.env` menunjuk ke database tersebut:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simasi_parkir
DB_USERNAME=root
DB_PASSWORD=
```

Jangan menimpa `.env` yang sudah ada karena file itu dapat berisi konfigurasi lokal.

## Instalasi pertama kali

Dari PowerShell, jalankan perintah berikut di folder project:

```powershell
cd C:\laragon\www\simasi-parkir_web
composer install
```

Jika `.env` belum ada, salin template dan buat application key:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Sesuaikan kredensial MySQL di `.env`, lalu buat tabel dan data demo:

```powershell
php artisan migrate --seed
```

Pasang dan build asset frontend:

```powershell
npm install
npm run build
```

## Membangun ulang database development

Jika database sudah berisi tabel dari setup SQL manual sebelumnya, migrasi Laravel belum mencatat atau mengelola tabel-tabel tersebut. Untuk menggantinya dengan skema migrasi project dan mengisi data demo, pastikan dulu `DB_DATABASE` benar-benar menunjuk ke database development `simasi_parkir`, lalu jalankan:

```powershell
php artisan config:clear
php artisan db:wipe --database=mysql --force
php artisan migrate --seed --force
```

**Perhatian:** `db:wipe` menghapus semua tabel dan data di database yang sedang dipilih. Jangan jalankan pada database penting atau production. Untuk database kosong, perintah wipe tidak diperlukan; cukup gunakan `php artisan migrate --seed`.

Untuk melihat status migrasi:

```powershell
php artisan migrate:status
```

## Data demo

Seeder membuat:

- Pengguna admin dan petugas.
- Dua blok parkir dengan delapan slot dan satu sel peta untuk pintu masuk.
- Tiga kendaraan.
- Dua transaksi yang masih aktif dan satu transaksi yang sudah selesai.

Akun lokal untuk pengujian:

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@simasi.test` | `password` |
| Petugas | `petugas@simasi.test` | `password` |

Kredensial tersebut hanya untuk development. Route login aplikasi belum diimplementasikan.

## Menjalankan aplikasi

Jalankan server Laravel:

```powershell
php artisan serve
```

Buka `http://127.0.0.1:8000`. Untuk pengembangan frontend dengan Vite hot reload, jalankan juga di terminal terpisah:

```powershell
npm run dev
```

## Pengujian

```powershell
php artisan test
```

## Tabel aplikasi

Migrasi Laravel mengelola tabel `users`, `blocks`, `map_cells`, `vehicles`, `slots`, dan `parking_transactions`, beserta tabel pendukung Laravel untuk sessions, cache, dan jobs.
