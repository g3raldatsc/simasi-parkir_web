# SIMASI-PARKIR Web

SIMASI-PARKIR adalah aplikasi web untuk mengelola parkir sepeda motor di Fakultas Ilmu Komputer Universitas Jember. Aplikasi ini dibangun dengan Laravel, Blade, Bootstrap, dan MySQL.

## Kebutuhan Sistem

- PHP 8.3 atau lebih baru
- Composer
- MySQL (disarankan via Laragon)
- Git

## Langkah Instalasi Pertama Kali

Ikuti langkah-langkah berikut secara berurutan setelah meng-clone project:

### 1. Clone repository dan masuk ke folder project

```bash
git clone https://github.com/g3raldatsc/simasi-parkir_web.git
cd simasi-parkir_web
```

### 2. Pasang dependensi PHP

```bash
composer install
```

### 3. Salin file environment dan generate application key

Untuk Git Bash / Linux / macOS / PowerShell:
```bash
cp .env.example .env
php artisan key:generate
```

*(Atau di CMD Windows: `copy .env.example .env`)*

### 4. Siapkan database MySQL

Nyalakan MySQL (misalnya melalui Laragon), lalu buat database kosong:

```sql
CREATE DATABASE simasi_parkir
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Pastikan pengaturan database di file `.env` sudah sesuai:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simasi_parkir
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Jalankan migrasi dan isi data demo

```bash
php artisan migrate --seed
```

> [!NOTE]
> Aset tampilan menggunakan Bootstrap 5 via CDN dan stylesheet statis di folder `public/`, sehingga Anda tidak perlu menjalankan build frontend dengan npm untuk menjalankan aplikasi.

### 6. Jalankan server aplikasi

```bash
php artisan serve
```

Aplikasi dapat dibuka melalui browser di:
- **Halaman Publik / Pencarian:** [http://127.0.0.1:8000](http://127.0.0.1:8000)
- **Peta Parkir:** [http://127.0.0.1:8000/peta](http://127.0.0.1:8000/peta)
- **Login Petugas / Admin:** [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)

---

## Akun Demo untuk Pengujian

Seeder (`php artisan db:seed` atau saat `migrate --seed`) secara otomatis membuat data awal:
- Pengguna admin dan petugas.
- Dua blok parkir dengan delapan slot dan satu sel peta untuk pintu masuk.
- Tiga kendaraan.
- Dua transaksi parkir aktif dan satu transaksi yang sudah selesai.

Kredensial akun untuk pengujian lokal:

| Peran | Email | Password | Akses URL |
|---|---|---|---|
| Admin | `admin@simasi.test` | `password` | `/login` |
| Petugas | `petugas@simasi.test` | `password` | `/login` |

Setelah login, pengguna akan diarahkan ke dashboard panel petugas/admin.

---

## Membangun Ulang Database Development

Jika ingin mereset dan mengisi ulang database dari awal (misalnya setelah ada perubahan skema migrasi atau seeder):

```bash
php artisan config:clear
php artisan db:wipe --database=mysql --force
php artisan migrate --seed --force
```

> [!WARNING]
> Perintah `db:wipe` akan menghapus seluruh tabel dan data pada database yang sedang aktif. Pastikan hanya dijalankan di environment lokal/development.

Untuk mengecek status migrasi saat ini:

```bash
php artisan migrate:status
```

---

## Menjalankan Pengujian (Testing)

Untuk memastikan seluruh pengujian otomatis (Unit dan Feature tests) berjalan dengan baik:

```bash
php artisan test
```

---

## Struktur Modul & Tabel

Routing aplikasi dipisahkan per modul di direktori `routes/modules/`:
- `auth.php` : Autentikasi (login, logout, dashboard, pengguna)
- `master.php` : Master data (blok, slot parkir)
- `parkir.php` : Operasional parkir (kendaraan masuk/keluar, transaksi, riwayat)
- `publik.php` : Tampilan publik (beranda, pencarian motor, visualisasi peta parkir)

Tabel utama yang dikelola oleh migrasi Laravel meliputi: `users`, `blocks`, `slots`, `map_cells`, `vehicles`, dan `parking_transactions`.
