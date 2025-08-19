# Sales App - Aplikasi Penjualan dan Pembelian

Aplikasi berbasis web untuk mengelola penjualan dan pembelian dengan sistem role-based authentication menggunakan Laravel 11 dan TailwindCSS.

## Fitur Utama

### 🔐 Sistem Autentikasi
- Login & Register dengan role-based access
- 3 Role pengguna: **Admin**, **Kasir**, dan **Pelanggan**
- Middleware untuk kontrol akses berdasarkan role

### 📦 Manajemen Produk
- CRUD produk lengkap (khusus Admin)
- Pencarian dan filter berdasarkan kategori
- Tracking stok dengan alert untuk stok rendah
- Perhitungan margin keuntungan otomatis

### 💰 Sistem Transaksi
- Pencatatan penjualan (Admin & Kasir)
- Pencatatan pembelian (khusus Admin)
- Generate nomor transaksi otomatis
- Update stok otomatis berdasarkan transaksi
- Batasan waktu edit/hapus transaksi

### 📊 Dashboard & Laporan
- Dashboard berbeda untuk setiap role
- Laporan penjualan dan pembelian (khusus Admin)
- Laporan inventori dan profit
- Statistik real-time

### 🎨 Antarmuka Modern
- Responsive design dengan TailwindCSS
- Dark/Light mode support
- User-friendly interface
- Mobile-first approach

## Tech Stack

- **Backend**: Laravel 11
- **Frontend**: Blade Templates + TailwindCSS + Alpine.js
- **Database**: MySQL/SQLite
- **Build Tool**: Vite
- **Authentication**: Laravel Breeze-style custom auth

## Struktur Database

### Users
- `id`, `name`, `email`, `password`
- `role` (admin/kasir/pelanggan)
- Timestamps

### Products
- `id`, `kode`, `nama`, `kategori`
- `stok`, `harga_beli`, `harga_jual`
- Timestamps

### Transactions
- `id`, `nomor_transaksi`, `user_id`
- `jenis` (penjualan/pembelian), `total`
- Timestamps

### Transaction Details
- `id`, `transaction_id`, `product_id`
- `qty`, `harga`, `subtotal`
- Timestamps

## Instalasi

### Prasyarat
- PHP 8.2+
- Composer
- Node.js & npm
- MySQL (opsional, menggunakan SQLite by default)

### Langkah Instalasi

1. **Clone atau extract project**
   ```bash
   cd sales-app
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate --seed
   ```

5. **Start server**
   ```bash
   php artisan serve
   ```

6. **Akses aplikasi**
   - Buka browser: `http://localhost:8000`

## Akun Demo

Setelah menjalankan seeder, tersedia akun demo berikut:

| Role | Email | Password |
|------|--------|----------|
| **Admin** | admin@salesapp.com | admin123 |
| **Kasir** | kasir@salesapp.com | kasir123 |
| **Pelanggan** | pelanggan@salesapp.com | pelanggan123 |

## Hak Akses Role

### 👑 Admin
- **Full access** ke semua fitur
- Manajemen produk (CRUD)
- Transaksi penjualan & pembelian
- Akses semua laporan
- Manajemen user (melalui register)

### 🛒 Kasir
- View produk
- Transaksi penjualan
- Dashboard kasir
- Tidak dapat mengelola produk atau pembelian

### 👤 Pelanggan
- View produk
- Lihat transaksi pribadi
- Dashboard sederhana
- Akses terbatas

## Struktur Folder

```
sales-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php
│   │   │   ├── TransactionController.php
│   │   │   └── ReportController.php
│   │   └── Middleware/RoleMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Product.php
│       ├── Transaction.php
│       └── TransactionDetail.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── css/app.css
│   ├── js/app.js
│   └── views/
│       ├── auth/
│       ├── dashboard/
│       ├── products/
│       └── layouts/app.blade.php
└── routes/web.php
```

## Fitur Keamanan

- ✅ CSRF Protection
- ✅ SQL Injection Prevention (Eloquent ORM)
- ✅ XSS Protection (Blade templating)
- ✅ Role-based Access Control
- ✅ Password Hashing
- ✅ Form Validation

## Kustomisasi

### Menambah Role Baru
1. Update enum di migration `users` table
2. Tambah method di `User` model
3. Update `RoleMiddleware`
4. Sesuaikan route permissions

### Menambah Field Produk
1. Buat migration baru: `php artisan make:migration add_field_to_products_table`
2. Update model `Product`
3. Update controller dan views

### Custom Laporan
1. Tambah method di `ReportController`
2. Buat route baru
3. Buat view laporan

## API Endpoints (Web Routes)

```
GET  / → redirect to dashboard
GET  /login → login form
POST /login → authenticate
GET  /register → register form
POST /register → create account
POST /logout → logout

Protected Routes (auth middleware):
GET  /dashboard → dashboard
GET  /products → product list
GET  /products/create → add product (admin)
POST /products → store product (admin)
GET  /products/{id}/edit → edit product (admin)
PUT  /products/{id} → update product (admin)
DELETE /products/{id} → delete product (admin)

GET  /transactions → transaction list (admin/kasir)
GET  /transactions/create → add transaction (admin/kasir)
POST /transactions → store transaction (admin/kasir)

GET  /reports/sales → sales report (admin)
GET  /reports/purchases → purchase report (admin)
GET  /reports/inventory → inventory report (admin)
GET  /reports/profit → profit report (admin)
```

## Troubleshooting

### Error: Class not found
```bash
composer dump-autoload
```

### Error: Vite manifest not found
```bash
npm run build
```

### Error: Permission denied
```bash
chmod -R 755 storage bootstrap/cache
```

### Error: Database connection
Pastikan konfigurasi database di `.env` sudah benar.

## Development

### Menjalankan dalam mode development
```bash
# Terminal 1 - Laravel server
php artisan serve

# Terminal 2 - Vite dev server (opsional)
npm run dev
```

### Testing
```bash
php artisan test
```

### Code Quality
```bash
# Format code
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse
```

## Kontribusi

1. Fork repository
2. Buat branch feature (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

## Lisensi

Project ini menggunakan [MIT License](LICENSE).

## Support

Jika mengalami masalah atau memiliki pertanyaan:

1. Check dokumentasi ini
2. Lihat issue yang sudah ada
3. Buat issue baru dengan detail yang lengkap

---

**Happy Coding! 🚀**

*Dibuat dengan ❤️ menggunakan Laravel 11 & TailwindCSS*