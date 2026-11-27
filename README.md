# SIMASI-PARKIR Web
SIMASI-PARKIR adalah aplikasi web untuk mengelola parkir sepeda motor di Fakultas Ilmu Komputer Universitas Jember. Aplikasi ini dibangun dengan Laravel, Blade, Bootstrap, dan MySQL.

> [!WARNING]
> Project ini dibuat untuk penilaian matakuliah Pemrograman Website, semua source code disini hanyalah untuk pebelajaran.



# Langkah Instalasi Pertama Kali

```bash

git clone https://github.com/g3raldatsc/simasi-parkir_web.git
cd simasi-parkir_web

```

```bash

composer install
npm install

```

```bash

cp .env.example .env

```

```bash

php artisan key:generate

```

```bash

php artisan migrate

```

```bash

php artisan db:seed

```
