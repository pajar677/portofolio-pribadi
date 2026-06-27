# 🌟 Website Portofolio PHP & MySQL

Website portofolio dinamis yang dibangun dengan PHP & MySQL, dilengkapi panel admin.

## 📁 Struktur File

```
portfolio/
├── index.php           ← Halaman utama portofolio
├── database.sql        ← Setup database (jalankan sekali)
├── admin/
│   └── index.php       ← Panel admin
└── includes/
    └── config.php      ← Konfigurasi database
```

## 🚀 Cara Setup

### 1. Siapkan Database
- Buka **phpMyAdmin** (biasanya di `localhost/phpmyadmin`)
- Klik **"New"** → buat database bernama `portfolio_db`
- Klik tab **"Import"** → pilih file `database.sql` → klik **Go**

### 2. Konfigurasi Koneksi
Edit file `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Username MySQL Anda
define('DB_PASS', '');           // Password MySQL Anda (kosong jika XAMPP default)
define('DB_NAME', 'portfolio_db');
```

### 3. Jalankan di XAMPP/WAMP
- Letakkan folder `portfolio/` di dalam `htdocs/` (XAMPP) atau `www/` (WAMP)
- Buka browser: `http://localhost/portfolio/`
- Admin panel: `http://localhost/portfolio/admin/`

## 🔐 Akses Admin Panel

URL: `http://localhost/portfolio/admin/`

| Field | Default |
|-------|---------|
| Username | `admin` |
| Password | `admin123` |

> ⚠️ **Ganti password** di file `admin/index.php` sebelum deploy ke internet!
> Cari: `define('ADMIN_PASS', 'admin123');` dan ubah nilainya.

## ✏️ Cara Mengisi Data Anda

### Via Admin Panel (Mudah)
1. Login ke `http://localhost/portfolio/admin/`
2. Tab **Profil** → isi nama, jabatan, bio, email, dll
3. Tab **Keahlian** → tambah skill beserta level persentase
4. Tab **Proyek** → tambah proyek-proyek yang pernah dibuat

### Via phpMyAdmin (Langsung di Database)
Edit tabel:
- `profil` → data diri Anda
- `keahlian` → skill & level
- `pengalaman` → riwayat kerja
- `pendidikan` → riwayat sekolah/kuliah
- `proyek` → proyek yang dibuat
- `sertifikat` → sertifikat yang dimiliki

## 🎨 Fitur Website

- ✅ Desain modern dengan tema Navy & Gold
- ✅ Animasi typewriter di hero section
- ✅ Filter skill berdasarkan kategori
- ✅ Timeline pengalaman & pendidikan
- ✅ Grid proyek dengan badge unggulan
- ✅ Animasi scroll (fade up)
- ✅ Responsive untuk mobile
- ✅ Navbar sticky dengan active state
- ✅ Panel admin dengan login session
- ✅ CRUD keahlian dan proyek via admin

## 🛠️ Teknologi

- **Backend**: PHP 7.4+ dengan PDO
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Icons**: Font Awesome 6
- **Fonts**: Space Grotesk + Lora (Google Fonts)
