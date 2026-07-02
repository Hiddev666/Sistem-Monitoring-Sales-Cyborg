# Planning Implementasi: Pembuatan Jadwal oleh Sales

## 1. Ringkasan Perubahan

Saat ini hanya **admin** yang bisa membuat PJP/Jadwal Kunjungan (`Admin\PJPController`).  
Sales hanya bisa mengeksekusi jadwal yang sudah dibuatkan oleh admin.

**Tujuan:**  
- Sales dapat membuat jadwal kunjungannya sendiri melalui menu baru di akun sales.
- Menu pembuatan PJP di admin dapat disembunyikan/ditampilkan via permission.
- Fleksibilitas: admin masih bisa membuatkan jadwal untuk sales jika permission `create_pjp` diaktifkan.

---

## 2. Perubahan Database / Permission

### 2.1 Permission Baru

| Permission | Deskripsi | Diberikan ke Role |
|---|---|---|
| `create_pjp_self` | Sales membuat jadwal kunjungan sendiri | `sales` |
| `create_pjp` (existing) | Admin membuat jadwal untuk sales mana pun | `admin` (existing) |

Tidak ada perubahan schema database. Tabel `jadwal_kunjungan` sudah memiliki kolom:
- `user_id` -> sales yg ditugaskan
- `created_by` -> user yg membuat jadwal (bisa admin atau sales sendiri)

### 2.2 File yang Diubah

#### `database/seeders/RoleSeeder.php`
- Tambah permission `create_pjp_self` ke daftar permissions.
- Berikan `create_pjp_self` ke role `sales`.

---

## 3. Controller Baru

### 3.1 `app/Http/Controllers/SalesPJPController.php` (tambah method baru)

Method baru:

| Method | Fungsi |
|---|---|
| `create()` | Tampilkan form pembuatan jadwal untuk sales |
| `store(Request $request)` | Simpan jadwal baru (user_id = Auth::id(), created_by = Auth::id()) |

**Detail `create()`:**
- Ambil daftar klien aktif (milik wilayah sales atau semua klien aktif).
- Tampilkan view `sales.pjp.create`.

**Detail `store()`:**
- Validasi: `tanggal` (required, date, after:yesterday), `keterangan` (nullable), `klien` (required, array, min:1).
- Set `user_id` = `Auth::id()` (sales yang login).
- Set `created_by` = `Auth::id()`.
- Cek duplikat (satu jadwal per user per tanggal) via `ensureScheduleDoesNotExist()`.
- Buat `JadwalKunjungan` + `JadwalKlien`.

### 3.2 Alternatif: Controller Terpisah

Bisa juga buat controller baru `app/Http/Controllers/Sales/ScheduleController.php`  
untuk menjaga separation of concern, tapi method di `SalesPJPController` sudah cukup.

---

## 4. Routes Baru

### `routes/web.php`

Di dalam group `Route::middleware('role:sales')...sales.`:

```php
// PJP Creation (Self-Service)
Route::middleware('permission:create_pjp_self')->group(function () {
    Route::get('/pjp/create', [SalesPJPController::class, 'create'])->name('pjp.create');
    Route::post('/pjp', [SalesPJPController::class, 'store'])->name('pjp.store');
});
```

Letakkan setelah route PJP existing di group sales.

### Rute yang Ada (tidak diubah):

```php
Route::prefix('pjp')->name('pjp.')->group(function () {
    Route::get('/today', [SalesPJPController::class, 'today'])->name('today');
    Route::get('/{jadwal}', [SalesPJPController::class, 'show'])->name('show');
    // ... dst
});
```

---

## 5. View Baru

### `resources/views/sales/pjp/create.blade.php`

- Extends `layouts.sales`.
- Form pembuatan jadwal **tanpa** pilihan user/sales (karena user adalah sales yang login).
- Field: tanggal, keterangan, pilih klien (multi-select, urut).
- Mirip dengan `admin.pjp.create` tapi:
  - Hilangkan dropdown user/sales.
  - Keterangan dibuat lebih sederhana.
  - Tanggal default: besok.
  - Submit ke `route('sales.pjp.store')`.

---

## 6. Perubahan Navigasi / Layout

### 6.1 `resources/views/layouts/sales.blade.php`

Tambah item navigasi baru di bottom nav (setelah Jadwal atau di menu Beranda):

```blade
@can('create_pjp_self')
    <a href="{{ route('sales.pjp.create') }}" 
       class="sales-nav-link {{ request()->routeIs('sales.pjp.create') ? 'active' : '' }}">
        <i class="fas fa-plus-circle"></i>
        <span>Buat Jadwal</span>
    </a>
@endcan
```

Atau alternatif: tambahkan tombol "Buat Jadwal Baru" di halaman `sales.pjp.today` / `sales.pjp.no-schedule`  
dengan permission check `@can('create_pjp_self')`.

### 6.2 `resources/views/layouts/app.blade.php` (Admin Sidebar)

Ubah kondisi penampilan menu **Penjadwalan & Absensi** dari `@if($isAdmin)` menjadi:

```blade
@if($isAdmin || $authUser?->can('create_pjp'))
```

Atau gunakan permission `create_pjp` langsung untuk mengontrol visibilitas menu PJP:

```blade
@can('create_pjp')
    <div class="sidebar-section">
        ...
    </div>
@endcan
```

### 6.3 `resources/views/admin/pjp/index.blade.php`

Tombol "Buat Jadwal Baru" di-gate dengan permission:

```blade
@can('create_pjp')
    <a href="{{ route('admin.pjp.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Buat Jadwal Baru
    </a>
@endcan
```

### 6.4 `resources/views/sales/pjp/no-schedule.blade.php`

Tambah tombol/tautan ke form pembuatan jadwal jika sales punya permission `create_pjp_self`:

```blade
@can('create_pjp_self')
    <div class="d-grid gap-2 mt-3">
        <a href="{{ route('sales.pjp.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Buat Jadwal Sendiri
        </a>
    </div>
@endcan
```

---

## 7. Daftar File yang Diubah/Dibuat

### File Baru:
1. `resources/views/sales/pjp/create.blade.php` — Form pembuatan jadwal oleh sales.

### File Diubah:
1. **`database/seeders/RoleSeeder.php`** — Tambah permission `create_pjp_self`, berikan ke role sales.
2. **`app/Http/Controllers/SalesPJPController.php`** — Tambah method `create()` dan `store()`.
3. **`routes/web.php`** — Tambah route `sales.pjp.create` dan `sales.pjp.store`.
4. **`resources/views/layouts/sales.blade.php`** — Tambah navigasi "Buat Jadwal".
5. **`resources/views/layouts/app.blade.php`** — Ganti kondisi sidebar PJP dari `@if($isAdmin)` ke `@can('create_pjp')`.
6. **`resources/views/admin/pjp/index.blade.php`** — Gate tombol "Buat Jadwal Baru" dengan `@can('create_pjp')`.
7. **`resources/views/sales/pjp/no-schedule.blade.php`** — Tambah tombol "Buat Jadwal Sendiri" (jika ada permission).

---

## 8. Alur Kerja Baru

### Alur Sales Membuat Jadwal:
1. Sales masuk ke menu **Buat Jadwal** (dari bottom nav atau tombol di halaman jadwal).
2. Form: pilih tanggal, tulis keterangan (opsional), pilih klien dalam urutan kunjungan.
3. Submit → `SalesPJPController@store` → validasi → simpan.
4. Redirect ke `sales.pjp.today` atau `sales.pjp.show` dengan pesan sukses.

### Alur Admin (dengan permission `create_pjp` aktif):
1. Admin bisa melihat menu PJP di sidebar.
2. Admin bisa membuat jadwal untuk sales mana pun (seperti sekarang).
3. Tanpa permission `create_pjp`, menu PJP di sidebar admin tersembunyi.

---

## 9. Catatan Penting

1. **Unique constraint**: Satu user hanya boleh memiliki satu jadwal per tanggal.  
   Validasi ini sudah ada di `PJPController@ensureScheduleDoesNotExist`.  
   Harus dipakai juga di method `store()` baru milik sales.

2. **Edit/Hapus jadwal oleh sales**: Tidak termasuk dalam scope ini. Sales hanya bisa membuat jadwal baru.  
   Jika nanti diperlukan, tinggal tambah permission `edit_pjp_self` / `delete_pjp_self`.

3. **Wilayah klien**: Saat sales membuat jadwal, daftar klien bisa difilter berdasarkan `wilayah_id` sales  
   (sales punya field `wilayah_id`).  
   Atau bisa ditampilkan semua klien aktif — tergantung kebutuhan.

4. **GPS check-in tetap sama**: Tidak ada perubahan pada alur eksekusi kunjungan.

---

## 10. Urutan Implementasi

1. **RoleSeeder**: Tambah permission `create_pjp_self`, assign ke sales. Jalankan `php artisan db:seed --class=RoleSeeder`.
2. **Routes**: Tambah route baru `sales.pjp.create` dan `sales.pjp.store`.
3. **Controller**: Tambah method `create()` dan `store()` di `SalesPJPController`.
4. **View**: Buat `sales.pjp.create.blade.php`.
5. **Layout Sales**: Tambah navigasi "Buat Jadwal" di `sales.blade.php`.
6. **Layout Admin**: Ubah kondisi sidebar PJP dari `@if($isAdmin)` ke `@can('create_pjp')`.
7. **Admin Index**: Gate tombol "Buat Jadwal Baru".
8. **No-Schedule View**: Tambah tombol "Buat Jadwal Sendiri".
9. **Uji coba**: Login sebagai sales → buka menu Buat Jadwal → buat jadwal → cek di today/show.
