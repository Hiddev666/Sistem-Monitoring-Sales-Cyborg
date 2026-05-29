# Analisis Modul Sales

Sumber pembanding utama:
- `sales-bug-planning.md`
- kode modul sales di `app/`, `resources/views/sales/`, `resources/views/layouts/sales.blade.php`, `routes/web.php`, dan test sales di `tests/Feature/`

## Done

- `sales.dashboard` sudah fungsional dan memakai data nyata: absensi hari ini, jadwal hari ini, progress kunjungan, dan riwayat kunjungan terakhir. Lihat [app/Http/Controllers/Dashboard/SalesDashboardController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Dashboard/SalesDashboardController.php#L19) dan [resources/views/sales/dashboard.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/dashboard.blade.php#L1).
- Layout sales sudah konsisten memakai `layouts.sales` untuk dashboard, absensi, PJP, dan visit form. Lihat [resources/views/layouts/sales.blade.php](/D:/cyborg/sistem-sales/resources/views/layouts/sales.blade.php#L1), [resources/views/sales/attendance/index.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/attendance/index.blade.php#L1), [resources/views/sales/pjp/today.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/today.blade.php#L1), dan [resources/views/sales/pjp/visit-form.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/visit-form.blade.php#L1).
- Navigasi sales sudah mengarah ke route nyata, bukan placeholder. Bottom nav dan quick action dashboard sudah memakai `sales.dashboard`, `sales.pjp.today`, dan `sales.attendance.index`. Lihat [resources/views/layouts/sales.blade.php](/D:/cyborg/sistem-sales/resources/views/layouts/sales.blade.php#L368) dan [resources/views/sales/dashboard.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/dashboard.blade.php#L154).
- Attendance sales sudah berjalan end-to-end: check-in, check-out, status hari ini, dan riwayat absensi. Lihat [app/Http/Controllers/AbsensiController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/AbsensiController.php#L37) dan [resources/views/sales/attendance/index.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/attendance/index.blade.php#L1).
- Aturan checkout absensi sudah ditegakkan: sales tidak bisa pulang kalau masih ada kunjungan aktif yang belum selesai atau dilewati. Lihat [app/Http/Controllers/AbsensiController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/AbsensiController.php#L98) dan [tests/Feature/SalesAttendanceCheckoutRuleTest.php](/D:/cyborg/sistem-sales/tests/Feature/SalesAttendanceCheckoutRuleTest.php#L17).
- PJP sales sudah berjalan dengan urutan kunjungan yang dijaga, check-in berbasis GPS, dan pemisahan antara jadwal milik sendiri vs jadwal orang lain. Lihat [app/Http/Controllers/SalesPJPController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/SalesPJPController.php#L163) dan [tests/Feature/SalesPJPVisitOrderTest.php](/D:/cyborg/sistem-sales/tests/Feature/SalesPJPVisitOrderTest.php#L16).
- Konfigurasi radius GPS untuk check-in sudah benar-benar dipakai dan punya fallback default. Lihat [app/Http/Controllers/SalesPJPController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/SalesPJPController.php#L197) dan [tests/Feature/SalesPJPCheckInConfigurationTest.php](/D:/cyborg/sistem-sales/tests/Feature/SalesPJPCheckInConfigurationTest.php#L18).
- Visit form untuk foto, tanda tangan digital, catatan kunjungan, nominal transaksi, dan submit form sudah aktif. Lihat [app/Http/Controllers/VisitFormController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/VisitFormController.php#L50) dan [tests/Feature/VisitFormSubmitTest.php](/D:/cyborg/sistem-sales/tests/Feature/VisitFormSubmitTest.php#L15).
- Indikator tracking di layout sales sudah ada dan route update lokasi sales juga aktif. Lihat [resources/views/layouts/sales.blade.php](/D:/cyborg/sistem-sales/resources/views/layouts/sales.blade.php#L330) dan [app/Http/Controllers/Api/LocationController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Api/LocationController.php#L22).
- Login sales memang diarahkan ke dashboard sales yang sudah fungsional. Lihat [app/Http/Controllers/Auth/LoginController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Auth/LoginController.php#L70) dan [routes/web.php](/D:/cyborg/sistem-sales/routes/web.php#L30).
- Status fase di `sales-bug-planning.md` sudah selaras dengan implementasi saat ini: fase 1 sampai fase 4 ditandai selesai.

## Partial

- Normalisasi status masih bercampur antara nilai baru dan legacy, misalnya `active`, `aktif`, `completed`, `selesai`, dan `skipped`. Sistem sekarang sudah toleran terhadap variasi itu, tetapi model dan view masih belum memakai satu sumber kebenaran status yang seragam. Lihat [app/Models/JadwalKlien.php](/D:/cyborg/sistem-sales/app/Models/JadwalKlien.php#L14), [app/Models/JadwalKunjungan.php](/D:/cyborg/sistem-sales/app/Models/JadwalKunjungan.php#L14), [app/Http/Controllers/VisitFormController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/VisitFormController.php#L282), dan [resources/views/sales/pjp/show.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/show.blade.php#L140).
- Endpoint checkout klien belum mengecek ulang apakah klien yang di-checkout adalah `current visit`. UI memang hanya menampilkan tombol untuk kunjungan aktif saat ini, jadi alur normal aman, tetapi direct access ke route masih bisa melewati guard urutan di sisi controller. Lihat [app/Http/Controllers/SalesPJPController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/SalesPJPController.php#L259).
- Endpoint `sales.pjp.next-klien` dan `sales.pjp.progress` sudah berjalan, tetapi belum punya konsumsi UI yang kuat di flow sales utama. Secara teknis endpoint ini hidup dan diuji, namun fungsinya masih lebih sebagai API pendukung daripada fitur yang benar-benar tampil ke pengguna. Lihat [app/Http/Controllers/SalesPJPController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/SalesPJPController.php#L299) dan [app/Http/Controllers/SalesPJPController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/SalesPJPController.php#L334).
- Beberapa aksi di view sales masih memakai URL string langsung di JavaScript, bukan route helper penuh. Ini tidak memblokir fungsi, tetapi membuat maintenance lebih rapuh jika prefix route berubah. Lihat [resources/views/sales/pjp/today.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/today.blade.php#L179) dan [resources/views/sales/pjp/visit-form.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/visit-form.blade.php#L372).

## Missing

- Tidak ada gap besar yang benar-benar masih hilang dari rencana awal modul sales.
- Fitur inti yang direncanakan sudah tersedia dan sudah tercakup oleh test: dashboard, navigasi, absensi, PJP berurutan, GPS check-in, visit form, dan tracking lokasi.

## Kesimpulan

Modul sales pada kondisi sekarang secara fungsional sudah sesuai dengan rencana awal di `sales-bug-planning.md`. Yang tersisa bukan fitur yang hilang, melainkan beberapa titik rapuh yang sebaiknya dirapikan kalau ingin menurunkan risiko regresi:

- konsolidasi status antar model dan view,
- penguncian checkout klien agar benar-benar hanya bisa untuk kunjungan aktif saat ini,
- dan perapian penggunaan route helper di JavaScript view.
