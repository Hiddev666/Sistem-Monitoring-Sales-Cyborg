# Hasil Analisis Flow Aplikasi Monitoring Sales Force

Sumber pembanding: `Analisis_Alur_Aplikasi_Monitoring_Sales_Force (1).pdf`

Tanggal analisis: 2026-05-28

## Ringkasan

Secara umum, codebase saat ini sudah mengikuti alur besar yang dijelaskan di PDF:

- Ada pemisahan role `sales`, `manager`, `admin`, dan `super_admin`.
- Sales memakai mobile web untuk absensi, melihat PJP hari ini, mulai perjalanan, check-in GPS, mengisi form kunjungan, upload foto, tanda tangan digital, dan checkout.
- Admin/super admin dapat mengelola user, wilayah, klien, konfigurasi GPS, PJP, rekap absensi, galeri dokumentasi, analytics, dan export laporan.
- Manager dapat mengakses dashboard monitoring real-time, peta lokasi sales, alert, analytics, dan export laporan dengan scope wilayah.
- Validasi keamanan utama sudah ada: auth middleware, role middleware, validasi GPS radius, timestamp server-side, validasi kepemilikan jadwal, dan proteksi akses foto kunjungan.

Status keseluruhan: **sesuai sebagian besar**, dengan beberapa gap dan inkonsistensi yang perlu diperhatikan.

## Perbandingan Alur Utama

| Alur di PDF | Implementasi di codebase | Status | Catatan |
|---|---|---:|---|
| Setup data master: user, wilayah, klien, konfigurasi sistem | Ada route admin untuk user, wilayah, klien, dan konfigurasi di `routes/web.php`. Controller terkait: `Admin/UserController.php`, `Admin/WilayahController.php`, `Admin/KlienController.php`, `Admin/ConfigurationController.php`. | Sesuai | Form klien mewajibkan latitude dan longitude, sesuai kebutuhan validasi GPS. |
| Pembuatan jadwal PJP harian untuk sales | Ada CRUD PJP di `Admin/PJPController.php`, route `admin.pjp.*`. Jadwal memakai `jadwal_kunjungan`, detail klien memakai `jadwal_klien`, dan urutan disimpan di kolom `urutan`. | Sesuai sebagian | Saat `store`, field `klien` masih dibuat `nullable`, sehingga admin bisa membuat jadwal tanpa daftar klien. Ini tidak sepenuhnya sesuai PDF yang menyebut jadwal berisi daftar klien. |
| Sales login mobile web | Login diarahkan berdasarkan role di `Auth/LoginController.php`. Role sales diarahkan ke `sales.dashboard`. Layout sales khusus ada di `resources/views/layouts/sales.blade.php`. | Sesuai | UI sales sudah mobile-oriented dengan bottom nav. |
| Sales absensi masuk | Ada `AbsensiController::checkIn`, route `sales.attendance.checkin`. Menyimpan waktu, latitude, longitude, accuracy, dan status. | Sesuai | Accuracy sudah dilebarkan sampai `999999.99`, cocok untuk nilai GPS besar dari browser. |
| Sales memulai perjalanan PJP | Ada `SalesPJPController::startJourney`, route `sales.pjp.start`. Status jadwal berubah dari `pending` ke `aktif`. | Sesuai | Controller memastikan sales hanya bisa mengakses jadwal miliknya. |
| Sales check-in GPS di lokasi klien | Ada `SalesPJPController::checkInKlien`. Validasi memakai `GpsValidationService::validateCheckIn` dengan radius dari `Configuration::getGpsRadiusTolerance()`. | Sesuai | Ini mendukung pencegahan ghost check-in berbasis jarak GPS. |
| Dokumentasi kunjungan: foto check-in, foto check-out, catatan, hasil, nominal, tanda tangan digital | Ada `VisitFormController` untuk upload foto, upload signature, submit form. Model `JadwalKlien` menyimpan `foto_checkin`, `foto_checkout`, `catatan_kunjungan`, `hasil_tipe`, `nominal_transaksi`, `tanda_tangan`, dan checkout GPS. | Sesuai | Submit form mewajibkan foto check-in, foto check-out, dan tanda tangan. |
| Sales checkout kunjungan | Ada `SalesPJPController::checkOutKlien`, tetapi proses final checkout diarahkan ke form kunjungan. `JadwalKlien::completeForm` mengisi `waktu_checkout` dan status `completed`. | Sesuai | Checkout teknis terjadi saat form lengkap dikirim. |
| Sales lanjut ke klien berikutnya | Ada `SalesPJPController::getNextKlien` dan daftar PJP ordered by `urutan`. | Sesuai | Belum terlihat hard-block yang memaksa urutan kunjungan satu per satu di semua entry point, tetapi data urutan dan next-klien tersedia. |
| Sales absensi pulang | Ada `AbsensiController::checkOut`, route `sales.attendance.checkout`. Menyimpan waktu keluar, lokasi keluar, dan durasi. | Sesuai | Tidak terlihat aturan wajib menyelesaikan semua PJP sebelum absensi pulang. |
| Monitoring real-time oleh manager/admin | Ada `ManagerDashboardController`, `LocationController::salesLocations`, dan peta Leaflet di `resources/views/manager/dashboard.blade.php`. Sales mengirim lokasi berkala dari `layouts/sales.blade.php`. | Sesuai sebagian | Dashboard map utama hanya route `manager.dashboard`. Admin bisa mengakses API dashboard, tetapi halaman admin dashboard saat ini masih statis dan tidak menampilkan map real-time. |
| Alert aktivitas sales | Ada perhitungan `noMovementMinutes` di `LocationController` dan alert di dashboard manager jika tidak bergerak lebih dari 60 menit. | Sesuai | Alert berbasis dua titik lokasi terakhir dengan toleransi gerakan 10 meter. |
| Laporan performa dan export PDF/Excel | Ada analytics di `Dashboard/AnalyticsController.php` dan export di `Admin/ReportExportController.php` via `ReportService`. | Sesuai sebagian | Beberapa query analytics masih memakai `jadwal_klien.created_at`, bukan `jadwal_kunjungan.tanggal`, sehingga hasil bisa berbeda dari alur "jadwal/kunjungan pada tanggal tertentu". |

## Perbandingan Berdasarkan Role

### Super Admin

| Alur PDF | Implementasi | Status |
|---|---|---:|
| Login ke sistem | Ada auth dan redirect role admin/super_admin ke admin dashboard. | Sesuai |
| Mengelola user dan role | Ada `admin.users.*`, role disimpan via Spatie Permission. | Sesuai |
| Mengelola wilayah | Ada `admin.wilayah.*`. | Sesuai |
| Mengelola klien | Ada `admin.klien.*`. | Sesuai |
| Mengatur konfigurasi sistem | Ada `admin.configuration.*`. | Sesuai |
| Mengakses seluruh dashboard dan laporan | Super admin masuk grup admin dan analytics/report. | Sesuai sebagian |
| Logout | Ada route `logout`. | Sesuai |

Catatan: super admin belum punya dashboard khusus yang benar-benar "full overview"; route yang tersedia adalah dashboard admin umum.

### Admin

| Alur PDF | Implementasi | Status |
|---|---|---:|
| Login ke dashboard admin | Ada `admin.dashboard`. | Sesuai |
| Mengelola data sales/user | Ada CRUD user. | Sesuai |
| Mengelola wilayah dan klien | Ada CRUD wilayah dan klien. | Sesuai |
| Menginput koordinat GPS klien | Form klien mewajibkan latitude dan longitude. | Sesuai |
| Membuat jadwal PJP harian | Ada CRUD PJP. | Sesuai |
| Menentukan urutan kunjungan sales | Urutan disimpan berdasarkan urutan array `klien` saat create/update. | Sesuai |
| Memantau rekap absensi | Ada `admin.attendance.recap` dan endpoint data. | Sesuai |
| Melihat dokumentasi hasil kunjungan | Ada `admin.photo-gallery.*` dan `admin.pjp.visit-gallery`. | Sesuai |
| Mengevaluasi aktivitas lapangan sales | Ada analytics dan report. | Sesuai sebagian |

Catatan penting: `resources/views/admin/dashboard.blade.php` masih menampilkan angka `0` dan link `#`, sehingga dashboard admin belum operasional sebagai ringkasan data. Fungsi operasionalnya ada di menu CRUD/report, bukan di dashboard admin.

### Manager / Pimpinan

| Alur PDF | Implementasi | Status |
|---|---|---:|
| Login ke dashboard manager | Ada `manager.dashboard`. | Sesuai |
| Melihat dashboard monitoring real-time | Ada dashboard manager dengan auto-refresh lokasi 30 detik. | Sesuai |
| Melihat lokasi sales di peta | Ada Leaflet map dan endpoint `api.dashboard.sales-locations`. | Sesuai |
| Memantau status kunjungan | Popup marker berisi status, jumlah kunjungan, selesai, update terakhir. | Sesuai |
| Melihat alert aktivitas sales | Ada alert sales tidak bergerak lebih dari 60 menit. | Sesuai |
| Membuka laporan performa | Manager dapat mengakses route analytics di prefix `admin.analytics.*`. | Sesuai |
| Filter laporan tanggal/wilayah | Laporan punya filter tanggal; manager dipaksa ke wilayah sendiri jika punya `wilayah_id`. | Sesuai |
| Export PDF/Excel | Ada route report export untuk PDF/XLSX. | Sesuai |
| Read-only terhadap data operasional | Manager tidak masuk route CRUD admin. | Sesuai |

Catatan: manager memakai URL prefix `admin/analytics/...` untuk laporan. Secara akses benar, tetapi dari sisi naming/UX bisa membingungkan karena role manager membuka halaman dengan prefix admin.

### Sales

| Alur PDF | Implementasi | Status |
|---|---|---:|
| Login melalui mobile web | Ada layout sales dan redirect sales dashboard. | Sesuai |
| Melakukan absensi masuk | Ada check-in absensi berbasis GPS. | Sesuai |
| Melihat jadwal kunjungan hari ini | Ada `sales.pjp.today`. | Sesuai |
| Menekan tombol mulai perjalanan | Ada route mulai perjalanan. | Sesuai |
| Datang ke lokasi klien | Ditunjang data klien dan jadwal. | Sesuai |
| Melakukan check-in GPS | Ada check-in klien dengan validasi radius. | Sesuai |
| Sistem memvalidasi radius GPS | Ada `GpsValidationService`. | Sesuai |
| Mengambil foto kunjungan | Ada upload foto check-in dan checkout. | Sesuai |
| Mengisi visit form | Ada form kunjungan dengan catatan, hasil, nominal, dan signature. | Sesuai |
| Melakukan check-out kunjungan | Checkout diarahkan ke form; status selesai saat form submit. | Sesuai |
| Melanjutkan ke klien berikutnya | Ada endpoint next-klien dan tampilan PJP ordered. | Sesuai |
| Melakukan absensi pulang | Ada checkout absensi. | Sesuai |

Catatan: location tracking real-time sales sekarang aktif setelah absensi check-in dan berhenti setelah checkout absensi. Ini selaras dengan alur monitoring real-time.

## Validasi dan Keamanan

| Aspek PDF | Implementasi | Status |
|---|---|---:|
| Role-based access control Spatie Permission | Ada role middleware dan pemakaian `assignRole`, `role()`, `hasRole()`. | Sesuai |
| Validasi GPS radius | Ada di check-in klien melalui `GpsValidationService` dan konfigurasi radius. | Sesuai |
| Timestamp server-side | Waktu absensi, check-in, checkout, dan submit form memakai `now()`. | Sesuai |
| Dokumentasi foto check-in/check-out | Ada upload, penyimpanan, preview, download, dan galeri. | Sesuai |
| Tanda tangan digital pelanggan | Ada upload signature base64 dan penyimpanan path signature. | Sesuai |
| Middleware route protection | Route dibungkus `auth` dan `role:*`. | Sesuai |
| Session management dan logout aman | Ada logout dan password change. Konfigurasi session timeout tersimpan, tetapi belum terlihat integrasi eksplisit ke middleware session timeout. | Sesuai sebagian |
| Pencegahan akses foto tidak sah | Preview foto memakai route controller dan cek owner/admin/manager wilayah. | Sesuai |

## Gap dan Risiko yang Ditemukan

1. **Dashboard admin masih statis**
   - File: `resources/views/admin/dashboard.blade.php`
   - Dampak: PDF menyebut admin bisa mengevaluasi aktivitas lapangan; modulnya ada, tetapi dashboard admin utama belum menampilkan angka nyata atau link yang mengarah ke modul terkait.

2. **Admin belum punya halaman map real-time yang sama dengan manager**
   - API map mengizinkan `manager,admin,super_admin`, tetapi halaman peta hanya ada di `manager.dashboard`.
   - Dampak: PDF menyebut Manager dan Admin dapat memonitor aktivitas via dashboard monitoring. Untuk admin, monitoring real-time belum muncul sebagai dashboard utama.

3. **PJP bisa dibuat tanpa klien pada proses create**
   - File: `app/Http/Controllers/Admin/PJPController.php`
   - Detail: validasi `store` memakai `klien => nullable|array`.
   - Dampak: jadwal bisa tidak berisi daftar klien, berbeda dari flow PDF.

4. **Beberapa analytics memakai `jadwal_klien.created_at`**
   - File: `app/Http/Controllers/Dashboard/AnalyticsController.php`
   - Dampak: filter tanggal laporan bisa mengikuti waktu data dibuat, bukan tanggal jadwal kunjungan. Ini berpotensi membuat laporan berbeda dari aktivitas harian sebenarnya.

5. **Photo gallery juga banyak memakai `created_at`**
   - File: `app/Http/Controllers/PhotoGalleryController.php`
   - Dampak: filter dokumentasi bisa tidak sama dengan tanggal kunjungan jika PJP dibuat sebelum/sesudah tanggal kunjungan.

6. **Urutan kunjungan tersedia, tetapi enforcement perlu dicek lebih lanjut**
   - Data `urutan` ada dan `getNextKlien` mengambil pending/active pertama.
   - Namun perlu audit UI/endpoint apakah sales bisa check-in klien urutan berikutnya sebelum menyelesaikan klien sebelumnya.

7. **Absensi pulang tidak terlihat wajib menunggu semua kunjungan selesai**
   - File: `app/Http/Controllers/AbsensiController.php`
   - Dampak: sales kemungkinan bisa checkout absensi walau PJP belum selesai. PDF tidak menyebut eksplisit larangan, tetapi flow idealnya absensi pulang dilakukan setelah aktivitas kunjungan selesai.

8. **Konfigurasi session timeout belum terlihat diterapkan**
   - File konfigurasi ada di `ConfigurationController`, tetapi belum terlihat middleware yang membaca `session_timeout_minutes`.
   - Dampak: klaim session management dari PDF baru sebagian.

## Rekomendasi Prioritas

### Prioritas Tinggi

1. Buat dashboard admin menggunakan data nyata dan link modul yang benar.
2. Tampilkan peta monitoring real-time juga untuk admin/super admin, atau buat route dashboard monitoring bersama.
3. Ubah validasi create PJP agar wajib memilih minimal satu klien:
   - `klien => required|array|min:1`
4. Samakan basis tanggal analytics/report dari `jadwal_klien.created_at` ke `jadwal_kunjungan.tanggal` untuk laporan berbasis aktivitas kunjungan.

### Prioritas Menengah

1. Terapkan aturan urutan kunjungan jika bisnis mengharuskan sales mengikuti PJP sesuai urutan.
2. Pertimbangkan blok checkout absensi jika masih ada PJP aktif atau kunjungan belum selesai.
3. Terapkan session timeout dari konfigurasi ke middleware/session handling.

### Prioritas Rendah

1. Rapikan naming route laporan manager agar tidak terasa seperti area admin, misalnya route alias `manager.analytics.*`.
2. Tambahkan indikator status tracking lokasi di UI sales agar sales tahu lokasi sedang dikirim setelah absensi check-in.

## Kesimpulan

Codebase saat ini sudah cukup dekat dengan flow pada PDF. Modul inti sudah ada dan saling terhubung:

- Master data -> PJP -> absensi sales -> perjalanan -> check-in GPS -> dokumentasi kunjungan -> monitoring manager -> laporan/export.

Perbedaan utama bukan pada ketiadaan fitur inti, tetapi pada penyempurnaan operasional:

- dashboard admin masih placeholder,
- monitoring map belum muncul untuk admin,
- beberapa laporan masih memakai tanggal pembuatan record,
- create PJP masih bisa tanpa klien,
- beberapa aturan bisnis tambahan belum dipaksa oleh backend.

Jika gap tersebut ditutup, implementasi aplikasi akan jauh lebih konsisten dengan alur ideal yang dijelaskan dalam PDF.
