# Planning Pengerjaan Modul Admin

Tanggal planning: 2026-05-28

Sumber: `analisis-admin.md`

## Tujuan

Menuntaskan sisa gap pada modul admin yang masih berstatus `partial`, dan memastikan tidak ada item `missing` yang benar-benar wajib dikerjakan.

Fokus:

- Konsistensi output photo gallery
- Penyederhanaan akses report dari dashboard admin
- Evaluasi kebutuhan halaman monitoring admin terpisah

## Status Umum

Hasil analisis menunjukkan:

- Modul admin inti sudah berjalan.
- Tidak ada gap fungsional besar yang masuk kategori `missing`.
- Sisa pekerjaan terutama berada di area UX dan konsistensi output.

## Prioritas Pengerjaan

1. Photo gallery export metadata
2. Shortcut report/export di dashboard admin
3. Evaluasi halaman monitoring admin terpisah

## Partial 1 - Photo Gallery Export Metadata

### Masalah

Photo gallery sudah mendukung `date_basis`, tetapi nama file download dan folder ZIP masih memakai `created_at`.

### Target

Metadata output galeri konsisten dengan konteks data yang dipilih, atau setidaknya tidak menimbulkan kesan bahwa output masih bergantung pada waktu record dibuat.

### File Terdampak

- `app/Http/Controllers/PhotoGalleryController.php`
- `resources/views/admin/gallery/index.blade.php`
- `resources/views/admin/gallery/grid.blade.php`
- `resources/views/admin/gallery/statistics.blade.php`
- Opsional: test baru di `tests/Feature/PhotoGalleryTest.php` atau test yang sudah ada

### Langkah Teknis

1. Audit fungsi `downloadPhoto()` dan `exportZip()`.
2. Tentukan apakah filename/folder akan:
   - tetap memakai `created_at` tetapi diberi label yang jelas, atau
   - mengikuti basis tanggal aktif dari request.
3. Jika memakai basis aktif, ambil parameter `date_basis`, `start_date`, dan `end_date` untuk membentuk label export.
4. Perbarui nama folder ZIP agar konsisten dengan label filter yang dipilih.
5. Perbarui view bila perlu agar user memahami basis tanggal yang dipakai saat export.
6. Tambahkan/regresi test untuk memastikan export tetap menghasilkan file yang benar.

### Verifikasi

- `php artisan test --filter=PhotoGallery`
- Manual:
  - buka galeri dengan `visit_date`
  - buka galeri dengan `upload_date`
  - export ZIP dari kedua basis dan cek nama output

### Risiko

- Perubahan naming bisa memengaruhi test yang memeriksa nama file export.
- Jangan mengubah basis filter utama kalau konteks yang dibutuhkan memang adalah tanggal upload.

## Partial 2 - Shortcut Report dari Dashboard Admin

### Masalah

Dashboard admin sudah operasional, tetapi shortcut cepat ke report/export belum tersedia secara langsung.

### Target

Admin bisa menuju report/export dari dashboard tanpa harus melewati beberapa halaman perantara.

### File Terdampak

- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/analytics/dashboard.blade.php`
- Opsional: `resources/views/layouts/app.blade.php`
- Opsional: test tambahan di `tests/Feature/AdminDashboardTest.php`

### Langkah Teknis

1. Tambahkan satu quick action baru di dashboard admin untuk laporan/export.
2. Tentukan target route yang paling masuk akal:
   - langsung ke analytics dashboard, atau
   - langsung ke salah satu report export yang sering dipakai.
3. Pastikan label tombol menjelaskan tujuan secara jelas, misalnya:
   - `Laporan`
   - `Export Report`
   - `Analytics & Report`
4. Jika perlu, ubah label existing button supaya tidak ambigu.
5. Tambahkan assertion test bahwa link baru memang muncul di dashboard admin.

### Verifikasi

- `php artisan test --filter=AdminDashboardTest`
- Manual:
  - login admin
  - buka dashboard
  - klik shortcut report dan pastikan route tujuan benar

### Risiko

- Jangan menambah terlalu banyak shortcut sehingga dashboard jadi berisik.
- Pastikan route yang dipilih memang relevan untuk admin.

## Partial 3 - Halaman Monitoring Admin Terpisah

### Masalah

Admin sudah bisa melihat monitoring real-time dari dashboard utama, tetapi belum ada halaman monitoring terpisah untuk drill-down.

### Target

Jika dibutuhkan, sediakan halaman monitoring admin khusus yang reusable dan lebih nyaman untuk filter/lookup.

### Status Keputusan

Item ini tidak wajib untuk operasional dasar. Jadi pengerjaan hanya dilakukan jika tim ingin memisahkan dashboard ringkas dengan halaman monitoring detail.

### File Potensial

- `routes/web.php`
- `app/Http/Controllers/Dashboard/AdminDashboardController.php`
- Baru: `app/Http/Controllers/Dashboard/AdminMonitoringController.php`
- Baru: `resources/views/admin/monitoring/index.blade.php`
- `resources/views/dashboard/partials/realtime-monitoring.blade.php`

### Langkah Teknis Jika Dikerjakan

1. Buat route khusus, misalnya `admin.monitoring.index`.
2. Pindahkan partial monitoring real-time ke halaman detail admin.
3. Tambahkan filter wilayah, status, atau sales bila diperlukan.
4. Pertahankan partial dashboard agar dashboard admin tetap ringkas.
5. Pastikan scope admin tetap lintas wilayah dan manager tetap dibatasi wilayah.

### Verifikasi

- `php artisan test --filter=LocationControllerTest`
- `php artisan test --filter=AdminDashboardTest`
- Manual:
  - login admin
  - cek dashboard ringkas
  - cek halaman monitoring detail jika dibuat

### Risiko

- Jangan duplikasi logika query lokasi di dua tempat.
- Jika dibuat, halaman ini harus memakai partial atau service yang sama agar mudah dirawat.

## Missing

### Catatan

Tidak ada pekerjaan wajib yang benar-benar masuk kategori `missing` untuk modul admin inti.

Artinya:

- dashboard admin sudah jalan,
- monitoring real-time sudah jalan,
- CRUD master data sudah jalan,
- PJP wajib klien sudah jalan,
- analytics/report sudah jalan,
- session timeout sudah jalan.

### Keputusan

Tidak ada task blocking untuk kategori `missing`.

Jika nanti audit lanjutan menemukan gap baru, tambahkan ke planning berikutnya. Untuk saat ini, fokus pengerjaan cukup pada item `partial`.

## Urutan Eksekusi yang Disarankan

1. Photo gallery export metadata
2. Shortcut report/export di dashboard admin
3. Evaluasi halaman monitoring admin terpisah

## Verifikasi Akhir

Setelah pengerjaan selesai:

- `php artisan test`
- Manual check admin dashboard
- Manual check photo gallery export
- Manual check route report/export dari dashboard admin

## Output yang Diharapkan

- Output photo gallery konsisten dengan basis tanggal yang dipilih
- Dashboard admin punya shortcut report/export yang lebih jelas
- Jika halaman monitoring admin terpisah dibuat, dashboard tetap ringkas dan halaman detail tetap reusable
