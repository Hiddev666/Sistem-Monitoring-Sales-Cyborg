# Planning Perbaikan Codebase Monitoring Sales Force

Tanggal dibuat: 2026-05-22  
Sumber analisis: `analyzed-codebase.md`  
Target project: Laravel 12, aplikasi monitoring aktivitas dan kinerja sales force.

## Tujuan

Dokumen ini menjadi rencana kerja untuk memperbaiki temuan pada `analyzed-codebase.md`. Fokus utama adalah membuat aplikasi kembali konsisten secara teknis, menghilangkan error fatal, menyambungkan fitur yang putus, dan menyelaraskan implementasi dengan PRD atau mendokumentasikan keputusan ketika implementasi sengaja berbeda dari PRD.

## Strategi Umum

Pendekatan yang paling pragmatis adalah:

1. Stabilkan aplikasi dan test terlebih dahulu.
2. Konsolidasikan role agar seluruh kode memakai Spatie Permission.
3. Perbaiki route, view, dan controller yang saat ini putus.
4. Putuskan desain data kunjungan sebelum memperluas laporan dan dashboard.
5. Sambungkan fitur PRD yang sudah setengah jadi.
6. Rapikan dependency, dokumentasi, dan istilah domain.

Keputusan desain yang perlu dibuat sejak awal:

- Jika target utama adalah aplikasi cepat stabil, pertahankan `jadwal_klien` sebagai record kunjungan dan visit form, lalu revisi PRD/dokumentasi agar sesuai.
- Jika target utama adalah kepatuhan PRD apa adanya, tambahkan tabel/model `kunjungan` dan `visit_form`, lalu refactor flow check-in, check-out, foto, form, dan laporan untuk memakai tabel tersebut.

Rekomendasi awal: pilih opsi pragmatis, yaitu mempertahankan `jadwal_klien` sebagai sumber data kunjungan, karena codebase saat ini sudah banyak dibangun ke arah itu.

## Fase 0 - Baseline Dan Pengamanan

Estimasi: 0.5 hari  
Prioritas: Wajib sebelum perubahan besar

### Task

1. Jalankan baseline:
   - `php artisan route:list`
   - `php artisan test`
   - `php artisan migrate:status`
2. Catat jumlah test gagal dan error utama sebelum perbaikan.
3. Pastikan `.env` lokal dan database test tidak bercampur dengan data penting.
4. Buat daftar file yang akan disentuh untuk menghindari perubahan melebar.

### Output

- Catatan baseline test dan route.
- Daftar area file yang akan diperbaiki.

### Acceptance Criteria

- Kondisi awal terdokumentasi.
- Tidak ada perubahan kode sebelum baseline diketahui.

### Status Fase 0 - Selesai 2026-05-22

Baseline sudah dijalankan pada environment lokal `D:\cyborg\sistem-sales`.

#### Hasil Command

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php artisan route:list` | Berhasil | Route registration tidak crash. Laravel menampilkan 76 route. |
| `php artisan migrate:status` | Berhasil | Semua migration yang tersedia sudah berstatus `Ran`, batch 1. |
| `php artisan test` | Gagal | 35 failed, 26 passed, 61 assertions, durasi sekitar 52.78 detik. |

#### Error Utama Baseline

Penyebab dominan kegagalan test:

```text
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'role' in 'field list'
```

Contoh query gagal berasal dari test Phase 5 yang membuat user dengan payload seperti:

```php
User::factory()->create(['role' => 'sales']);
```

Schema `users` tidak memiliki kolom `role`, sedangkan aplikasi memakai Spatie Permission. Ini mengonfirmasi bahwa Fase 1 harus dimulai dari factory/test dan seluruh query controller yang masih memakai role lama.

#### Area File Yang Perlu Disentuh Pada Fase Berikutnya

Area role lama yang ditemukan:

- `app/Http/Controllers/Dashboard/AnalyticsController.php`
- `app/Http/Controllers/Dashboard/ManagerDashboardController.php`
- `app/Http/Controllers/Api/LocationController.php`
- `app/Http/Controllers/PhotoGalleryController.php`
- `app/Services/ReportService.php`
- `tests/Unit/Phase5/LokasiRealtimeTest.php`
- `tests/Feature/Phase5/LocationControllerTest.php`
- `tests/Feature/Phase5/Phase5IntegrationTest.php`

Area route/view putus yang sudah terkonfirmasi untuk Fase 2:

- `app/Http/Controllers/SalesPJPController.php`
- `resources/views/sales/pjp/today.blade.php`
- `resources/views/sales/pjp/show.blade.php`
- `resources/views/admin/analytics/klien-analysis.blade.php`
- `resources/views/admin/analytics/regional-performance.blade.php`
- `resources/views/admin/analytics/sales-performance.blade.php`

Catatan: belum ada perubahan kode aplikasi pada Fase 0. Perubahan yang dilakukan hanya dokumentasi baseline di file ini.

## Fase 1 - Konsolidasi Role Spatie

Estimasi: 1-2 hari  
Prioritas: Tinggi  
Dampak: Menghilangkan error dominan `Unknown column 'role'`.

### Masalah

Schema `users` tidak memiliki kolom `role`, tetapi beberapa controller, API, analytics, dan test masih memakai `users.role`. Project sudah memakai Spatie Permission melalui `HasRoles`.

### File Terdampak

- `app/Http/Controllers/Dashboard/ManagerDashboardController.php`
- `app/Http/Controllers/Dashboard/AnalyticsController.php`
- `app/Http/Controllers/Api/LocationController.php`
- `app/Http/Controllers/PhotoGalleryController.php`
- `database/factories/UserFactory.php`
- Test Phase 5 atau test lain yang mengisi/membaca `users.role`

### Task

1. Cari seluruh referensi role lama:
   - `rg "where\\('role'|users\\.role|->role|\\['role'\\]|role =>" app database tests resources routes`
2. Ganti query model Eloquent:
   - Dari `User::where('role', 'sales')`
   - Menjadi `User::role('sales')` atau `User::whereHas('roles', ...)`
3. Ganti query query-builder/manual join:
   - Gunakan join ke `model_has_roles` dan `roles`.
   - Atau pindahkan ke query Eloquent jika lebih sederhana.
4. Perbaiki factory/test:
   - Jangan mengisi kolom `role`.
   - Assign role memakai `$user->assignRole('sales')`.
   - Pastikan role dibuat di seeder/test setup sebelum assign.
5. Pastikan helper `isSales()`, `isManager()`, dan `isAdmin()` tetap memakai Spatie.
6. Jalankan test relevan dan full test.

### Acceptance Criteria

- Tidak ada referensi query ke kolom `users.role`.
- `php artisan test` tidak lagi gagal karena `Unknown column 'role'`.
- Dashboard, API lokasi, analytics, dan gallery dapat mengambil user berdasarkan role.

### Status Fase 1 - Selesai 2026-05-22

Perbaikan role Spatie sudah dikerjakan.

#### Perubahan Yang Dilakukan

1. Mengganti query role lama dari kolom `users.role` ke Spatie Permission:
   - `User::role('sales')`
   - `$query->role('sales')` pada relasi user
   - join `model_has_roles` dan `roles` untuk query `DB::table('users')`
2. Mengubah test Phase 5 agar tidak lagi membuat user dengan `User::factory()->create(['role' => ...])`.
3. Menambahkan helper test `createUserWithRole()` pada test Phase 5 untuk membuat user lalu memanggil `assignRole()`.
4. Menambahkan wiring factory/model dasar yang terbuka setelah error role hilang:
   - `LokasiRealtime` memakai tabel eksplisit `lokasi_realtime`.
   - Model `Absensi`, `Klien`, `JadwalKunjungan`, dan `JadwalKlien` memakai `HasFactory`.
   - Factory baru: `AbsensiFactory`, `WilayahFactory`, `KlienFactory`, `JadwalKunjunganFactory`, `JadwalKlienFactory`.

#### File Yang Diubah

- `app/Http/Controllers/Dashboard/ManagerDashboardController.php`
- `app/Http/Controllers/Dashboard/AnalyticsController.php`
- `app/Http/Controllers/Api/LocationController.php`
- `app/Http/Controllers/PhotoGalleryController.php`
- `app/Services/ReportService.php`
- `app/Models/LokasiRealtime.php`
- `app/Models/Absensi.php`
- `app/Models/Klien.php`
- `app/Models/JadwalKunjungan.php`
- `app/Models/JadwalKlien.php`
- `tests/Unit/Phase5/LokasiRealtimeTest.php`
- `tests/Feature/Phase5/LocationControllerTest.php`
- `tests/Feature/Phase5/Phase5IntegrationTest.php`
- `database/factories/AbsensiFactory.php`
- `database/factories/WilayahFactory.php`
- `database/factories/KlienFactory.php`
- `database/factories/JadwalKunjunganFactory.php`
- `database/factories/JadwalKlienFactory.php`

#### Verifikasi

Scan referensi role lama:

- `rg "users\.role" app database tests resources routes`: tidak ada hasil.
- `rg "where\('role'" app database tests resources routes`: tidak ada hasil.
- `rg "'role' =>" app database tests resources routes`: hanya tersisa validasi/form admin role berbasis Spatie, bukan insert kolom `users.role`.

Test:

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php artisan test tests\Unit\Phase5\LokasiRealtimeTest.php tests\Feature\Phase5\LocationControllerTest.php tests\Feature\Phase5\Phase5IntegrationTest.php` | Gagal parsial | 27 passed, 8 failed. Tidak ada lagi error `Unknown column 'role'`. |
| `php artisan test` | Gagal parsial | 53 passed, 8 failed, 150 assertions, durasi sekitar 44.50 detik. Tidak ada lagi error `Unknown column 'role'`. |

#### Sisa Gagal Di Luar Scope Fase 1

Sisa kegagalan test sudah bergeser ke behavior lain:

1. `LokasiRealtimeTest` masih mengharapkan database melempar exception untuk latitude/longitude di luar range, tetapi migration belum memiliki check constraint.
2. `LokasiRealtimeTest` mengharapkan cascade delete saat user dihapus, tetapi `User` memakai soft delete sehingga row lokasi tidak ikut terhapus.
3. `LocationControllerTest` untuk no movement masih gagal karena kalkulasi menghasilkan menit negatif dan status idle tidak terdeteksi benar. Ini selaras dengan Fase 6.
4. `Phase5IntegrationTest` mengharapkan manager bisa membuka `/admin/analytics/dashboard`, tetapi route tersebut berada di area admin dan mengembalikan 403. Ini selaras dengan Fase 8.
5. `Phase5IntegrationTest` membandingkan `waktu_masuk` sebagai timestamp penuh, sedangkan kolom database menyimpan format time `H:i:s`.

## Fase 2 - Perbaikan Route/View Yang Putus

Estimasi: 0.5-1 hari  
Prioritas: Tinggi

### Masalah

Ada route/view yang dipanggil tetapi belum tersedia.

### File Terdampak

- `app/Http/Controllers/SalesPJPController.php`
- `resources/views/sales/pjp/today.blade.php`
- `resources/views/sales/pjp/show.blade.php`
- `resources/views/admin/analytics/*.blade.php`
- `routes/web.php`

### Task

1. Buat `resources/views/sales/pjp/show.blade.php`.
2. Isi halaman detail PJP minimal dengan:
   - Data klien.
   - Status kunjungan.
   - Lokasi check-in/check-out jika ada.
   - Foto bukti jika ada.
   - Hasil kunjungan dan catatan.
   - Tombol aksi yang sesuai status.
3. Pastikan route `sales.pjp.show` sudah mengirim data yang dibutuhkan view.
4. Untuk tombol export analytics:
   - Sementara sembunyikan tombol jika export belum tersedia, atau
   - Sambungkan route placeholder yang mengembalikan pesan jelas.
5. Jalankan `php artisan route:list` dan buka halaman terkait secara manual.

### Acceptance Criteria

- Klik detail PJP tidak menghasilkan error `View [sales.pjp.show] not found`.
- Analytics tidak memanggil route export yang belum terdaftar.
- Tidak ada route name yang hilang saat render view utama.

### Status Fase 2 - Selesai 2026-05-22

Perbaikan route/view yang putus sudah dikerjakan.

#### Perubahan Yang Dilakukan

1. Menambahkan view `resources/views/sales/pjp/show.blade.php`.
2. Halaman detail PJP sekarang menampilkan:
   - Ringkasan status jadwal.
   - Total klien, klien selesai, dan progress.
   - Tombol mulai/selesaikan perjalanan sesuai status jadwal.
   - Tabel detail klien, kontak, status, check-in/check-out, dokumentasi, hasil, catatan, dan nominal.
   - Link arah Google Maps dan link form kunjungan.
3. Menghapus pemanggilan route export analytics yang belum terdaftar:
   - `admin.reports.export-sales-performance`
   - `admin.reports.export-regional-performance`
   - `admin.reports.export-klien-analysis`
4. Tombol export analytics sementara dibuat disabled dengan label `Download Excel Belum Tersedia`.
5. Membetulkan beberapa key view analytics agar sesuai dengan data dari controller:
   - `regional-performance`: memakai `nama_wilayah`.
   - `klien-analysis`: memakai `nama_klien`.
   - `sales-performance`: filter wilayah memakai variable `$wilayah` dari controller, bukan `$salesReps`.

#### File Yang Diubah

- `resources/views/sales/pjp/show.blade.php`
- `resources/views/admin/analytics/sales-performance.blade.php`
- `resources/views/admin/analytics/regional-performance.blade.php`
- `resources/views/admin/analytics/klien-analysis.blade.php`

#### Verifikasi

| Command | Status | Ringkasan |
| --- | --- | --- |
| `rg "admin\.reports\.export|sales\.pjp\.show" resources app routes tests` | Berhasil | Route export kosong tidak ditemukan lagi. `sales.pjp.show` hanya tersisa di controller dan tombol detail yang valid. |
| `php artisan route:list` | Berhasil | Route registration tetap berhasil, 76 route. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali untuk environment development. |
| `php artisan test` | Gagal parsial | Tetap 53 passed, 8 failed. Tidak ada regresi jumlah test gagal dari akhir Fase 1. |

#### Sisa Catatan

Fitur export laporan belum diimplementasikan pada fase ini. Tombol export sengaja dibuat nonaktif sampai Fase 7 agar aplikasi tidak memanggil route palsu.

## Fase 3 - Keputusan Dan Konsistensi Data Kunjungan

Estimasi: 1-3 hari, tergantung opsi  
Prioritas: Tinggi

### Masalah

PRD mendesain `kunjungan` dan `visit_form`, tetapi implementasi menyimpan data kunjungan dan form langsung di `jadwal_klien`.

### Opsi A - Pertahankan Desain Saat Ini

Rekomendasi untuk stabilisasi cepat.

#### Task

1. Tetapkan `jadwal_klien` sebagai record kunjungan aktual.
2. Hapus import tidak terpakai `App\Models\Kunjungan`.
3. Tambahkan dokumentasi singkat bahwa `jadwal_klien` menampung:
   - Jadwal klien.
   - Check-in/check-out.
   - Foto kunjungan.
   - Visit form.
4. Revisi narasi teknis/PRD internal agar tidak menyebut tabel yang tidak ada sebagai implementasi aktual.
5. Pastikan `ReportService` dan dashboard membaca dari `jadwal_klien`.

#### Acceptance Criteria

- Tidak ada referensi model `Kunjungan` yang tidak ada.
- Dokumentasi data flow kunjungan sesuai schema aktual.
- Laporan dan controller memakai sumber data yang sama.

#### Status Opsi A - Selesai 2026-05-22

Keputusan desain Fase 3 ditetapkan memakai Opsi A: mempertahankan `jadwal_klien` sebagai record kunjungan aktual.

Perubahan yang dilakukan:

1. Menghapus import tidak terpakai `App\Models\Kunjungan` dari `SalesPJPController`.
2. Menambahkan dokumentasi desain aktual di `DATA_FLOW_KUNJUNGAN.md`.
3. Mendokumentasikan bahwa:
   - `jadwal_kunjungan` adalah header jadwal harian sales.
   - `jadwal_klien` adalah detail klien sekaligus record kunjungan aktual.
   - Check-in, check-out, GPS, foto, tanda tangan, visit form, hasil, catatan, nominal transaksi, dan waktu form selesai disimpan di `jadwal_klien`.
   - Tidak ada model/tabel `Kunjungan` dan `VisitForm` pada implementasi aktual.
4. Memastikan controller, gallery, analytics, dan report tetap membaca data kunjungan dari `jadwal_klien`.

File yang diubah/ditambahkan:

- `app/Http/Controllers/SalesPJPController.php`
- `DATA_FLOW_KUNJUNGAN.md`

Verifikasi:

| Command | Status | Ringkasan |
| --- | --- | --- |
| `rg "(^|[^A-Za-z0-9_])Kunjungan::|App\\Models\\Kunjungan|new Kunjungan" app database routes resources tests` | Berhasil | Tidak ada referensi exact ke model `Kunjungan` yang tidak ada. |
| `php artisan route:list` | Berhasil | Route registration tetap berhasil, 76 route. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali. |
| `php artisan test` | Gagal parsial | Tetap 53 passed, 8 failed. Tidak ada regresi dari akhir Fase 2. |

Sisa gap PRD terkait tabel `kunjungan` dan `visit_form` sekarang dianggap keputusan sadar, bukan wiring yang putus. Jika PRD formal harus sama dengan implementasi, bagian schema PRD perlu direvisi agar mengikuti `DATA_FLOW_KUNJUNGAN.md`.

### Opsi B - Ikuti PRD Apa Adanya

Dipilih hanya jika tabel `kunjungan` dan `visit_form` wajib ada untuk UAT/skripsi.

#### Task

1. Buat migration dan model:
   - `kunjungan`
   - `visit_form`
2. Pindahkan field check-in/check-out dari `jadwal_klien` ke `kunjungan`.
3. Pindahkan field form/foto/tanda tangan ke `visit_form`.
4. Refactor:
   - `SalesPJPController`
   - `VisitFormController`
   - `PhotoGalleryController`
   - `ReportService`
   - Analytics/dashboard.
5. Buat migrasi data jika sudah ada data lama.

#### Acceptance Criteria

- Schema sesuai PRD.
- Flow check-in, visit form, gallery, dan laporan memakai model baru.
- Test fitur kunjungan diperbarui.

## Fase 4 - Perbaikan Visit Form Dan Checkout

Estimasi: 0.5-1 hari  
Prioritas: Tinggi

### Masalah

`VisitFormController::submitForm()` dapat menghapus `tanda_tangan` yang sebelumnya sudah diupload, sehingga form menjadi tidak lengkap.

### File Terdampak

- `app/Http/Controllers/VisitFormController.php`
- `app/Models/JadwalKlien.php`
- View visit form terkait

### Task

1. Ubah `submitForm()` agar mempertahankan tanda tangan lama jika tidak ada input tanda tangan baru.
2. Pastikan `completeForm()` tidak menimpa `tanda_tangan` menjadi `null` saat data tidak dikirim.
3. Validasi urutan flow:
   - Upload foto.
   - Upload tanda tangan.
   - Submit form.
   - Check-out.
4. Tambahkan atau perbarui test untuk kasus:
   - Signature sudah ada lalu submit form.
   - Form tanpa signature tetap dianggap belum lengkap.
5. Samakan perilaku checkout GPS/foto agar tidak tersebar membingungkan antara `SalesPJPController` dan `VisitFormController`.

### Acceptance Criteria

- Tanda tangan tidak hilang setelah submit form.
- `isFormComplete()` memberi hasil benar.
- Check-out tetap dapat dilakukan sesuai aturan aplikasi.

### Status Fase 4 - Selesai 2026-05-22

Perbaikan visit form dan tanda tangan sudah dikerjakan.

#### Perubahan Yang Dilakukan

1. `JadwalKlien::completeForm()` tidak lagi menimpa field yang tidak dikirim menjadi `null`.
   - `tanda_tangan` yang sudah diupload sekarang dipertahankan.
   - Field form lain juga mempertahankan nilai lama jika tidak ada data baru.
2. `VisitFormController::submitForm()` sekarang menolak submit jika tanda tangan digital belum ada.
3. `VisitFormController::submitForm()` mengirim `tanda_tangan` existing ke `completeForm()` agar status kelengkapan form konsisten.
4. Menghapus pemanggilan `$this->middleware('auth')` dari constructor `VisitFormController`, karena route sudah berada dalam group `auth` dan method tersebut tidak tersedia pada base controller project ini.
5. Menambahkan test coverage baru:
   - Submit form mempertahankan tanda tangan yang sudah diupload.
   - Submit form tanpa tanda tangan ditolak dan form tetap belum lengkap.

#### File Yang Diubah

- `app/Models/JadwalKlien.php`
- `app/Http/Controllers/VisitFormController.php`
- `tests/Feature/VisitFormSubmitTest.php`

#### Verifikasi

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php -l app\Models\JadwalKlien.php` | Berhasil | Tidak ada error sintaks. |
| `php -l app\Http\Controllers\VisitFormController.php` | Berhasil | Tidak ada error sintaks. |
| `php -l tests\Feature\VisitFormSubmitTest.php` | Berhasil | Tidak ada error sintaks. |
| `php artisan test tests\Feature\VisitFormSubmitTest.php` | Berhasil | 2 passed, 8 assertions. |
| `php artisan route:list` | Berhasil | Route registration tetap berhasil, 76 route. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali. |
| `php artisan test` | Gagal parsial | 55 passed, 8 failed, 158 assertions. Dua test Fase 4 baru lulus; sisa 8 gagal masih area lama di luar Fase 4. |

#### Sisa Catatan

Flow check-out masih tersebar antara `SalesPJPController::checkOutKlien()` dan `VisitFormController::submitForm()`. Pada fase ini, perubahan dibatasi ke bug tanda tangan dan validasi kelengkapan form. Penyelarasan flow check-out yang lebih besar dapat dilakukan bersama Fase 9 atau ketika Fase 6/7 menyentuh data kunjungan dan laporan.

## Fase 5 - Konfigurasi Radius GPS

Estimasi: 0.5-1 hari  
Prioritas: Sedang

### Masalah

Admin dapat mengubah radius di tabel `configurations`, tetapi check-in memakai `config('sales.gps_tolerance', 100)` dan kemungkinan tidak membaca nilai database.

### File Terdampak

- `app/Http/Controllers/Admin/ConfigurationController.php`
- `app/Http/Controllers/SalesPJPController.php`
- `app/Models/Configuration.php`
- Service validasi GPS jika ada

### Task

1. Buat helper/service untuk mengambil konfigurasi radius dari database.
2. Beri fallback default jika konfigurasi belum ada.
3. Ubah validasi check-in agar memakai nilai konfigurasi database.
4. Pastikan perubahan konfigurasi admin langsung mempengaruhi check-in.
5. Tambahkan test untuk radius custom.

### Acceptance Criteria

- Nilai radius dari halaman konfigurasi dipakai saat validasi GPS.
- Default tetap berjalan jika konfigurasi kosong.

### Status Fase 5 - Selesai 2026-05-22

Konfigurasi radius GPS sudah disambungkan ke validasi check-in.

#### Perubahan Yang Dilakukan

1. Menambahkan konstanta konfigurasi di `Configuration`:
   - `GPS_RADIUS_TOLERANCE_KEY`
   - `DEFAULT_GPS_RADIUS_TOLERANCE`
2. Menambahkan helper `Configuration::getGpsRadiusTolerance()` sebagai satu pintu pembacaan radius GPS.
3. Mengubah `SalesPJPController::checkInKlien()` agar memakai radius dari tabel `configurations`, bukan `config('sales.gps_tolerance', 100)`.
4. Menyelaraskan `Admin\ConfigurationController` agar halaman konfigurasi, update, dan reset memakai konstanta/helper yang sama.
5. Menambahkan test coverage untuk:
   - Radius custom 50 meter menolak check-in yang di luar radius.
   - Radius custom 200 meter menerima check-in yang sama.
   - Konfigurasi kosong fallback ke default 100 meter.

#### File Yang Diubah

- `app/Models/Configuration.php`
- `app/Http/Controllers/SalesPJPController.php`
- `app/Http/Controllers/Admin/ConfigurationController.php`
- `tests/Feature/SalesPJPCheckInConfigurationTest.php`

#### Verifikasi

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php -l app\Models\Configuration.php` | Berhasil | Tidak ada error sintaks. |
| `php -l app\Http\Controllers\SalesPJPController.php` | Berhasil | Tidak ada error sintaks. |
| `php -l app\Http\Controllers\Admin\ConfigurationController.php` | Berhasil | Tidak ada error sintaks. |
| `php -l tests\Feature\SalesPJPCheckInConfigurationTest.php` | Berhasil | Tidak ada error sintaks. |
| `php artisan test tests\Feature\SalesPJPCheckInConfigurationTest.php` | Berhasil | 2 passed, 11 assertions. |
| `rg "gps_tolerance\|config\('sales\.gps\|Configuration::getValue\('gps_radius_tolerance'\|'gps_radius_tolerance', 100" app database resources tests config` | Berhasil | Tidak ada referensi lama ke `config('sales.gps_tolerance')` atau pembacaan radius hardcoded di kode aplikasi. |
| `php artisan route:list` | Berhasil | Route registration tetap berhasil, 76 route. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali. |
| `php artisan test` | Gagal parsial | 57 passed, 8 failed, 169 assertions. Dua test Fase 5 baru lulus; sisa 8 gagal sama seperti sebelumnya dan berada di luar scope Fase 5. |

#### Sisa Catatan

Fase 5 tidak mengubah logika histori lokasi realtime. Tiga kegagalan `LocationControllerTest` tetap masuk scope Fase 6, sedangkan akses manager ke analytics tetap masuk Fase 8. Kegagalan constraint latitude/longitude dan cascade delete `lokasi_realtime` perlu diputuskan saat fase lokasi realtime atau cleanup testing.

## Fase 6 - Lokasi Realtime Dan Deteksi Tidak Bergerak

Estimasi: 1-2 hari  
Prioritas: Sedang

### Masalah

`LokasiRealtime::updateOrCreate(['user_id' => ...])` hanya menyimpan satu row per user, tetapi `calculateNoMovement()` butuh dua titik lokasi terakhir. Deteksi tidak bergerak tidak akan andal.

### Opsi A - Tambah Tabel Histori Lokasi

Rekomendasi jika F-22 penting.

#### Task

1. Buat tabel `lokasi_histories` atau sejenis.
2. Saat update lokasi:
   - Update snapshot terakhir di `lokasi_realtime`.
   - Insert titik baru ke tabel histori.
3. Hitung tidak bergerak dari histori.
4. Tambahkan pruning/cleanup histori lama.

### Opsi B - Sederhanakan Fitur

Dipilih jika realtime snapshot saja cukup.

#### Task

1. Hapus logika yang mengandalkan dua row dari `lokasi_realtime`.
2. Simpan field tambahan pada snapshot, misalnya:
   - `last_moved_at`
   - `last_latitude`
   - `last_longitude`
3. Hitung diam berdasarkan perubahan jarak dari snapshot sebelumnya.

### Acceptance Criteria

- Deteksi tidak bergerak memiliki data pembanding yang valid.
- Endpoint lokasi tidak error dan hasilnya konsisten.

### Status Fase 6 - Selesai 2026-05-22

Perbaikan lokasi realtime dan deteksi tidak bergerak sudah dikerjakan.

#### Keputusan Implementasi

Dipilih pendekatan minimal yang sesuai dengan schema saat ini: `lokasi_realtime` dipakai sebagai histori titik lokasi, bukan hanya satu snapshot per user. Titik terbaru tetap diambil dengan scope `latestPerUser()`, sehingga dashboard masih mendapat snapshot terakhir, sementara kalkulasi tidak bergerak punya dua titik pembanding yang valid.

#### Perubahan Yang Dilakukan

1. Mengubah `LocationController::updateLocation()` dari `LokasiRealtime::updateOrCreate()` menjadi `LokasiRealtime::create()`.
   - Setiap update dari device sales sekarang menyimpan titik histori baru.
   - Dashboard tetap memakai `latestPerUser()` untuk mengambil titik terbaru per sales.
2. Memperbaiki `calculateNoMovement()` agar durasi tidak bergerak tidak menghasilkan nilai negatif.
3. Menambahkan test bahwa dua update lokasi dari API menghasilkan dua row histori dan status `idle` terdeteksi.
4. Menambahkan check constraint database untuk koordinat:
   - `latitude` harus berada antara `-90` dan `90`.
   - `longitude` harus berada antara `-180` dan `180`.
5. Menambahkan relasi `User::lokasiRealtime()` dan cleanup lokasi saat user dihapus, agar data realtime tidak tertinggal ketika user soft delete.

#### File Yang Diubah

- `app/Http/Controllers/Api/LocationController.php`
- `app/Models/User.php`
- `database/migrations/2026_05_22_000001_add_coordinate_checks_to_lokasi_realtime_table.php`
- `tests/Feature/Phase5/LocationControllerTest.php`

#### Verifikasi

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php -l app\Http\Controllers\Api\LocationController.php` | Berhasil | Tidak ada error sintaks. |
| `php -l app\Models\User.php` | Berhasil | Tidak ada error sintaks. |
| `php -l database\migrations\2026_05_22_000001_add_coordinate_checks_to_lokasi_realtime_table.php` | Berhasil | Tidak ada error sintaks. |
| `php -l tests\Feature\Phase5\LocationControllerTest.php` | Berhasil | Tidak ada error sintaks. |
| `php artisan test tests\Feature\Phase5\LocationControllerTest.php` | Berhasil | 15 passed, 51 assertions. |
| `php artisan test tests\Unit\Phase5\LokasiRealtimeTest.php` | Berhasil | 10 passed, 22 assertions. |
| `php artisan route:list` | Berhasil | Route registration tetap berhasil, 76 route. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali. |
| `php artisan test` | Gagal parsial | 64 passed, 2 failed, 175 assertions. Kegagalan lokasi realtime dan no movement sudah selesai. |

#### Sisa Catatan

Sisa full test yang gagal tinggal:

1. `Phase5IntegrationTest::phase5 integration with phase6 analytics` masih 403 karena manager mengakses `/admin/analytics/dashboard`. Ini masuk scope Fase 8.
2. `Phase5IntegrationTest::complete flow across phases` masih membandingkan `waktu_masuk` sebagai timestamp penuh, sedangkan database menyimpan `H:i:s`. Ini bisa diselesaikan pada Fase 9 atau cleanup test.

## Fase 7 - Laporan, Export, Dan Dependency

Estimasi: 1-3 hari  
Prioritas: Tinggi untuk PRD, Sedang untuk stabilisasi

### Masalah

View analytics memanggil route export yang tidak ada. `ReportService` memakai `PhpSpreadsheet`, tetapi dependency belum ada. Export PDF juga belum tersedia.

### File Terdampak

- `app/Services/ReportService.php`
- `routes/web.php`
- Controller report/export baru atau existing
- `composer.json`
- View analytics admin

### Task

1. Tentukan format export minimum:
   - Excel saja terlebih dahulu, atau
   - Excel dan PDF sesuai PRD.
2. Pilih dependency:
   - `maatwebsite/excel` untuk Excel, atau pakai langsung `phpoffice/phpspreadsheet`.
   - `barryvdh/laravel-dompdf` untuk PDF.
3. Tambahkan route:
   - `admin.reports.export-sales-performance`
   - `admin.reports.export-regional-performance`
   - `admin.reports.export-klien-analysis`
4. Buat controller export yang memanggil `ReportService`.
5. Perbaiki `ReportService` agar data source sesuai keputusan Fase 3.
6. Tambahkan authorization role admin/manager sesuai kebutuhan.
7. Tambahkan test route export minimal.

### Acceptance Criteria

- Tombol export analytics tidak error.
- File export dapat diunduh.
- Dependency export tercatat di `composer.json` dan `composer.lock`.
- Query laporan tidak memakai `users.role`.

### Status Fase 7 - Selesai 2026-05-22

Fitur export laporan minimum sudah disambungkan dengan format Excel XLSX.

#### Perubahan Yang Dilakukan

1. Menambahkan dependency `phpoffice/phpspreadsheet` di `composer.json` dan `composer.lock`.
2. Menambahkan `Admin\ReportExportController` untuk export:
   - Sales performance.
   - Regional performance.
   - Klien analysis.
3. Menambahkan route export bernama:
   - `admin.reports.export-sales-performance`
   - `admin.reports.export-regional-performance`
   - `admin.reports.export-klien-analysis`
4. Mengaktifkan ulang tombol `Download Excel` pada view analytics.
5. Menambahkan test export untuk admin, manager, dan penolakan akses sales.
6. Menyesuaikan `ReportService` agar kompatibel dengan PhpSpreadsheet 5.x.

#### File Yang Diubah

- `composer.json`
- `composer.lock`
- `app/Http/Controllers/Admin/ReportExportController.php`
- `app/Services/ReportService.php`
- `routes/web.php`
- `resources/views/admin/analytics/sales-performance.blade.php`
- `resources/views/admin/analytics/regional-performance.blade.php`
- `resources/views/admin/analytics/klien-analysis.blade.php`
- `tests/Feature/AdminReportExportTest.php`

#### Verifikasi

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php artisan test tests\Feature\AdminReportExportTest.php` | Berhasil | 5 passed, 9 assertions. |
| `php artisan route:list` | Berhasil | Route registration berhasil, 79 route. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali. |

#### Sisa Catatan

PDF belum diimplementasikan pada fase ini. Keputusan fase ini adalah Excel terlebih dahulu karena `ReportService` sudah memakai PhpSpreadsheet dan kebutuhan utama adalah menghilangkan route/tombol export yang putus.

## Fase 8 - Akses Manager Ke Laporan

Estimasi: 0.5-1 hari  
Prioritas: Sedang

### Masalah

Manager saat ini hanya punya dashboard, sedangkan PRD mengharuskan manager melihat laporan performa, filter tanggal, dan export.

### Task

1. Tentukan laporan apa saja yang boleh diakses manager.
2. Tambahkan route manager untuk laporan:
   - Sales performance.
   - Regional performance.
   - Klien analysis jika diperlukan.
3. Gunakan controller yang sama dengan admin bila authorization memungkinkan.
4. Batasi data manager jika ada scope wilayah/tim.
5. Tambahkan menu navigasi manager.

### Acceptance Criteria

- Manager dapat membuka laporan sesuai PRD.
- Data manager tidak melebihi scope yang ditentukan.
- Export manager berjalan jika fitur export diaktifkan.

### Status Fase 8 - Selesai 2026-05-22

Akses manager ke analytics dan export laporan sudah dibuka.

#### Perubahan Yang Dilakukan

1. Route analytics dan report export sekarang dapat diakses role `admin`, `super_admin`, dan `manager`.
2. Route tetap memakai nama `admin.analytics.*` dan `admin.reports.*` agar tidak memecah view/controller yang sudah ada.
3. Menu manager di sidebar sekarang mengarah ke halaman laporan nyata:
   - Ringkasan analytics.
   - Performa sales.
   - Performa regional.
   - Analisis klien.
4. Menambahkan test akses manager ke halaman analytics.
5. Menambahkan test manager dapat export laporan, dan sales tetap ditolak.

#### File Yang Diubah

- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `tests/Feature/ManagerReportAccessTest.php`
- `tests/Feature/AdminReportExportTest.php`

#### Verifikasi

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php -l routes\web.php` | Berhasil | Tidak ada error sintaks. |
| `php -l resources\views\layouts\app.blade.php` | Berhasil | Tidak ada error sintaks. |
| `php -l tests\Feature\ManagerReportAccessTest.php` | Berhasil | Tidak ada error sintaks. |
| `php artisan test tests\Feature\ManagerReportAccessTest.php` | Berhasil | 2 passed, 5 assertions. |
| `php artisan test tests\Feature\AdminReportExportTest.php` | Berhasil | 5 passed, 9 assertions. |
| `php artisan test tests\Feature\Phase5\Phase5IntegrationTest.php` | Gagal parsial | 10 passed, 1 failed. Test analytics manager yang sebelumnya 403 sudah lulus; sisa gagal hanya format `waktu_masuk`. |
| `php artisan route:list` | Berhasil | Route registration berhasil, 79 route. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali. |
| `php artisan test` | Gagal parsial | 72 passed, 1 failed, 191 assertions. Sisa gagal adalah mismatch `waktu_masuk` timestamp penuh vs format database `H:i:s`. |

#### Sisa Catatan

Scope wilayah/tim manager belum dibatasi karena belum ada aturan eksplisit di schema atau dokumen fase sebelumnya. Saat ini manager melihat laporan agregat yang sama dengan admin, tanpa akses ke master data admin.

## Fase 9 - Query Dan Bug Minor

Estimasi: 0.5 hari  
Prioritas: Sedang-Rendah

### Task

1. Perbaiki `SalesPJPController::getNextKlien()`:
   - Kelompokkan `where status active/pending` dalam closure.
2. Selaraskan status `JadwalKunjungan` dan `JadwalKlien`:
   - Dokumentasikan mapping `aktif` vs `active`.
   - Atau refactor ke satu bahasa jika memungkinkan.
3. Rapikan import tidak terpakai.
4. Pastikan `routes/api.php` sengaja tidak dipakai atau pindahkan endpoint API ke sana jika ingin mengikuti konvensi Laravel.

### Acceptance Criteria

- Query tidak berisiko mengambil data dari jadwal lain.
- Import tidak terpakai berkurang.
- Status domain terdokumentasi atau konsisten.

### Status Fase 9 - Selesai 2026-05-23

Query dan bug minor sudah dikerjakan.

#### Perubahan Yang Dilakukan

1. Memperbaiki `SalesPJPController::getNextKlien()` agar filter status `active` atau `pending` dikelompokkan dalam closure.
   - Query tidak lagi berisiko mengambil `jadwal_klien` pending dari jadwal lain akibat `orWhere` yang tidak dikelompokkan.
2. Menambahkan konstanta status pada model:
   - `JadwalKunjungan::STATUS_PENDING`, `STATUS_ACTIVE`, `STATUS_COMPLETED`.
   - `JadwalKlien::STATUS_PENDING`, `STATUS_ACTIVE`, `STATUS_CHECKING_OUT`, `STATUS_COMPLETED`, `STATUS_SKIPPED`.
3. Menggunakan konstanta status pada method model dan controller yang disentuh Fase 9.
4. Menambahkan dokumentasi mapping status di `DATA_FLOW_KUNJUNGAN.md`.
   - `jadwal_kunjungan.status`: `pending`, `aktif`, `selesai`.
   - `jadwal_klien.status`: `pending`, `active`, `checking_out`, `completed`, `skipped`.
5. Mendokumentasikan keputusan route API saat ini:
   - Endpoint lokasi realtime tetap berada di `routes/web.php` dengan prefix `/api` karena memakai session auth aplikasi web.
   - Jangan dipindah ke `routes/api.php` tanpa mengganti autentikasi dan test terkait.
6. Menambahkan test regresi `SalesPJPNextKlienTest` untuk memastikan endpoint next klien hanya membaca klien dari jadwal yang diminta.

#### File Yang Diubah

- `app/Http/Controllers/SalesPJPController.php`
- `app/Models/JadwalKunjungan.php`
- `app/Models/JadwalKlien.php`
- `DATA_FLOW_KUNJUNGAN.md`
- `tests/Feature/SalesPJPNextKlienTest.php`

#### Verifikasi

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php -l app\Http\Controllers\SalesPJPController.php` | Berhasil | Tidak ada error sintaks. |
| `php -l app\Models\JadwalKunjungan.php` | Berhasil | Tidak ada error sintaks. |
| `php -l app\Models\JadwalKlien.php` | Berhasil | Tidak ada error sintaks. |
| `php -l tests\Feature\SalesPJPNextKlienTest.php` | Berhasil | Tidak ada error sintaks. |
| `php artisan test tests\Feature\SalesPJPNextKlienTest.php` | Berhasil | 1 passed, 2 assertions. |
| `php artisan route:list` | Berhasil | Route registration berhasil, 79 route. |
| `php artisan test` | Berhasil | 74 passed, 199 assertions, durasi sekitar 59.90 detik. |

## Fase 10 - Testing Dan Verifikasi Akhir

Estimasi: 1 hari  
Prioritas: Wajib

### Task

1. Jalankan:
   - `php artisan test`
   - `php artisan route:list`
2. Uji manual flow utama:
   - Login admin, manager, sales.
   - Absensi masuk dan pulang.
   - Sales melihat PJP hari ini.
   - Sales membuka detail PJP.
   - Check-in dengan GPS valid.
   - Upload foto dan tanda tangan.
   - Submit visit form.
   - Check-out.
   - Admin/manager melihat dashboard.
   - Admin/manager membuka analytics.
   - Export laporan jika sudah diaktifkan.
3. Uji kasus negatif:
   - Sales check-in di luar radius.
   - User tanpa role mencoba akses halaman role tertentu.
   - Export tanpa data.
4. Catat sisa gap PRD yang sengaja belum dikerjakan.

### Acceptance Criteria

- `php artisan route:list` berhasil.
- `php artisan test` lulus, atau sisa kegagalan terdokumentasi dengan alasan jelas.
- Flow utama sales dan admin/manager berjalan tanpa error fatal.

### Status Fase 10 - Selesai 2026-05-23

Testing dan verifikasi akhir sudah dikerjakan.

#### Perubahan Yang Dilakukan

1. Menambahkan smoke test operasional `Phase10OperationalSmokeTest`.
2. Smoke test mencakup flow utama:
   - Admin membuka dashboard.
   - Manager membuka dashboard.
   - Sales membuka dashboard.
   - Sales check-in absensi.
   - Sales membuka PJP hari ini dan detail PJP.
   - Sales mulai perjalanan.
   - Sales check-in klien dengan GPS valid.
   - Sales upload foto check-in dan check-out.
   - Sales upload tanda tangan.
   - Sales submit visit form.
   - Sales check-out klien.
   - Sales check-out absensi.
   - Manager membuka analytics.
   - Manager export laporan sales performance.
3. Smoke test juga mencakup kasus negatif:
   - Guest ditolak dari dashboard sales dan diarahkan ke login.
   - Sales ditolak dari analytics admin/manager.
   - Check-in klien di luar radius GPS ditolak.
4. Menjalankan migration lokal yang masih pending:
   - `2026_05_22_000001_add_coordinate_checks_to_lokasi_realtime_table`.
5. Membersihkan compiled view setelah verifikasi cache.

#### File Yang Diubah/Ditambahkan

- `tests/Feature/Phase10OperationalSmokeTest.php`
- `fixing-planning.md`

#### Verifikasi

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php artisan migrate` | Berhasil | Migration constraint koordinat lokasi realtime diterapkan ke database lokal. |
| `php artisan migrate:status` | Berhasil | Semua migration berstatus `Ran`. |
| `php -l tests\Feature\Phase10OperationalSmokeTest.php` | Berhasil | Tidak ada error sintaks. |
| `php artisan test tests\Feature\Phase10OperationalSmokeTest.php` | Berhasil | 2 passed, 31 assertions. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan route:list` | Berhasil | Route registration berhasil, 79 route. |
| `php artisan test` | Berhasil | 76 passed, 230 assertions, durasi sekitar 31.62 detik. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali untuk environment development. |

#### Gap Lanjutan Diselesaikan 2026-05-23

Gap yang sebelumnya tersisa sudah ditutup:

1. Export PDF sudah diimplementasikan untuk laporan:
   - Sales performance.
   - Regional performance.
   - Klien analysis.
2. Tombol `Download PDF` ditambahkan pada halaman analytics terkait.
3. Dependency `barryvdh/laravel-dompdf` ditambahkan ke `composer.json` dan `composer.lock`.
4. Scope manager sekarang dibatasi berdasarkan `users.wilayah_id`.
   - Admin dan super admin tetap dapat melihat semua wilayah.
   - Manager hanya melihat sales, wilayah, klien, analytics, dan export pada wilayahnya sendiri.
   - Manager tanpa `wilayah_id` mendapat hasil kosong untuk laporan scoped.
   - Manager yang mencoba export `wilayah_id` lain mendapat 403.
5. Test coverage ditambahkan untuk PDF export dan scope manager.

File tambahan/diubah:

- `app/Http/Controllers/Admin/ReportExportController.php`
- `app/Http/Controllers/Dashboard/AnalyticsController.php`
- `app/Services/ReportService.php`
- `resources/views/reports/pdf.blade.php`
- `resources/views/admin/analytics/sales-performance.blade.php`
- `resources/views/admin/analytics/regional-performance.blade.php`
- `resources/views/admin/analytics/klien-analysis.blade.php`
- `tests/Feature/ManagerReportScopeTest.php`
- `composer.json`
- `composer.lock`

Verifikasi gap lanjutan:

| Command | Status | Ringkasan |
| --- | --- | --- |
| `composer update barryvdh/laravel-dompdf --with-dependencies` | Berhasil | Dependency PDF tersedia dan package discovery berhasil. |
| `php -l app\Http\Controllers\Admin\ReportExportController.php` | Berhasil | Tidak ada error sintaks. |
| `php -l app\Services\ReportService.php` | Berhasil | Tidak ada error sintaks. |
| `php -l app\Http\Controllers\Dashboard\AnalyticsController.php` | Berhasil | Tidak ada error sintaks. |
| `php -l tests\Feature\ManagerReportScopeTest.php` | Berhasil | Tidak ada error sintaks. |
| `php artisan test tests\Feature\ManagerReportScopeTest.php` | Berhasil | 3 passed, 8 assertions. |
| `php artisan test tests\Feature\AdminReportExportTest.php tests\Feature\ManagerReportAccessTest.php` | Berhasil | 7 passed, 14 assertions. |
| `php artisan route:list` | Berhasil | Route registration berhasil, 79 route. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan test` | Berhasil | 79 passed, 238 assertions, durasi sekitar 37.95 detik. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali untuk environment development. |

## Urutan Eksekusi Yang Disarankan

1. Fase 0 - Baseline Dan Pengamanan
2. Fase 1 - Konsolidasi Role Spatie
3. Fase 2 - Perbaikan Route/View Yang Putus
4. Fase 4 - Perbaikan Visit Form Dan Checkout
5. Fase 3 - Keputusan Dan Konsistensi Data Kunjungan
6. Fase 5 - Konfigurasi Radius GPS
7. Fase 9 - Query Dan Bug Minor
8. Fase 7 - Laporan, Export, Dan Dependency
9. Fase 8 - Akses Manager Ke Laporan
10. Fase 6 - Lokasi Realtime Dan Deteksi Tidak Bergerak
11. Fase 10 - Testing Dan Verifikasi Akhir

Catatan: Fase 3 diletakkan setelah perbaikan fatal awal agar aplikasi cepat stabil. Namun keputusan desain Fase 3 tetap perlu dibuat sebelum pengerjaan laporan/export yang lebih serius.

## Quick Wins

Item berikut bisa dikerjakan cepat dan memberi dampak langsung:

1. Ganti semua query `users.role` ke Spatie Permission.
2. Tambahkan view `sales.pjp.show`.
3. Perbaiki `submitForm()` agar tidak menghapus `tanda_tangan`.
4. Kelompokkan `orWhere` di `getNextKlien()`.
5. Sembunyikan atau sambungkan tombol export analytics yang routenya belum ada.
6. Hapus import `App\Models\Kunjungan` jika memilih tetap memakai `jadwal_klien`.

## Risiko Dan Mitigasi

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Refactor role menyentuh banyak test | Test bisa gagal di banyak tempat | Perbaiki factory dan seeder test dulu, lalu controller |
| Perubahan model kunjungan terlalu besar | Banyak flow rusak | Pilih opsi `jadwal_klien` untuk stabilisasi cepat |
| Dependency export perlu instalasi package | Butuh update composer dan kemungkinan konflik versi | Tambahkan dependency satu per satu dan jalankan test |
| Data lokasi historis membesar | Database cepat penuh | Tambahkan pruning histori lokasi |
| Manager report butuh scope wilayah | Risiko data leakage | Tentukan aturan scope sebelum membuka route manager |

## Definition Of Done

Perbaikan dianggap selesai jika:

1. Semua error fatal dari analisis sudah hilang atau terdokumentasi sebagai keputusan sadar.
2. Role memakai Spatie Permission secara konsisten.
3. Route/view yang dipanggil aplikasi tersedia.
4. Visit form tidak menghapus tanda tangan.
5. Radius GPS membaca konfigurasi yang bisa diubah admin.
6. Laporan/export tidak memiliki tombol atau route palsu.
7. `php artisan route:list` berhasil.
8. `php artisan test` lulus atau sisa kegagalan sudah memiliki daftar tindak lanjut yang jelas.
