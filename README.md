# MBACCO – Inventory & Sales Management System

Aplikasi manajemen stok dan penjualan berbasis **Laravel + MySQL** (UTS Web Programming 2 – Integrated Laravel Project).

## 1. Project Overview
Sistem untuk toko tembakau **MBACCO** : mengelola kategori, produk, pemasok, pembelian (stock-in), dan penjualan.
Stok berubah otomatis dan konsisten saat transaksi berhasil. Ada dua role: **Admin/Owner** dan **Staff**.

## 2. Requirements
- PHP >= 8.2 (ekstensi: mbstring, xml, pdo_mysql, ctype, json)
- Composer 2
- MySQL / MariaDB
- Git
- Node.js/npm **tidak diperlukan**: tampilan memakai Bootstrap 5 via CDN (butuh koneksi internet saat membuka halaman)

## 3. Installation
```bash
git clone https://github.com/sayedkaysan11/mbacco-inventory-sales.git
cd mbacco-inventory-sales
composer install
cp .env.example .env
php artisan key:generate
```
Di Windows (Command Prompt), ganti `cp .env.example .env` dengan `copy .env.example .env`.

## 4. Environment Setup
1. Buat database kosong di MySQL, misalnya bernama `inventory_sales`.
2. Buka file `.env`, lalu atur koneksi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_sales
DB_USERNAME=root
DB_PASSWORD=
```
File `.env` tidak di-commit ke Git. Yang di-commit hanya `.env.example`.

## 5. Migration & Seeding
```bash
php artisan migrate --seed      # atau: php artisan migrate:fresh --seed
php artisan serve
```
Buka http://127.0.0.1:8000 lalu login memakai akun demo di bawah.

## 6. Demo Account
| Role  | Email             | Password      |
|-------|-------------------|---------------|
| Admin | admin@example.com | password123   |
| Staff | staff@example.com | password123   |

(Akun fiktif untuk demo — bukan kredensial asli.)

## 7. Features
| Modul | Admin | Staff |
|---|---|---|
| Login / logout, password hash, route terlindungi | ✔ | ✔ |
| Kategori, Pemasok (CRUD) | ✔ | ✘ |
| Produk (CRUD) | ✔ | hanya lihat |
| Pengguna & role | ✔ | ✘ |
| Pembelian / stock-in (multi item) | ✔ semua | ✔ miliknya |
| Penjualan (multi item) | ✔ semua | ✔ miliknya |
| Pembatalan transaksi (stok dipulihkan) | ✔ | ✘ |
| Dashboard (agregat dari DB), daftar stok rendah | ✔ | ✔ (angka miliknya) |
| Search SKU/nama, filter kategori/tanggal/status, pagination | ✔ | ✔ |

Business rules yang diterapkan:
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
        decimal subtotal }
```

### Penjelasan relasi Eloquent
- `hasMany` ada di sisi "satu" (Category → banyak Product); `belongsTo` di sisi "banyak" (Product → satu Category).
- **Foreign key berada di tabel detail/anak** (`products.category_id`, `sale_items.sale_id`) karena satu baris induk dipakai banyak baris anak; menyimpan banyak id di induk melanggar normalisasi.
- Daftar produk memakai `Product::with('category')` untuk menghindari N+1 query.

## 8. Known Limitations
- Penomoran transaksi memakai kode acak unik (bukan urut).
- Tidak ada laporan ekspor (PDF/Excel).
- Tidak ada pengembalian sebagian (partial return); hanya pembatalan penuh.
- Bootstrap dimuat via CDN sehingga perlu koneksi internet.
- Belum diuji beban pada transaksi paralel sungguhan (mekanisme `lockForUpdate` dijelaskan di bawah).

---

## Testing
```bash
php artisan test
```
Test memakai database SQLite in-memory bawaan `phpunit.xml` Laravel 11/12 (tidak menyentuh MySQL).

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

> > Bukti hasil pengujian (`php artisan test`) dan screenshot halaman ada di folder `docs/`.

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

## Struktur kode
```
app/Models                  8 model + relasi Eloquent
app/Http/Controllers        Auth, Dashboard, Category, Supplier, Product, Purchase, Sale, User
app/Http/Requests           ProductRequest, SaleRequest, PurchaseRequest
app/Services                SaleService, PurchaseService  (business logic transaksi)
app/Providers               AppServiceProvider (Gate 'admin', pagination Bootstrap)
database/migrations         skema + FK + index
database/seeders            kategori, produk, pemasok, akun demo
routes/web.php              named routes + middleware auth / can:admin
resources/views             layout reusable + halaman Blade
tests/Feature               test scenario minimum
```
