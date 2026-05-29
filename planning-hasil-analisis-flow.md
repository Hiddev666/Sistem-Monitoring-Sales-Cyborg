# Planning Penyelesaian Gap Flow Aplikasi

Sumber: `hasil-analisis-flow.md`

Tanggal planning: 2026-05-28

## Tujuan

Menyelesaikan gap antara alur ideal pada PDF dan implementasi codebase saat ini, dengan prioritas pada:

1. Membuat dashboard admin benar-benar operasional.
2. Menyediakan monitoring real-time untuk admin/super admin.
3. Memastikan PJP selalu memiliki daftar klien.
4. Menyamakan basis tanggal laporan dengan tanggal jadwal kunjungan.
5. Menguatkan aturan urutan kunjungan, absensi pulang, session timeout, dan UX tracking lokasi.

## Prinsip Implementasi

- Perubahan dibuat bertahap agar mudah dites dan tidak mengganggu flow sales yang sudah berjalan.
- Query laporan berbasis aktivitas kunjungan harus mengacu ke `jadwal_kunjungan.tanggal`, bukan `jadwal_klien.created_at`, kecuali memang yang dicari adalah tanggal input data.
- Validasi bisnis penting harus berada di backend, bukan hanya di UI.
- Role manager tetap read-only dan dibatasi wilayah.
- Admin/super admin boleh melihat data lintas wilayah.
- Test ditambahkan atau diperbarui setiap fase yang mengubah perilaku backend.

## Fase 1 - Dashboard Admin Operasional

### Gap yang Diselesaikan

- Dashboard admin masih statis.
- Angka di dashboard admin masih `0`.
- Link quick action masih `#`.

### Target Hasil

Dashboard admin menampilkan ringkasan nyata:

- Total pengguna aktif.
- Total sales aktif.
- Total klien aktif.
- Total wilayah.
- PJP hari ini.
- Kunjungan hari ini.
- Kunjungan selesai hari ini.
- Absensi aktif hari ini.

Dashboard admin juga memiliki link yang benar ke:

- Kelola pengguna.
- Kelola klien.
- Kelola wilayah.
- Buat PJP.
- Rekap absensi.
- Galeri dokumentasi.
- Analytics.
- Reports/export.

### File Terdampak

- `app/Http/Controllers/Dashboard/AdminDashboardController.php`
- `resources/views/admin/dashboard.blade.php`
- Opsional: `tests/Feature/AdminDashboardTest.php`

### Langkah Teknis

1. Update `AdminDashboardController@index` untuk menghitung statistik dari database.
2. Gunakan query yang relevan:
   - `User::count()`
   - `User::role('sales')->active()->count()`
   - `Klien::active()->count()`
   - `Wilayah::count()`
   - `JadwalKunjungan::whereDate('tanggal', today())->count()`
   - `JadwalKlien::whereHas('jadwalKunjungan', fn ($q) => $q->whereDate('tanggal', today()))`
   - `Absensi::whereDate('tanggal', today())->whereNotNull('waktu_masuk')->whereNull('waktu_keluar')`
3. Kirim data statistik ke view.
4. Ganti nilai statis `0` di blade dengan variabel.
5. Ganti semua link `#` dengan route yang benar.
6. Tambahkan test dashboard admin menampilkan data nyata dan hanya bisa diakses admin/super_admin.

### Verifikasi

- `php artisan test --filter=AdminDashboardTest`
- `php artisan test`
- Manual: login admin, buka `/admin/dashboard`, cek angka dan link.

### Risiko

- Nama route harus dicek agar tidak salah.
- Jika beberapa model tidak punya scope `active`, gunakan `where('is_active', true)`.

## Fase 2 - Monitoring Real-Time untuk Admin dan Super Admin

### Gap yang Diselesaikan

- Admin bisa mengakses API map, tetapi belum punya halaman map real-time.
- PDF menyebut admin dan manager dapat memonitor aktivitas sales.

### Target Hasil

Admin dan super admin dapat melihat peta monitoring real-time seperti manager.

### Opsi Implementasi

Opsi yang direkomendasikan: buat komponen/partial dashboard map bersama.

Alasannya:

- Menghindari duplikasi JavaScript Leaflet.
- Manager dan admin memakai data endpoint yang sama.
- Perawatan lebih mudah.

### File Terdampak

- `resources/views/manager/dashboard.blade.php`
- `resources/views/admin/dashboard.blade.php`
- Baru: `resources/views/dashboard/partials/realtime-map.blade.php`
- Baru atau update: `resources/views/dashboard/partials/realtime-stat-cards.blade.php`
- `app/Http/Controllers/Dashboard/AdminDashboardController.php`
- `app/Http/Controllers/Dashboard/ManagerDashboardController.php`
- `app/Http/Controllers/Api/LocationController.php`
- `routes/web.php` jika perlu route halaman monitoring khusus.

### Langkah Teknis

1. Ekstrak bagian peta dan alert dari `manager/dashboard.blade.php` ke partial reusable.
2. Pastikan partial memakai route `api.dashboard.sales-locations`.
3. Render partial tersebut di dashboard manager dan dashboard admin.
4. Untuk admin/super_admin, API tidak memfilter wilayah.
5. Untuk manager, API tetap filter wilayah manager.
6. Pastikan empty state tetap muncul saat tidak ada lokasi sales hari ini.
7. Tambahkan test API:
   - manager hanya melihat sales wilayah sendiri.
   - admin/super_admin melihat semua sales.
   - sales tidak bisa akses API dashboard.

### Verifikasi

- `php artisan test --filter=LocationControllerTest`
- `php artisan test --filter=Phase5IntegrationTest`
- Manual:
  - Login manager, map hanya menampilkan sales wilayah manager.
  - Login admin, map menampilkan sales lintas wilayah.
  - Login sales, API dashboard harus 403.

### Risiko

- Leaflet CSS/JS harus hanya dimuat sekali dari layout utama.
- ID elemen map harus unik jika suatu halaman punya lebih dari satu map.

## Fase 3 - PJP Wajib Memiliki Minimal Satu Klien

### Gap yang Diselesaikan

- PJP bisa dibuat tanpa daftar klien.

### Target Hasil

Admin tidak bisa membuat PJP tanpa klien. PJP baru wajib memiliki minimal satu klien.

### File Terdampak

- `app/Http/Controllers/Admin/PJPController.php`
- `resources/views/admin/pjp/create.blade.php`
- `tests/Feature/...` test PJP admin, jika ada.
- Baru: `tests/Feature/AdminPJPTest.php` jika belum ada.

### Langkah Teknis

1. Ubah validasi `store`:
   - dari `klien => nullable|array`
   - menjadi `klien => required|array|min:1`
2. Pastikan `klien.* => exists:klien,id` tetap ada.
3. Pastikan form create menampilkan error jika tidak ada klien dipilih.
4. Tambahkan test:
   - gagal create PJP tanpa klien.
   - berhasil create PJP dengan minimal satu klien.
   - urutan klien tersimpan sesuai urutan input.

### Verifikasi

- `php artisan test --filter=AdminPJPTest`
- Manual: coba submit create PJP tanpa klien, harus validasi error.

### Risiko

- Seeder atau test lama yang membuat PJP tanpa klien bisa gagal dan perlu disesuaikan.

## Fase 4 - Samakan Basis Tanggal Analytics dan Report

### Gap yang Diselesaikan

- Beberapa analytics memakai `jadwal_klien.created_at`.
- Photo gallery juga memakai `created_at`.
- Laporan bisa tidak sesuai tanggal aktivitas kunjungan.

### Target Hasil

Filter tanggal pada laporan performa, dashboard analytics, dan dokumentasi kunjungan mengikuti `jadwal_kunjungan.tanggal`.

### File Terdampak

- `app/Http/Controllers/Dashboard/AnalyticsController.php`
- `app/Http/Controllers/PhotoGalleryController.php`
- `app/Services/ReportService.php`
- Test terkait:
  - `tests/Feature/AdminReportExportTest.php`
  - `tests/Feature/ManagerReportScopeTest.php`
  - `tests/Feature/Phase5/Phase5IntegrationTest.php`

### Langkah Teknis

1. Audit semua query yang memakai `jadwal_klien.created_at`.
2. Untuk data kunjungan, ubah filter ke relasi/join `jadwal_kunjungan.tanggal`.
3. Buat helper query bila perlu:
   - `scopeVisitDateRange($query, $startDate, $endDate)`
   - atau gunakan `whereHas('jadwalKunjungan', ...)`.
4. Update `AnalyticsController`:
   - `adminDashboard`
   - `klienAnalysis`
   - helper daily trend
   - top klien by visits
   - average visit duration
5. Update `PhotoGalleryController`:
   - index filter tanggal
   - grid filter tanggal
   - export zip filter tanggal
   - statistics filter tanggal
6. Update `ReportService` agar semua export memakai tanggal jadwal.
7. Tambahkan/regresi test:
   - Jika `jadwal_klien.created_at` berbeda dari `jadwal_kunjungan.tanggal`, filter laporan tetap mengikuti tanggal jadwal.
   - Manager hanya dapat melihat laporan wilayah sendiri.

### Verifikasi

- `php artisan test --filter=AdminReportExportTest`
- `php artisan test --filter=ManagerReportScopeTest`
- `php artisan test --filter=Phase5IntegrationTest`
- `php artisan test`

### Risiko

- Query join dapat membuat duplikasi row jika tidak hati-hati.
- Beberapa tampilan mungkin memang menampilkan tanggal upload/dokumentasi; perlu bedakan label "tanggal kunjungan" vs "tanggal input".

## Fase 5 - Enforcement Urutan Kunjungan

### Gap yang Diselesaikan

- Urutan kunjungan tersedia, tetapi belum dipastikan selalu dipaksa backend.

### Target Hasil

Sales hanya bisa check-in ke klien yang sedang aktif atau klien pending pertama sesuai `urutan`.

### File Terdampak

- `app/Http/Controllers/SalesPJPController.php`
- `app/Models/JadwalKlien.php`
- `resources/views/sales/pjp/show.blade.php`
- `resources/views/sales/pjp/today.blade.php`
- Test terkait:
  - `tests/Feature/SalesPJPNextKlienTest.php`
  - Baru: `tests/Feature/SalesPJPVisitOrderTest.php`

### Langkah Teknis

1. Buat helper pada model atau controller untuk menentukan current visit:
   - active visit jika ada.
   - jika tidak ada, pending pertama by `urutan`.
2. Di `checkInKlien`, tolak jika `jadwalKlien->id` bukan current visit.
3. Pastikan completed/skipped tidak bisa di-check-in ulang.
4. UI hanya mengaktifkan tombol check-in pada current visit.
5. Tambahkan test:
   - sales gagal check-in klien urutan kedua saat klien pertama masih pending.
   - sales berhasil check-in urutan kedua setelah urutan pertama completed/skipped.

### Verifikasi

- `php artisan test --filter=SalesPJPVisitOrderTest`
- `php artisan test --filter=SalesPJPNextKlienTest`
- Manual: sales mencoba klik klien di luar urutan.

### Risiko

- Jika bisnis membolehkan kunjungan acak, fase ini perlu konfirmasi sebelum implementasi.

## Fase 6 - Aturan Checkout Absensi Setelah Aktivitas Selesai

### Gap yang Diselesaikan

- Sales bisa saja checkout absensi sebelum PJP selesai.

### Target Hasil

Sales tidak bisa absensi pulang jika masih ada jadwal aktif hari ini dengan kunjungan yang belum selesai/skipped.

### File Terdampak

- `app/Http/Controllers/AbsensiController.php`
- `resources/views/sales/attendance/index.blade.php`
- Baru: `tests/Feature/SalesAttendanceCheckoutRuleTest.php`

### Langkah Teknis

1. Di `AbsensiController::checkOut`, cek jadwal hari ini milik sales.
2. Jika ada jadwal status `aktif` dan masih ada `jadwal_klien` status selain `completed`/`skipped`, return 400.
3. Pesan error harus jelas: "Selesaikan semua kunjungan sebelum absensi pulang."
4. UI attendance menampilkan pesan yang sama jika checkout ditolak.
5. Tambahkan test:
   - checkout absensi gagal saat ada kunjungan belum selesai.
   - checkout absensi berhasil setelah semua kunjungan completed/skipped.
   - checkout tetap berhasil jika sales tidak punya jadwal hari ini, sesuai keputusan bisnis.

### Verifikasi

- `php artisan test --filter=SalesAttendanceCheckoutRuleTest`
- `php artisan test`

### Risiko

- Perlu keputusan bisnis untuk sales tanpa jadwal: boleh checkout atau tidak.

## Fase 7 - Session Timeout dari Konfigurasi

### Gap yang Diselesaikan

- Konfigurasi `session_timeout_minutes` ada, tetapi belum terlihat diterapkan.

### Target Hasil

Session user otomatis dianggap expired berdasarkan konfigurasi sistem.

### File Terdampak

- Baru: `app/Http/Middleware/SessionTimeout.php`
- `bootstrap/app.php` atau lokasi registrasi middleware Laravel yang dipakai project.
- `app/Http/Controllers/Admin/ConfigurationController.php`
- `resources/views/admin/configuration/index.blade.php`
- Test baru: `tests/Feature/SessionTimeoutTest.php`

### Langkah Teknis

1. Buat middleware `SessionTimeout`.
2. Baca nilai `Configuration::getValue('session_timeout_minutes', 120)`.
3. Simpan timestamp aktivitas terakhir di session.
4. Jika idle melebihi konfigurasi:
   - logout user,
   - invalidate session,
   - regenerate CSRF token,
   - redirect ke login dengan pesan session expired.
5. Daftarkan middleware pada route auth group.
6. Pastikan route login/logout tidak terkena loop.
7. Tambahkan test:
   - user masih aktif sebelum timeout.
   - user logout otomatis setelah timeout.
   - perubahan konfigurasi timeout mempengaruhi middleware.

### Verifikasi

- `php artisan test --filter=SessionTimeoutTest`
- Manual: set timeout kecil, login, idle, akses halaman protected.

### Risiko

- Jika middleware membaca DB setiap request, ada overhead kecil. Bisa dipertimbangkan cache konfigurasi.

## Fase 8 - UX Indikator Tracking Lokasi Sales

### Gap yang Diselesaikan

- Sales belum melihat status bahwa lokasi sedang dikirim setelah absensi check-in.

### Target Hasil

Sales mendapat indikator sederhana:

- Tracking aktif.
- Tracking nonaktif karena belum check-in atau sudah checkout.
- Permission GPS ditolak.
- Update lokasi terakhir berhasil/gagal.

### File Terdampak

- `resources/views/layouts/sales.blade.php`
- Opsional: partial kecil untuk status tracking.

### Langkah Teknis

1. Tambahkan elemen status tracking kecil di layout sales, misalnya di topbar atau toast non-intrusif.
2. Update JavaScript tracking:
   - set status saat attendance status dicek.
   - set status saat geolocation permission gagal.
   - set status saat POST lokasi berhasil/gagal.
3. Pastikan UI tidak mengganggu flow form kunjungan.
4. Hindari menampilkan detail teknis panjang ke sales.

### Verifikasi

- Manual di browser:
   - sebelum check-in absensi: tracking nonaktif.
   - setelah check-in: tracking aktif.
   - permission GPS deny: tampil pesan GPS ditolak.
   - setelah checkout absensi: tracking nonaktif.

### Risiko

- Browser permission geolocation berbeda-beda; status harus bersifat best-effort.

## Fase 9 - Naming Route Laporan Manager

### Gap yang Diselesaikan

- Manager mengakses laporan melalui prefix `admin/analytics`, secara UX membingungkan.

### Target Hasil

Manager punya alias route yang lebih sesuai, misalnya:

- `manager.analytics.dashboard`
- `manager.analytics.sales-performance`
- `manager.analytics.klien-analysis`
- `manager.analytics.regional-performance`
- `manager.reports.*`

### File Terdampak

- `routes/web.php`
- Layout/menu navigasi manager jika ada.
- View analytics jika memakai route name hardcoded.
- Test akses manager/admin.

### Langkah Teknis

1. Tambahkan route alias manager yang mengarah ke controller analytics/report yang sama.
2. Pertahankan route admin lama agar tidak merusak link lama.
3. Update menu manager agar memakai route `manager.analytics.*`.
4. Pastikan middleware manager tetap read-only dan scope wilayah tetap aktif.
5. Tambahkan test route alias manager.

### Verifikasi

- `php artisan route:list --name=manager.analytics`
- `php artisan test --filter=ManagerReportAccessTest`
- Manual: manager buka laporan dari menu tanpa prefix admin.

### Risiko

- Duplikasi route name harus dihindari.
- Export route perlu tetap aman terhadap akses wilayah lain.

## Urutan Eksekusi yang Disarankan

1. Fase 3 - PJP wajib punya klien.
2. Fase 1 - Dashboard admin operasional.
3. Fase 2 - Monitoring real-time untuk admin/super admin.
4. Fase 4 - Basis tanggal analytics/report.
5. Fase 5 - Enforcement urutan kunjungan.
6. Fase 6 - Aturan checkout absensi.
7. Fase 8 - UX indikator tracking lokasi.
8. Fase 7 - Session timeout.
9. Fase 9 - Naming route laporan manager.

Alasan urutan:

- Fase 3 kecil dan mengunci kualitas data PJP.
- Fase 1 dan 2 memperbaiki gap yang paling terlihat oleh admin/manager.
- Fase 4 menyentuh banyak query dan export, jadi dikerjakan setelah fondasi dashboard stabil.
- Fase 5 dan 6 adalah aturan bisnis yang bisa berdampak ke workflow sales, sehingga perlu test dan validasi lebih hati-hati.
- Fase 7 dan 9 lebih bersifat penyempurnaan platform/UX.

## Checklist Verifikasi Akhir

Setelah semua fase selesai:

- `php artisan test`
- Login sebagai sales:
  - absensi masuk,
  - lokasi terkirim,
  - lihat PJP,
  - mulai perjalanan,
  - check-in klien sesuai urutan,
  - upload foto,
  - tanda tangan,
  - submit form,
  - checkout absensi setelah semua selesai.
- Login sebagai manager:
  - dashboard map menampilkan sales wilayah sendiri,
  - alert muncul jika sales tidak bergerak,
  - laporan hanya wilayah sendiri,
  - export PDF/XLSX berhasil.
- Login sebagai admin:
  - dashboard menampilkan data nyata,
  - map real-time menampilkan semua sales,
  - CRUD master data berjalan,
  - PJP tidak bisa dibuat tanpa klien,
  - laporan/export mengikuti tanggal jadwal kunjungan.
- Login sebagai super admin:
  - akses admin penuh,
  - akses map dan laporan lintas wilayah.

## Keputusan Bisnis yang Direkomendasikan

Bagian ini menggantikan daftar pertanyaan konfirmasi. Keputusan berikut dipilih berdasarkan keamanan, integritas data, kemudahan audit, dan kelancaran operasional sales di lapangan.

### 1. Sales Wajib Mengikuti Urutan PJP

Keputusan: **sales wajib mengikuti urutan kunjungan sesuai PJP**.

Alasan:

- Mengurangi risiko manipulasi kunjungan dan ghost check-in.
- Membuat progress PJP lebih mudah diaudit.
- Membuat alert dan status "next client" lebih konsisten.
- Sesuai konsep PJP sebagai rute kunjungan terencana, bukan sekadar daftar bebas.

Implikasi implementasi:

- Backend harus menolak check-in ke klien di luar urutan.
- Klien yang bisa di-check-in hanya:
  - klien status `active`, jika ada,
  - atau klien `pending` pertama berdasarkan `urutan`.
- UI hanya mengaktifkan tombol check-in pada klien yang sedang boleh dikunjungi.
- Jika sales tidak bisa mengunjungi klien tertentu, harus ada mekanisme `skipped` dengan alasan.

### 2. Sales Boleh Checkout Absensi Jika Tidak Punya Jadwal Hari Itu

Keputusan: **sales boleh checkout absensi walaupun tidak punya jadwal PJP hari itu**.

Alasan:

- Absensi adalah catatan kehadiran kerja, tidak selalu identik dengan kunjungan PJP.
- Sales bisa saja masuk kerja untuk briefing, administrasi, training, follow-up non-kunjungan, atau aktivitas lain yang belum dijadwalkan.
- Memblokir checkout tanpa jadwal dapat membuat data absensi menggantung dan menyulitkan payroll/rekap kehadiran.

Implikasi implementasi:

- Jika tidak ada jadwal hari ini, checkout absensi tetap boleh.
- Jika ada jadwal hari ini dan statusnya `aktif`, checkout harus dicek terhadap penyelesaian kunjungan.
- Jika ada jadwal `pending` yang belum dimulai, checkout tetap boleh, tetapi bisa dipertimbangkan memberi warning di UI.

### 3. Sales Boleh Checkout Absensi Jika Kunjungan Dilewati dengan Status `skipped`

Keputusan: **sales boleh checkout absensi jika semua kunjungan sudah berstatus `completed` atau `skipped`**.

Alasan:

- Kondisi lapangan bisa berubah: toko tutup, kontak tidak ada, akses lokasi gagal, atau force majeure.
- Memaksa semua kunjungan menjadi `completed` akan mendorong input palsu.
- Status `skipped` menjaga audit trail lebih jujur dibanding membiarkan `pending` atau memaksa `completed`.

Implikasi implementasi:

- Checkout absensi ditolak jika masih ada kunjungan dengan status selain `completed` atau `skipped` pada jadwal aktif hari itu.
- Status `skipped` wajib menyimpan alasan minimal, misalnya:
  - `toko_tutup`
  - `kontak_tidak_ada`
  - `lokasi_tidak_dapat_diakses`
  - `dibatalkan_admin`
  - `lainnya`
- Untuk alasan `lainnya`, catatan wajib diisi.
- Data `skipped` harus masuk laporan agar manager/admin bisa mengevaluasi penyebab kunjungan gagal.

### 4. Admin Dashboard Perlu Map Ringkas di Halaman Utama dan Link ke Monitoring Detail

Keputusan: **admin dashboard menampilkan map real-time ringkas di halaman utama, ditambah link ke halaman monitoring detail jika dibutuhkan**.

Alasan:

- PDF menyebut admin ikut memonitor aktivitas sales.
- Admin butuh visibility cepat tanpa harus berpindah halaman.
- Map ringkas di dashboard membantu menemukan masalah operasional lebih cepat.
- Jika nanti data makin banyak, halaman detail bisa dipakai untuk filter, tabel, dan drill-down lebih lengkap.

Implikasi implementasi:

- Dashboard admin menampilkan stat cards, peta real-time, dan alert ringkas.
- Peta admin menampilkan semua sales lintas wilayah.
- Manager tetap melihat sales sesuai `wilayah_id`.
- Jika dibuat halaman detail, route bisa memakai nama seperti `admin.monitoring.index`.

### 5. Filter Photo Gallery Harus Mendukung Tanggal Kunjungan dan Tanggal Upload

Keputusan: **photo gallery menyediakan dua basis filter tanggal: tanggal kunjungan sebagai default, dan tanggal upload/dokumentasi sebagai opsi tambahan**.

Alasan:

- Untuk evaluasi operasional, tanggal yang paling penting adalah tanggal jadwal/kunjungan.
- Untuk audit file dan troubleshooting upload, tanggal upload juga berguna.
- Memakai hanya `created_at` rawan membuat laporan dokumentasi tidak sesuai aktivitas lapangan.
- Menyediakan dua opsi menghindari kehilangan kebutuhan audit teknis.

Implikasi implementasi:

- Default filter memakai `jadwal_kunjungan.tanggal`.
- Tambahkan opsi filter `date_basis`:
  - `visit_date` untuk tanggal jadwal kunjungan.
  - `upload_date` untuk tanggal record/dokumentasi dibuat.
- Label UI harus jelas agar admin/manager tahu filter sedang memakai basis tanggal apa.
- Export ZIP dan statistik photo gallery juga mengikuti basis tanggal yang dipilih.

## Ringkasan Keputusan Final

| Topik | Keputusan |
|---|---|
| Urutan PJP | Wajib diikuti sesuai urutan. |
| Checkout absensi tanpa jadwal | Boleh. |
| Checkout absensi dengan kunjungan dilewati | Boleh hanya jika kunjungan berstatus `skipped` dengan alasan. |
| Admin dashboard map | Tampilkan map ringkas di dashboard admin dan sediakan opsi halaman detail. |
| Filter photo gallery | Default tanggal kunjungan, tambah opsi tanggal upload. |
