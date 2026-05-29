# Analisis Modul Manager

Tanggal analisis: 2026-05-28

## Scope

Analisis ini fokus ke modul manager yang relevan dengan rencana awal dan alur operasional saat ini:

- Dashboard manager
- Monitoring real-time manager
- Analytics dan export report
- Scope wilayah manager
- Alias route manager

## Done

### Dashboard manager operasional

Status: done

Bukti:

- [app/Http/Controllers/Dashboard/ManagerDashboardController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Dashboard/ManagerDashboardController.php)
- [resources/views/manager/dashboard.blade.php](/D:/cyborg/sistem-sales/resources/views/manager/dashboard.blade.php)
- [tests/Feature/Phase5/Phase5IntegrationTest.php](/D:/cyborg/sistem-sales/tests/Feature/Phase5/Phase5IntegrationTest.php)
- [tests/Feature/Phase10OperationalSmokeTest.php](/D:/cyborg/sistem-sales/tests/Feature/Phase10OperationalSmokeTest.php)

Catatan:

- Dashboard manager bisa diakses role manager.
- Statistik dashboard diambil dari database, bukan statis.
- Partial monitoring real-time sudah ditampilkan langsung di dashboard.

### Monitoring real-time manager

Status: done

Bukti:

- [resources/views/dashboard/partials/realtime-monitoring.blade.php](/D:/cyborg/sistem-sales/resources/views/dashboard/partials/realtime-monitoring.blade.php)
- [app/Http/Controllers/Api/LocationController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Api/LocationController.php)
- [resources/views/manager/dashboard.blade.php](/D:/cyborg/sistem-sales/resources/views/manager/dashboard.blade.php)
- [tests/Feature/Phase5/LocationControllerTest.php](/D:/cyborg/sistem-sales/tests/Feature/Phase5/LocationControllerTest.php)

Catatan:

- Manager melihat sales berdasarkan scope wilayah.
- Endpoint dashboard lokasi menolak akses sales.
- Partial yang sama dipakai juga oleh admin, jadi implementasi tidak duplikatif.

### Analytics dan export report manager

Status: done

Bukti:

- [app/Http/Controllers/Dashboard/AnalyticsController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Dashboard/AnalyticsController.php)
- [app/Http/Controllers/Admin/ReportExportController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Admin/ReportExportController.php)
- [resources/views/admin/analytics/dashboard.blade.php](/D:/cyborg/sistem-sales/resources/views/admin/analytics/dashboard.blade.php)
- [resources/views/admin/analytics/sales-performance.blade.php](/D:/cyborg/sistem-sales/resources/views/admin/analytics/sales-performance.blade.php)
- [resources/views/admin/analytics/regional-performance.blade.php](/D:/cyborg/sistem-sales/resources/views/admin/analytics/regional-performance.blade.php)
- [resources/views/admin/analytics/klien-analysis.blade.php](/D:/cyborg/sistem-sales/resources/views/admin/analytics/klien-analysis.blade.php)
- [tests/Feature/ManagerReportAccessTest.php](/D:/cyborg/sistem-sales/tests/Feature/ManagerReportAccessTest.php)
- [tests/Feature/ManagerReportScopeTest.php](/D:/cyborg/sistem-sales/tests/Feature/ManagerReportScopeTest.php)

Catatan:

- Manager bisa membuka analytics pages.
- Manager bisa export laporan.
- Scope wilayah dibatasi oleh `managerWilayahScope()` pada analytics.

### Alias route manager tersedia

Status: done

Bukti:

- [routes/web.php](/D:/cyborg/sistem-sales/routes/web.php)
- [tests/Feature/ManagerReportAccessTest.php](/D:/cyborg/sistem-sales/tests/Feature/ManagerReportAccessTest.php)

Catatan:

- Route alias `manager.analytics.*` tersedia.
- Route alias `manager.reports.*` juga tersedia.
- Login manager diarahkan ke dashboard manager.

## Partial

### Adoptasi alias manager belum konsisten di view

Status: partial

Bukti:

- [resources/views/layouts/app.blade.php](/D:/cyborg/sistem-sales/resources/views/layouts/app.blade.php)
- [resources/views/admin/analytics/dashboard.blade.php](/D:/cyborg/sistem-sales/resources/views/admin/analytics/dashboard.blade.php)
- [resources/views/admin/analytics/sales-performance.blade.php](/D:/cyborg/sistem-sales/resources/views/admin/analytics/sales-performance.blade.php)
- [resources/views/admin/analytics/regional-performance.blade.php](/D:/cyborg/sistem-sales/resources/views/admin/analytics/regional-performance.blade.php)
- [resources/views/admin/analytics/klien-analysis.blade.php](/D:/cyborg/sistem-sales/resources/views/admin/analytics/klien-analysis.blade.php)

Yang sudah ada:

- Menu sidebar manager sudah mengarah ke route `manager.analytics.*`.
- Alias route manager memang tersedia di backend.

Yang belum konsisten:

- Beberapa tombol di halaman analytics masih hardcode ke `admin.analytics.*` dan `admin.reports.*`.
- Secara fungsi tetap jalan karena manager diizinkan mengakses route itu.
- Secara UX dan naming, penggunaan alias manager belum menyeluruh.

Dampak:

- Tidak memblokir fitur.
- Tetapi target penyederhanaan UX untuk manager belum sepenuhnya tercapai.

### Dashboard manager belum punya shortcut report/export yang eksplisit

Status: partial

Bukti:

- [resources/views/manager/dashboard.blade.php](/D:/cyborg/sistem-sales/resources/views/manager/dashboard.blade.php)
- [resources/views/layouts/app.blade.php](/D:/cyborg/sistem-sales/resources/views/layouts/app.blade.php)

Yang sudah ada:

- Manager bisa masuk ke analytics dari sidebar.
- Halaman analytics menyediakan tombol export.

Yang masih kurang:

- Tidak ada tombol cepat di dashboard manager untuk export report langsung.
- Shortcut cepat masih bergantung ke navigasi sidebar atau halaman analytics.

Dampak:

- Fungsionalitas tidak hilang.
- Tetapi jalur kerja manager belum secepat yang ideal untuk kebutuhan review harian.

### Scope manager tanpa `wilayah_id` belum ditangani secara konsisten

Status: partial

Bukti:

- [app/Http/Controllers/Dashboard/ManagerDashboardController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Dashboard/ManagerDashboardController.php)
- [app/Http/Controllers/Dashboard/AnalyticsController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Dashboard/AnalyticsController.php)
- [database/seeders/CreateTestUsersSeeder.php](/D:/cyborg/sistem-sales/database/seeders/CreateTestUsersSeeder.php)
- [app/Http/Controllers/Admin/UserController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Admin/UserController.php)

Yang sudah ada:

- Flow resmi create/update user mewajibkan `wilayah_id`.
- Seeder test user juga memberi `wilayah_id` ke manager.

Yang belum konsisten:

- `ManagerDashboardController` hanya memfilter data bila `wilayah_id` ada.
- Jika manager tanpa `wilayah_id` lolos ke sistem, dashboard ini bisa fallback ke data yang lebih luas dari yang seharusnya.
- `AnalyticsController` menangani kasus itu dengan cara berbeda, jadi perilakunya tidak seragam.

Dampak:

- Pada data normal masalah ini tidak terlihat.
- Tetapi dari sisi hardening akses, belum ada guard eksplisit yang menolak manager tanpa wilayah.

## Missing

### Tidak ada gap besar yang benar-benar memblokir modul manager inti

Status: missing = tidak ditemukan fitur manager inti yang benar-benar belum berjalan.

Kesimpulan:

- Dashboard manager berjalan.
- Monitoring real-time berjalan.
- Analytics dan export berjalan.
- Alias route manager tersedia.

Jadi kategori `missing` untuk modul manager inti saat ini kosong.

## Ringkasan Akhir

| Area | Status |
|---|---|
| Dashboard manager | done |
| Monitoring real-time manager | done |
| Analytics dan export report | done |
| Alias route manager | done |
| Shortcut report/export di dashboard manager | partial |
| Adopsi alias manager di view | partial |
| Guard `wilayah_id` manager | partial |
| Fitur manager inti yang belum berjalan | missing |

## Penilaian Akhir

Secara fungsional, modul manager sudah berjalan untuk kebutuhan utama: melihat dashboard, memantau lokasi sales, membuka analytics, dan export report.

Sisa gap yang paling relevan ada di area:

- konsistensi route alias manager di view,
- shortcut kerja yang lebih cepat dari dashboard,
- dan hardening scope manager supaya tidak bergantung penuh pada asumsi bahwa semua manager pasti punya `wilayah_id`.
