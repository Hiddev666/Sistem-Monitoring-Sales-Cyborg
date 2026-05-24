# Analisis Codebase Monitoring Sales Force

Tanggal analisis: 2026-05-22  
Project: Laravel 12, aplikasi monitoring aktivitas dan kinerja sales force.

## Ringkasan Eksekutif

Codebase sudah memiliki fondasi Laravel MVC yang cukup jelas: autentikasi, role middleware, master data user/klien/wilayah, PJP, absensi, visit form berbasis `jadwal_klien`, galeri foto, dashboard manager, dan analytics admin. Namun implementasinya belum sepenuhnya konsisten dengan PRD dan masih ada beberapa sambungan controller-model-view yang putus.

Masalah paling besar adalah inkonsistensi role dan database kunjungan:

- PRD dan sebagian kode memakai Spatie Permission, tetapi controller dashboard/API/analytics/test masih banyak mengakses kolom `users.role`, sementara migration `users` tidak membuat kolom tersebut.
- PRD mendesain tabel/model `kunjungan` dan `visit_form`, tetapi codebase tidak memiliki migration/model untuk keduanya. Data check-in, check-out, foto, dan visit form disimpan langsung di `jadwal_klien`.
- Beberapa view dan route yang dipanggil belum ada, terutama `sales.pjp.show` dan route export `admin.reports.*`.
- Fitur laporan export Excel/PDF dalam PRD belum benar-benar tersambung ke route/controller, dan dependency export yang diperlukan juga belum ada di `composer.json`.

## Hasil Verifikasi

Perintah yang dijalankan:

```bash
php artisan route:list
php artisan test
```

Hasil:

- `php artisan route:list` berhasil dan menampilkan 76 route. Artinya registrasi route dasar tidak crash.
- `php artisan test` gagal: 35 failed, 26 passed.
- Penyebab dominan kegagalan test adalah `SQLSTATE[42S22]: Unknown column 'role' in 'field list'`, karena test Phase 5/factory mencoba menyimpan atau membaca `users.role`, sedangkan schema memakai Spatie Permission tanpa kolom `role`.

## Kesesuaian Terhadap PRD

### Sudah Ada Sebagian

- F-01 Login, F-04 Logout: ada di `LoginController` dan route `/login`, `/logout`.
- F-02 Manajemen role: ada Spatie Permission, `RoleMiddleware`, `UserController`, dan tabel role/permission.
- F-03 Ganti password: ada `PasswordController`.
- F-05/F-06 Absensi masuk/pulang: ada `AbsensiController` dan view `sales.attendance.index`.
- F-08/F-09 PJP admin dan sales: ada `Admin\PJPController`, `SalesPJPController`, dan view `admin.pjp.*`, `sales.pjp.today`.
- F-11/F-30/F-31/F-32 master data user/klien/wilayah: ada controller, model, dan view.
- F-12/F-13 check-in dengan validasi GPS: ada di `SalesPJPController::checkInKlien` memakai `GpsValidationService`.
- F-14 foto bukti: ada di `VisitFormController` dan `PhotoService`.
- F-16/F-18 visit form: ada, tetapi disimpan ke `jadwal_klien`, bukan tabel `visit_form`.
- F-20/F-24 dashboard peta real-time: ada view `manager.dashboard` dan `LocationController`, tetapi query role bermasalah.
- F-33 konfigurasi radius: ada `ConfigurationController`, tetapi check-in memakai `config('sales.gps_tolerance', 100)` dan tidak membaca tabel `configurations`.

### Belum Sesuai / Belum Lengkap

- PRD menyebut tabel `kunjungan` dan `visit_form`; codebase tidak memiliki model/migration `Kunjungan` dan `VisitForm`.
- F-15 check-out kunjungan belum konsisten: `SalesPJPController::checkOutKlien` hanya menerima hasil/keterangan, sedangkan checkout GPS dan foto checkout diproses di visit form.
- F-19 riwayat kunjungan sales belum terlihat sebagai route/view khusus.
- F-21 pin klien warna abu-abu/hijau/kuning belum tampak lengkap; dashboard lebih fokus ke lokasi sales.
- F-22 deteksi tidak bergerak ada, tetapi logikanya tidak akurat karena `LokasiRealtime::updateOrCreate(['user_id' => ...])` hanya menyimpan satu row per user. `calculateNoMovement()` mengambil 2 lokasi terakhir, tetapi data historisnya selalu ditimpa.
- F-25 sampai F-29 laporan/export belum tersambung penuh. Ada `ReportService`, tetapi tidak ada route/controller export laporan Excel/PDF.
- PRD menyebut Yajra DataTables, Maatwebsite Excel, DomPDF, Leaflet; `composer.json` tidak memuat `yajra/laravel-datatables`, `maatwebsite/excel`, `barryvdh/laravel-dompdf`, atau `phpoffice/phpspreadsheet`.

## Controller, Model, View Yang Belum Terhubung / Janggal

### 1. `SalesPJPController::show()` Memanggil View Yang Tidak Ada

File:

- `app/Http/Controllers/SalesPJPController.php`
- `resources/views/sales/pjp/today.blade.php`

Controller memanggil:

```php
return view('sales.pjp.show', ...)
```

View `resources/views/sales/pjp/show.blade.php` tidak ada. Di view `sales.pjp.today`, tombol detail juga mengarah ke `route('sales.pjp.show', $jadwal->id)`. Akibatnya halaman detail PJP akan error `View [sales.pjp.show] not found`.

### 2. Analytics View Memanggil Route Export Yang Tidak Ada

File:

- `resources/views/admin/analytics/sales-performance.blade.php`
- `resources/views/admin/analytics/regional-performance.blade.php`
- `resources/views/admin/analytics/klien-analysis.blade.php`
- `routes/web.php`

View memanggil route:

```php
admin.reports.export-sales-performance
admin.reports.export-regional-performance
admin.reports.export-klien-analysis
```

Route tersebut tidak terdaftar di `routes/web.php`. Jika tombol export diklik, Laravel akan error `Route [admin.reports.*] not defined`.

### 3. `ReportService` Ada Tetapi Tidak Dipakai

File:

- `app/Services/ReportService.php`

Service berisi generator laporan Excel untuk sales performance, klien analysis, regional performance, dan detail kunjungan. Namun tidak ada controller yang meng-inject atau memanggil service ini, dan tidak ada route export laporan selain export ZIP foto.

Selain itu, service memakai namespace `PhpOffice\PhpSpreadsheet`, tetapi dependency `phpoffice/phpspreadsheet` tidak ada di `composer.json` atau `composer.lock`.

### 4. Query Role Salah Di Dashboard/API/Analytics/PhotoGallery

File terdampak:

- `app/Http/Controllers/Dashboard/ManagerDashboardController.php`
- `app/Http/Controllers/Dashboard/AnalyticsController.php`
- `app/Http/Controllers/Api/LocationController.php`
- `app/Http/Controllers/PhotoGalleryController.php`

Contoh pola bermasalah:

```php
User::where('role', 'sales')
$query->where('role', 'sales')
DB::table('users')->where('users.role', 'sales')
```

Schema tidak memiliki kolom `users.role`. Project memakai Spatie Permission, sehingga query seharusnya memakai `User::role('sales')`, `whereHas('roles', ...)`, atau join ke tabel `model_has_roles`/`roles`.

Dampak:

- Dashboard manager bisa gagal saat dibuka.
- API lokasi sales bisa gagal.
- Analytics admin bisa gagal.
- Test Phase 5 gagal massal.

### 5. `Kunjungan` Diimport Tetapi Model Tidak Ada

File:

- `app/Http/Controllers/SalesPJPController.php`

Ada import:

```php
use App\Models\Kunjungan;
```

Namun tidak ada file `app/Models/Kunjungan.php` dan tidak ada migration `kunjungan`. Import ini saat ini tidak dipakai, tetapi menunjukkan desain awal belum selesai atau sudah berubah tanpa dibersihkan.

### 6. PRD Tabel `visit_form` Tidak Ada

PRD mendesain tabel `visit_form` dengan relasi 1:1 ke `kunjungan`. Codebase tidak membuat tabel/model tersebut. Data form disimpan langsung ke kolom tambahan `jadwal_klien` lewat migration `2026_03_16_000008_add_visit_form_columns_to_jadwal_klien.php`.

Ini bukan bug fatal jika memang desain diubah, tetapi dokumentasi PRD dan implementasi sudah menyimpang. Jika PRD menjadi acuan skripsi/UAT, gap ini perlu diputuskan: mengikuti PRD dengan model `Kunjungan`/`VisitForm`, atau revisi PRD agar sesuai implementasi pivot-heavy sekarang.

### 7. `LokasiRealtime` Tidak Cocok Dengan Fitur Deteksi Tidak Bergerak

File:

- `app/Http/Controllers/Api/LocationController.php`
- `app/Models/LokasiRealtime.php`

`updateLocation()` memakai:

```php
LokasiRealtime::updateOrCreate(['user_id' => Auth::id()], ...)
```

Artinya hanya ada satu record lokasi per user. Tetapi `calculateNoMovement()` mengambil dua lokasi terakhir:

```php
LokasiRealtime::where('user_id', $userId)
    ->orderBy('recorded_at', 'desc')
    ->limit(2)
```

Karena lokasi lama ditimpa, fungsi ini hampir selalu tidak punya dua titik pembanding. F-22 deteksi tidak bergerak >60 menit tidak akan bekerja andal.

### 8. `getNextKlien()` Kurang Mengelompokkan Query Ke Jadwal Milik User

File:

- `app/Http/Controllers/SalesPJPController.php`

Query:

```php
$jadwal->jadwalKlien()
    ->where('status', 'active')
    ->orWhere('status', 'pending')
```

Karena `orWhere` tidak dikelompokkan, secara SQL ini berisiko mengambil `pending` dari jadwal lain jika constraint relation tidak terbungkus sesuai harapan. Lebih aman:

```php
->where(function ($q) {
    $q->where('status', 'active')
      ->orWhere('status', 'pending');
})
```

### 9. `VisitFormController::submitForm()` Tidak Menyimpan Tanda Tangan

File:

- `app/Http/Controllers/VisitFormController.php`
- `app/Models/JadwalKlien.php`

`JadwalKlien::isFormComplete()` mensyaratkan `tanda_tangan`, dan ada endpoint `uploadSignature()`. Namun `submitForm()` membangun `$formData` tanpa memasukkan `tanda_tangan`. `completeForm()` kemudian melakukan:

```php
$this->tanda_tangan = $data['tanda_tangan'] ?? null;
```

Jika tanda tangan sudah diupload sebelumnya, submit form dapat menghapus nilai `tanda_tangan` menjadi `null`. Ini membuat form yang sudah lengkap menjadi tidak lengkap menurut `isFormComplete()`.

### 10. Konfigurasi Radius Tidak Terhubung Ke Tabel Configuration

File:

- `app/Http/Controllers/Admin/ConfigurationController.php`
- `app/Http/Controllers/SalesPJPController.php`
- `app/Models/Configuration.php`

Admin bisa mengubah konfigurasi radius, tetapi check-in memakai:

```php
config('sales.gps_tolerance', 100)
```

Tidak ada file `config/sales.php`, dan nilai dari tabel `configurations` tidak dibaca di sini. Dampaknya F-33 tampak ada di UI, tetapi perubahan radius kemungkinan tidak mempengaruhi validasi check-in.

### 11. Manager Hanya Punya Dashboard, Belum Punya Laporan Sesuai PRD

Route manager saat ini hanya:

```php
GET /manager/dashboard
```

PRD mengharuskan manager melihat laporan performa, filter tanggal, export Excel/PDF. Implementasi analytics dan gallery saat ini berada di group admin, bukan manager.

### 12. `routes/api.php` Tidak Ada

Endpoint API didefinisikan di `routes/web.php` dengan prefix `/api`, bukan di `routes/api.php`. Ini masih bisa berjalan, tetapi endpoint API memakai session/web middleware, bukan stack API standar. Untuk web dashboard internal mungkin cukup, tetapi ini perlu dicatat karena PRD menyebut endpoint API dilindungi auth dan role.

### 13. Dependency PRD Belum Terpasang

PRD menyebut:

- Yajra DataTables
- Maatwebsite Laravel Excel
- Barryvdh DomPDF
- Leaflet.js

Codebase:

- `composer.json` hanya memuat Laravel, Tinker, dan Spatie Permission.
- View memakai DataTables/Leaflet kemungkinan via CDN, tetapi backend Yajra tidak ada.
- `ReportService` memakai PhpSpreadsheet, tetapi package tidak ada.
- Export PDF belum ada implementasi/dependency.

## Catatan Per Model

### `User`

Model sudah memakai `HasRoles` dari Spatie dan helper `isSales()`, `isManager()`, `isAdmin()`. Ini arah yang benar. Masalah muncul karena beberapa controller/test tidak memakai API Spatie dan malah mengakses `role` sebagai kolom.

### `JadwalKunjungan`

Berfungsi sebagai header jadwal harian per sales. Relasi ke `jadwal_klien` sudah ada. Status memakai bahasa Indonesia: `pending`, `aktif`, `selesai`.

Catatan: child `JadwalKlien` memakai status bahasa Inggris: `pending`, `active`, `completed`, `skipped`. Ini tidak salah secara teknis, tetapi mudah menyebabkan bug jika tidak didokumentasikan.

### `JadwalKlien`

Model ini menjadi pusat terlalu banyak tanggung jawab: detail PJP, status kunjungan, check-in GPS, check-out GPS, foto, tanda tangan, catatan, hasil, nominal transaksi, dan durasi.

Ini menyimpang dari PRD yang memisahkan `jadwal_klien`, `kunjungan`, dan `visit_form`. Jika aplikasi akan berkembang, model ini akan cepat sulit dirawat.

### `Absensi`

Model dan controller cukup tersambung. Namun PRD menyebut `total_jam` decimal jam, sedangkan migration dan model menghitungnya sebagai menit integer. UI juga memformat sebagai HH:MM dari menit. Ini perlu diselaraskan di PRD atau schema.

### `LokasiRealtime`

Model sesuai untuk snapshot lokasi terakhir, tetapi tidak cukup untuk histori pergerakan dan deteksi diam berbasis dua titik/lebih. Perlu tabel log lokasi atau ubah logika agar tidak membutuhkan history.

## Rekomendasi Prioritas Perbaikan

### Prioritas Tinggi

1. Konsolidasikan sistem role.
   - Hapus seluruh query `users.role`.
   - Ganti dengan `User::role('sales')` atau `whereHas('roles', ...)`.
   - Perbaiki test/factory Phase 5 agar assign role via Spatie, bukan mengisi kolom `role`.

2. Tambahkan view `resources/views/sales/pjp/show.blade.php` atau hapus route/tombol detail jika tidak diperlukan.

3. Putuskan desain data kunjungan.
   - Opsi A: ikuti PRD, buat model/migration `Kunjungan` dan `VisitForm`.
   - Opsi B: tetap pakai `jadwal_klien` sebagai visit record, lalu revisi PRD dan hapus import/konsep `Kunjungan`.

4. Sambungkan export laporan.
   - Buat route `admin.reports.*` atau ubah tombol view agar tidak mengarah ke route kosong.
   - Tambahkan dependency export yang benar.

5. Perbaiki `VisitFormController::submitForm()` agar tidak menghapus `tanda_tangan` yang sudah diupload.

### Prioritas Sedang

1. Hubungkan konfigurasi radius dari tabel `configurations` ke validasi GPS.
2. Perbaiki `LocationController` agar deteksi tidak bergerak punya histori lokasi atau gunakan strategi snapshot yang sesuai.
3. Tambahkan akses manager ke laporan sesuai PRD, bukan hanya admin.
4. Rapikan `ReportService` agar dependency dan route-nya jelas.
5. Kelompokkan query `orWhere` di `SalesPJPController::getNextKlien()`.

### Prioritas Rendah

1. Hapus import tidak terpakai seperti `App\Models\Kunjungan`.
2. Selaraskan istilah status: `aktif/selesai` vs `active/completed`.
3. Rapikan encoding PRD yang terlihat rusak pada karakter bullet dan tanda baca.
4. Tambahkan dokumentasi singkat desain aktual database jika memilih tetap memakai `jadwal_klien` sebagai visit/form record.

## Kesimpulan

Codebase sudah berjalan sampai level route registration dan sebagian fitur dasar lolos test, tetapi belum siap disebut sesuai PRD. Area yang paling perlu dibereskan adalah konsistensi role Spatie, model data kunjungan/visit form, route/view export laporan, dan wiring dashboard real-time.

Jika ingin mengejar fungsi aplikasi lebih cepat, pendekatan paling pragmatis adalah mempertahankan desain `jadwal_klien` sebagai record kunjungan, lalu perbaiki semua wiring yang putus dan revisi PRD agar konsisten. Jika PRD harus dipatuhi apa adanya, perlu refactor data layer dengan tabel `kunjungan` dan `visit_form` sebelum fitur laporan dan dashboard diperluas.
