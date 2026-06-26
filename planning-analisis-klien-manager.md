# Planning Analisis Klien Manager

Tanggal planning: 2026-06-26

## Tujuan

Membuat fitur analisis klien di akun manager berfungsi stabil dan konsisten seperti di akun admin, tanpa mengubah perilaku admin yang sudah berjalan.

Target utama:

1. Halaman analisis klien bisa dibuka dari akun manager.
2. Data yang tampil di akun manager konsisten dengan scope manager.
3. Export Excel dan PDF untuk manager memakai data yang sama dengan halaman.
4. Tidak ada regresi untuk akun admin.

## Hasil Analisis Awal

Berdasarkan codebase, fitur analisis klien sebenarnya sudah punya jalur manager:

- Route manager tersedia di `routes/web.php`.
- View analytics sudah memilih route manager/admin secara dinamis.
- `AnalyticsController::klienAnalysis()` sudah mencoba menerapkan scope manager lewat `managerWilayahScope()`.
- `ReportExportController::klienAnalysis()` juga sudah mencoba membatasi export manager lewat `effectiveWilayahId()`.

Namun ada beberapa titik rawan yang perlu dibereskan:

- Scope manager sangat bergantung pada `wilayah_id` yang melekat ke akun manager.
- Jika `wilayah_id` kosong atau tidak sesuai, manager bisa melihat data kosong atau perilaku yang tidak seragam.
- Belum ada test yang benar-benar mengunci perilaku manager khusus untuk analisis klien end-to-end.
- View analisis klien masih memakai pola route dinamis yang perlu dipastikan benar untuk export dan navigasi manager.

## Prinsip Implementasi

- Jangan ubah perilaku admin yang sudah valid.
- Perbaikan manager harus dilakukan di backend, bukan hanya di view.
- Scope data harus eksplisit dan aman.
- Jika manager tanpa `wilayah_id` dianggap data invalid, respons harus jelas dan tidak membuka data lintas wilayah.
- Jika manager memang boleh akses lintas wilayah, keputusan itu harus konsisten di controller, export, dan test.

## Partial 1 - Audit dan Reproduksi Masalah

### Fokus

Pastikan masalah manager terjadi di mana:

- akses halaman,
- isi data,
- export Excel,
- export PDF,
- atau data scope yang kosong.

### File yang Dicek

- `app/Http/Controllers/Dashboard/AnalyticsController.php`
- `app/Http/Controllers/Admin/ReportExportController.php`
- `resources/views/admin/analytics/klien-analysis.blade.php`
- `resources/views/layouts/app.blade.php`
- `app/Models/User.php`

### Langkah

1. Login sebagai manager dan buka halaman analisis klien.
2. Bandingkan data yang tampil dengan akun admin pada periode yang sama.
3. Cek apakah manager punya `wilayah_id` di database.
4. Cek apakah tombol export manager mengarah ke route yang benar.
5. Cek apakah hasil export mengikuti scope data yang sama dengan halaman.

### Output yang Diharapkan

- Daftar titik gagal yang spesifik.
- Keputusan apakah problem utamanya ada di scope data, route, atau export.

## Partial 2 - Standarisasi Scope Manager

### Fokus

Samakan aturan scope manager untuk analisis klien di controller dan export.

### Target

- Manager hanya melihat data wilayahnya sendiri.
- Manager tanpa `wilayah_id` tidak mendapat data yang ambigu.
- Admin tetap bisa melihat cakupan penuh.

### File Terdampak

- `app/Http/Controllers/Dashboard/AnalyticsController.php`
- `app/Http/Controllers/Admin/ReportExportController.php`
- `app/Http/Controllers/Dashboard/ManagerDashboardController.php` jika perlu diselaraskan
- `app/Models/User.php` jika diperlukan helper scope tambahan

### Langkah

1. Tentukan satu aturan baku untuk manager dengan `wilayah_id`.
2. Terapkan aturan yang sama di `klienAnalysis()` dan export `klienAnalysis()`.
3. Jika manager tanpa `wilayah_id` tidak valid, tampilkan respons yang aman dan jelas.
4. Jika manager tanpa `wilayah_id` harus tetap bisa akses, pastikan fallback-nya tidak membuka data lintas wilayah.
5. Pastikan query admin tidak ikut berubah.

### Catatan Teknis

Saat ini controller analytics dan export sudah punya mekanisme scope sendiri, tetapi implementasinya harus dicek ulang agar tidak ada perbedaan perilaku antara halaman dan file export.

## Partial 3 - Konsistensi Route dan View

### Fokus

Pastikan semua tombol dan link di halaman analisis klien manager mengarah ke jalur manager yang benar.

### File Terdampak

- `resources/views/admin/analytics/klien-analysis.blade.php`
- `resources/views/admin/analytics/dashboard.blade.php`
- `resources/views/layouts/app.blade.php`
- `routes/web.php`

### Langkah

1. Pastikan link dari sidebar dan dashboard menuju `manager.analytics.klien-analysis`.
2. Pastikan tombol export di halaman manager memakai `manager.reports.export-klien-analysis`.
3. Pastikan mode admin tetap memakai route admin.
4. Hindari hardcode yang membuat manager bergantung ke nama route admin.

### Output yang Diharapkan

- Navigasi manager terasa konsisten.
- Tidak ada route mismatch saat manager menekan tombol export atau buka detail analytics.

## Partial 4 - Test Coverage

### Fokus

Tambahkan test yang mengunci perilaku manager untuk analisis klien.

### File Terdampak

- `tests/Feature/ManagerReportAccessTest.php`
- `tests/Feature/ManagerReportScopeTest.php`
- kemungkinan test baru khusus klien analysis jika perlu

### Skenario Test

1. Manager bisa membuka halaman analisis klien.
2. Manager hanya melihat data wilayahnya sendiri.
3. Manager tidak bisa mengakses wilayah lain lewat parameter export.
4. Export manager menghasilkan file dengan scope yang sama seperti halaman.
5. Admin tetap bisa melihat data penuh dan export normal.

### Kriteria Lulus

- Semua test manager analisis klien lolos.
- Tidak ada regresi pada test admin.

## Urutan Eksekusi yang Disarankan

1. Audit dan reproduksi masalah.
2. Standarisasi scope manager.
3. Rapikan route dan view.
4. Tambahkan test coverage.

## Definisi Selesai

Fitur dianggap selesai jika:

- manager bisa membuka analisis klien tanpa error,
- data manager sesuai scope wilayah,
- export manager sama dengan data halaman,
- admin tetap berfungsi seperti sebelumnya,
- dan ada test yang menjaga perilaku ini ke depan.
