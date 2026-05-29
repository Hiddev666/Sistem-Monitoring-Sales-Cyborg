# Planning Pengerjaan Modul Manager

Sumber: `analisis-manager.md`

Tanggal planning: 2026-05-28

## Tujuan

Menutup gap yang masih tersisa pada modul manager, dengan fokus pada:

1. Konsistensi route alias manager di view.
2. Shortcut kerja yang lebih cepat dari dashboard manager ke report/export.
3. Hardening scope manager supaya tidak bergantung penuh pada asumsi `wilayah_id` selalu ada.

## Prinsip Implementasi

- Perubahan dibuat kecil dan aman, karena modul manager sudah fungsional.
- Route alias manager dipakai konsisten di view agar UX tidak campur antara `admin.*` dan `manager.*`.
- Shortcut dashboard harus langsung berguna untuk kerja harian manager.
- Validasi scope harus ada di backend, bukan hanya asumsi dari data seed atau form.
- Test ditambahkan untuk memastikan behavior manager tetap read-only dan scope wilayah tetap aman.

## Partial 1 - Konsistensi Alias Route Manager di View

### Gap yang Diselesaikan

- Beberapa view analytics masih hardcode ke `admin.analytics.*` dan `admin.reports.*`.
- Secara fungsi masih jalan, tetapi naming manager belum konsisten.

### Target Hasil

View yang dipakai manager lebih konsisten memakai alias `manager.*`:

- `manager.analytics.dashboard`
- `manager.analytics.sales-performance`
- `manager.analytics.regional-performance`
- `manager.analytics.klien-analysis`
- `manager.reports.*`

### File Terdampak

- `resources/views/layouts/app.blade.php`
- `resources/views/admin/analytics/dashboard.blade.php`
- `resources/views/admin/analytics/sales-performance.blade.php`
- `resources/views/admin/analytics/regional-performance.blade.php`
- `resources/views/admin/analytics/klien-analysis.blade.php`
- Opsional: test akses manager jika perlu penyesuaian assert route

### Langkah Teknis

1. Audit semua link analytics/report yang bisa dibuka manager.
2. Ganti link view yang spesifik manager ke route alias `manager.*`.
3. Pertahankan route `admin.*` agar link lama tetap valid.
4. Pastikan tombol export di halaman analytics tetap mengarah ke route yang benar.
5. Tambahkan test sederhana jika ada link yang berpotensi rusak.

### Verifikasi

- `php artisan test --filter=ManagerReportAccessTest`
- `php artisan test --filter=ManagerReportScopeTest`
- Manual: login manager lalu buka sidebar dan halaman analytics.

### Risiko

- Jika ada link yang masih dipakai admin dan manager bersama, jangan dipaksa ganti semua sekaligus.
- Hindari duplikasi route yang membingungkan jika view dipakai lintas role.

## Partial 2 - Shortcut Report/Export di Dashboard Manager

### Gap yang Diselesaikan

- Dashboard manager belum punya shortcut langsung ke report/export.
- Manager masih harus masuk ke analytics dulu untuk download laporan.

### Target Hasil

Dashboard manager punya shortcut yang lebih cepat untuk kerja harian:

- buka analytics ringkasan,
- buka sales performance,
- akses export report secara langsung atau satu langkah dari dashboard.

### File Terdampak

- `resources/views/manager/dashboard.blade.php`
- `resources/views/admin/analytics/sales-performance.blade.php`
- Opsional: `routes/web.php` bila ingin menambah route alias report khusus manager

### Langkah Teknis

1. Tambahkan quick action di dashboard manager untuk report/export yang paling sering dipakai.
2. Jika perlu, buat shortcut langsung ke halaman sales performance dengan filter default.
3. Pastikan tombol tersebut memakai route yang aman untuk manager.
4. Jangan ubah behavior export yang sudah ada, hanya permudah aksesnya.
5. Tambahkan test akses dashboard manager dan assert shortcut baru muncul.

### Verifikasi

- `php artisan test --filter=Phase10OperationalSmokeTest`
- `php artisan test --filter=ManagerReportAccessTest`
- Manual: login manager dan cek shortcut baru di dashboard.

### Risiko

- Terlalu banyak tombol cepat bisa membuat dashboard ramai.
- Prioritaskan satu atau dua shortcut yang paling relevan, bukan semua report sekaligus.

## Partial 3 - Guard `wilayah_id` Manager

### Gap yang Diselesaikan

- Scope manager bergantung pada asumsi bahwa manager selalu punya `wilayah_id`.
- Flow create/update user sudah mewajibkan `wilayah_id`, tetapi guard backend belum eksplisit.

### Target Hasil

Manager tanpa `wilayah_id` tidak lolos ke view data lintas wilayah secara tidak sengaja.

Pilihan implementasi yang aman:

- validasi/guard akses manager tanpa wilayah,
- atau fallback yang jelas dan aman,
- namun tidak boleh diam-diam membuka data lebih luas dari yang semestinya.

### File Terdampak

- `app/Http/Controllers/Dashboard/ManagerDashboardController.php`
- `app/Http/Controllers/Dashboard/AnalyticsController.php`
- `app/Http/Controllers/Admin/ReportExportController.php`
- Opsional: `app/Models/User.php` jika ingin helper role/scope baru
- Baru atau update test scope manager

### Langkah Teknis

1. Tentukan kebijakan final untuk manager tanpa `wilayah_id`.
2. Terapkan guard yang konsisten di dashboard, analytics, dan export report.
3. Jika manager tanpa wilayah dianggap data invalid, redirect atau abort dengan pesan yang jelas.
4. Jika manager tanpa wilayah harus tetap bisa akses, pastikan scope-nya tetap aman dan tidak melebar.
5. Tambahkan test untuk case manager tanpa `wilayah_id`.

### Verifikasi

- `php artisan test --filter=ManagerReportScopeTest`
- `php artisan test --filter=ManagerReportAccessTest`
- Manual: coba login manager yang tidak punya `wilayah_id` jika data itu memang bisa dibuat.

### Risiko

- Perubahan guard bisa memengaruhi data seed lama atau akun manager lama.
- Jika banyak data historis manager tanpa wilayah, perlu migrasi data atau keputusan bisnis yang tegas.

## Missing

### Tidak ada gap fungsional besar yang benar-benar hilang

Status: missing = tidak ditemukan fitur manager inti yang benar-benar belum berjalan.

Catatan:

- Modul manager inti sudah bisa dipakai.
- Jadi `missing` pada planning ini tidak berisi pekerjaan wajib baru.

## Urutan Eksekusi yang Disarankan

1. Partial 3 - Guard `wilayah_id` manager.
2. Partial 1 - Konsistensi alias route manager di view.
3. Partial 2 - Shortcut report/export di dashboard manager.

Alasan urutan:

- Guard scope adalah isu paling sensitif karena menyangkut akses data.
- Setelah scope aman, konsistensi UX route bisa dirapikan.
- Terakhir, tambahkan shortcut dashboard agar perubahan UX terasa langsung tetapi tidak mengganggu akses dasar.

## Checklist Verifikasi Akhir

Setelah semua perubahan selesai:

- `php artisan test --filter=ManagerReportAccessTest`
- `php artisan test --filter=ManagerReportScopeTest`
- `php artisan test --filter=Phase10OperationalSmokeTest`
- `php artisan test`
- Manual:
  - login manager,
  - buka dashboard,
  - buka analytics,
  - export report,
  - pastikan scope wilayah tetap benar.
