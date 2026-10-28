# MBACCO – Inventory & Sales Management System

Aplikasi manajemen stok dan penjualan berbasis **Laravel + MySQL** (UTS Web Programming 2 – Integrated Laravel Project).

## 1. Project Overview
Sistem untuk toko tembakau **MBACCO** (instagram.com/mbacco.id): mengelola kategori, produk, pemasok, pembelian (stock-in), dan penjualan.
Stok berubah otomatis dan konsisten saat transaksi berhasil. Ada tiga role: **Admin/Owner**, **Staff**, dan **Customer** (pembeli yang mendaftar sendiri dan memesan lewat toko online).

## 2. Requirements
- PHP >= 8.2 (ekstensi: mbstring, xml, pdo_mysql, ctype, json)
- Composer 2
- MySQL / MariaDB (XAMPP + phpMyAdmin)
- Laravel 11 atau 12
- Tidak perlu Node/npm: tampilan memakai Bootstrap 5 via CDN (butuh internet saat membuka halaman)

## 3. Installation

### Cara cepat (clone repository ini)
```bash
git clone https://github.com/sayedkaysan11/mbacco-inventory-sales.git
cd mbacco-inventory-sales
composer install
copy .env.example .env
php artisan key:generate
```
(Di Linux/macOS gunakan `cp .env.example .env`.)

### Cara alternatif (pasang di atas project Laravel baru)
Repository ini berisi **lapisan aplikasi** (models, controllers, views, migrations, seeders, tests).

```bash
composer create-project laravel/laravel inventory-sales
cd inventory-sales
# salin isi folder paket ini ke root project (timpa file bila diminta):
#   app/  database/  resources/  routes/  tests/  README.md
```

## 4. Environment Setup
Edit `.env` (file ini jangan di-commit; commit hanya `.env.example`):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_sales
DB_USERNAME=root
DB_PASSWORD=
```
Nyalakan **Apache** dan **MySQL** di XAMPP, buat database kosong `inventory_sales` lewat phpMyAdmin, lalu pastikan `APP_KEY` terisi (`php artisan key:generate`).

Zona waktu aplikasi diatur ke `Asia/Jakarta` (WIB) di `config/app.php`.

## 5. Migration & Seeding
```bash
php artisan migrate --seed      # atau: php artisan migrate:fresh --seed
php artisan serve
```
Buka http://127.0.0.1:8000

## 6. Demo Account
| Role     | Email             | Password    |
|----------|-------------------|-------------|
| Admin    | admin@example.com | password123 |
| Staff    | staff@example.com | password123 |
| Customer | kaysan@gmail.com  | 12345678    |

Customer juga bisa mendaftar sendiri lewat halaman `/register`.
(Akun untuk demo, bukan kredensial asli.)

## 7. Features
| Modul | Admin | Staff | Customer |
|---|---|---|---|
| Login / logout, password hash, route terlindungi | ✔ | ✔ | ✔ |
| Daftar mandiri (`/register`) | - | - | ✔ |
| Kategori, Pemasok (CRUD) | ✔ | ✘ | ✘ |
| Produk (CRUD) | ✔ | hanya lihat | ✘ |
| Pengguna & role | ✔ | ✘ | ✘ |
| Pembelian / stock-in (multi item) | ✔ semua | ✔ miliknya | ✘ |
| Penjualan (multi item) | ✔ semua | ✔ miliknya | ✘ |
| Pembatalan transaksi (stok dipulihkan) | ✔ | ✘ | ✘ |
| Dashboard (agregat dari DB), daftar stok rendah | ✔ | ✔ (angka miliknya) | dialihkan ke toko |
| Toko: katalog, pesan barang, riwayat pesanan | ✘ | ✘ | ✔ |
| Search SKU/nama, filter kategori/tanggal/status, pagination | ✔ | ✔ | ✔ (katalog) |

### Fitur Customer (toko)
- Daftar mandiri (`/register`); role selalu `customer`, tidak bisa dipilih dari form.
- Melihat katalog produk **tanpa harga modal**.
- Memesan barang: stok berkurang otomatis, pesanan ditolak bila stok tidak cukup.
- Hanya bisa melihat pesanannya sendiri, dan tidak bisa membatalkan pesanan.
- Membuka `/dashboard` otomatis dialihkan ke toko; halaman staff/admin ditolak (403).

### Business rules yang diterapkan
- SKU unik (validasi **dan** unique index database).
- Kuantitas > 0, harga >= 0, divalidasi server.
- Penjualan tidak boleh melebihi stok; stok hanya berubah setelah transaksi sukses.
- Total & harga dihitung server dari database, bukan dari input browser.
- Header + item + perubahan stok disimpan dalam satu `DB::transaction` (rollback bila ada yang gagal).
- Baris produk dikunci `lockForUpdate()` untuk mencegah overselling pada transaksi bersamaan.
- Transaksi tidak diedit; hanya bisa dibatalkan (stok dipulihkan, pembatalan ganda ditolak).
- Pembatalan **pembelian** ditolak bila stok saat ini sudah lebih kecil dari kuantitas pembelian (stok tak boleh negatif).
- Produk yang sudah dipakai transaksi tidak dapat dihapus; produk lain di-*soft delete*.
- Kategori/pemasok/pengguna yang masih dipakai tidak dapat dihapus (FK `restrictOnDelete` + pengecekan).
- Hak akses dicek di server lewat Gate (`admin`, `internal`, `customer`), bukan hanya dengan menyembunyikan menu.

## ERD
```mermaid
erDiagram
    users ||--o{ sales : "mencatat"
    users ||--o{ purchases : "mencatat"
    categories ||--o{ products : "memiliki"
    suppliers ||--o{ purchases : "memasok"
    purchases ||--|{ purchase_items : "berisi"
    sales ||--|{ sale_items : "berisi"
    products ||--o{ purchase_items : "dibeli"
    products ||--o{ sale_items : "dijual"
    users { bigint id PK
        string name
        string email UK
        string password
        string role }
    categories { bigint id PK
        string name UK }
    suppliers { bigint id PK
        string name
        string phone
        string email }
    products { bigint id PK
        bigint category_id FK
        string sku UK
        string name
        decimal purchase_price
        decimal selling_price
        int stock
        int minimum_stock
        datetime deleted_at }
    purchases { bigint id PK
        bigint supplier_id FK
        bigint user_id FK
        string purchase_number UK
        decimal total
        string status
        datetime purchased_at }
    purchase_items { bigint id PK
        bigint purchase_id FK
        bigint product_id FK
        int quantity
        decimal unit_cost
        decimal subtotal }
    sales { bigint id PK
        bigint user_id FK
        string sale_number UK
        decimal total
        string status
        datetime sold_at }
    sale_items { bigint id PK
        bigint sale_id FK
        bigint product_id FK
        int quantity
        decimal unit_price
        decimal unit_cost
        decimal subtotal }
```

### Penjelasan relasi Eloquent
- `hasMany` ada di sisi "satu" (Category → banyak Product); `belongsTo` di sisi "banyak" (Product → satu Category).
- **Foreign key berada di tabel detail/anak** (`products.category_id`, `sale_items.sale_id`) karena satu baris induk dipakai banyak baris anak; menyimpan banyak id di induk melanggar normalisasi.
- Daftar produk memakai `Product::with('category')` untuk menghindari N+1 query.
- Pesanan customer disimpan sebagai `Sale` yang dimiliki user dengan role customer (`sales.user_id`).

## Fitur tambahan: Laporan Laba & Estimasi Stok
- **Laporan laba** (`/reports`, khusus admin): omzet, modal, laba kotor, margin, laba per hari, produk paling menguntungkan, filter tanggal, dan ekspor CSV.
  Laba = subtotal penjualan - (qty x `unit_cost`). Kolom `sale_items.unit_cost` menyimpan harga modal *saat transaksi terjadi*
  (snapshot), sehingga laporan lama tidak berubah jika harga beli produk diubah kemudian.
- **Estimasi stok habis** (`/stock-forecast`, admin dan staff): perkiraan hari sampai stok habis = stok / rata-rata terjual per hari (30 hari terakhir),
  plus saran jumlah beli agar cukup 30 hari. Ini hitungan sederhana, bukan AI. Transaksi yang dibatalkan tidak dihitung.
- Setelah menyalin file baru ke project yang sudah berjalan, cukup jalankan `php artisan migrate` (tanpa `fresh`) agar data tidak hilang.

## 8. Known Limitations
- Penomoran transaksi memakai kode acak unik (bukan urut).
- Tidak ada laporan ekspor PDF/Excel (hanya CSV).
- Tidak ada pengembalian sebagian (partial return); hanya pembatalan penuh.
- Pesanan customer langsung selesai dan mengurangi stok; belum ada alur status (pending, dibayar, dikirim) maupun pencatatan metode pembayaran.
- Bootstrap dimuat via CDN sehingga perlu koneksi internet.
- Belum diuji beban pada transaksi paralel sungguhan (mekanisme `lockForUpdate` dijelaskan di bawah).

---

## Testing
```bash
php artisan test
```
Test memakai database SQLite in-memory bawaan `phpunit.xml` Laravel 11/12 (tidak menyentuh MySQL). Total: **45 test lulus**.

| Skenario (slide UTS) | Test |
|---|---|
| Guest tidak bisa buka dashboard; password salah ditolak | `AuthTest` |
| SKU duplikat & harga negatif ditolak | `ProductTest` |
| Pembelian menambah stok | `PurchaseTest::test_purchase_increases_stock` |
| Penjualan mengurangi stok; total dihitung server | `SaleTest::test_sale_decreases_stock...` |
| Stok tidak cukup ditolak tanpa mengubah data | `SaleTest::test_insufficient_stock...` |
| Atomicity / rollback jika satu item gagal | `SaleTest::test_failure_on_second_item_rolls_back_everything` |
| Staff tidak bisa akses fungsi Admin lewat URL | `AuthorizationTest` |
| Filter & pagination | `SearchTest` |
| Laporan laba, snapshot modal, filter tanggal, CSV, akses admin | `ReportTest` |
| Estimasi stok habis dan saran beli | `StockForecastTest` |
| Customer: register (role tidak bisa dipilih), akses dibatasi, tidak melihat harga modal, order mengurangi stok, stok kurang ditolak, tidak bisa lihat order orang lain, tidak bisa membatalkan | `CustomerTest` |

## Catatan untuk Code Ownership Interview
| Pertanyaan | Jawaban singkat & lokasi kode |
|---|---|
| Mengapa `Sale` hasMany `SaleItem`? | Satu transaksi berisi banyak item; FK `sale_id` ada di `sale_items`. `app/Models/Sale.php` |
| Di mana stok dijaga agar tidak negatif? | Cek `$product->stock < $qty` di `SaleService::create` + kolom `unsignedInteger` di migration. |
| Mengapa total dihitung di server? | Input browser bisa dimanipulasi. Harga dibaca dari tabel `products` di `SaleService`. |
| Risiko mengurangi stok di luar `DB::transaction`? | Bila langkah berikutnya gagal, stok sudah berubah tapi transaksi tidak tercatat (data tidak konsisten). |
| Mencegah Staff akses fungsi Admin lewat URL? | Middleware `can:admin` (Gate di `AppServiceProvider`) pada `routes/web.php` + `authorize()` di `ProductRequest`. |
| Dua penjualan hampir bersamaan pada produk yang sama? | `lockForUpdate()` mengunci baris produk; transaksi kedua menunggu lalu membaca stok terbaru. |
| SKU unik & peran constraint DB? | `Rule::unique` memberi pesan ramah; unique index di migration melindungi dari race condition. |
| Ubah aturan minimum stock? | Definisi "rendah" ada di `Product::isLowStock()` dan `whereColumn('stock','<=','minimum_stock')` (Dashboard & `lowStock`). |
| Bagaimana customer dicegah membuka halaman staff/admin? | Route staff/admin dibungkus `can:internal`, route toko dibungkus `can:customer` (Gate di `AppServiceProvider`), jadi dicek di server. `routes/web.php` |
| Mengapa role tidak boleh dipilih di form register? | Kalau role dikirim dari browser, orang bisa mendaftar sebagai admin. Role diisi `customer` oleh server di `RegisterController`. |
| Mengapa customer tidak boleh melihat harga modal? | Harga modal adalah data internal toko (menentukan laba). View toko hanya menampilkan harga jual. `ShopController` dan `resources/views/shop/` |
| Mengapa customer di `/dashboard` dialihkan? | `DashboardController::index` mengecek `can('customer')` lalu `redirect()->route('shop.index')`, karena dashboard berisi data internal. |

## Struktur kode
```
app/Models                  model + relasi Eloquent
app/Http/Controllers        Auth, Register, Dashboard, Category, Supplier, Product, Purchase, Sale, User, Report, StockForecast, Shop
app/Services                SaleService, PurchaseService  (business logic transaksi)
app/Providers               AppServiceProvider (Gate 'admin', 'internal', 'customer', pagination Bootstrap)
database/migrations         skema + FK + index
database/seeders            kategori, produk, pemasok, akun demo
routes/web.php              named routes + middleware auth / can:admin / can:internal / can:customer
resources/views             layout reusable + halaman Blade (termasuk shop/ dan auth/register)
tests/Feature               test scenario (45 test)
```

## Screenshot

### Dashboard
![Dashboard](docs/dashboard.png)
Ringkasan penjualan, jumlah produk, dan stok rendah. Semua angka dihitung dari database.

### Penjualan ditolak karena stok tidak cukup
![Stok tidak cukup](docs/stok-tidak-cukup.png)
Sistem menolak penjualan yang melebihi stok, dan data tidak berubah sama sekali.

### Staff ditolak mengakses halaman admin
![Staff ditolak 403](docs/staffditolak.png)
Staff yang membuka halaman admin lewat URL langsung mendapat 403.

### Hasil pengujian
![Hasil test](docs/hasiltest.png)
Hasil `php artisan test`: 45 test lulus.
