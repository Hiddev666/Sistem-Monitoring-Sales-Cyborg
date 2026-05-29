# Planning Modul Sales

Sumber:
- [analisis-sales.md](/D:/cyborg/sistem-sales/analisis-sales.md)
- [sales-bug-planning.md](/D:/cyborg/sistem-sales/sales-bug-planning.md)

Fokus planning ini hanya pada bagian `partial` dan `missing`.

## Partial 1 - Konsolidasi Status Sales

Prioritas: Medium  
Tujuan: menyatukan status yang masih campur antara nilai baru dan legacy agar logika, view, dan test memakai representasi yang lebih seragam.

### Masalah

- `JadwalKunjungan` memakai status `pending`, `aktif`, `selesai`.
- `JadwalKlien` masih menerima campuran `active`, `aktif`, `completed`, `skipped`, dan legacy state lain.
- View masih memeriksa string status secara langsung di beberapa tempat.

### Target Perbaikan

1. Tetapkan satu skema status yang disepakati untuk `JadwalKlien` dan `JadwalKunjungan`.
2. Tambahkan helper atau accessor kalau perlu agar view tidak lagi bergantung pada string literal yang tersebar.
3. Pastikan semua kontroler, model, dan view memakai nilai status yang sama.
4. Audit test yang masih mengandalkan string status lama dan sesuaikan jika perlu.

### File yang Kemungkinan Diubah

- [app/Models/JadwalKlien.php](/D:/cyborg/sistem-sales/app/Models/JadwalKlien.php)
- [app/Models/JadwalKunjungan.php](/D:/cyborg/sistem-sales/app/Models/JadwalKunjungan.php)
- [app/Http/Controllers/SalesPJPController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/SalesPJPController.php)
- [app/Http/Controllers/VisitFormController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/VisitFormController.php)
- [resources/views/sales/pjp/today.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/today.blade.php)
- [resources/views/sales/pjp/show.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/show.blade.php)
- [resources/views/sales/attendance/index.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/attendance/index.blade.php)
- [tests/Feature/VisitFormSubmitTest.php](/D:/cyborg/sistem-sales/tests/Feature/VisitFormSubmitTest.php)

### Acceptance Criteria

- Tidak ada lagi penggunaan string status legacy yang tidak diperlukan di view utama sales.
- Logika check-in, checkout, dan form completion tetap lulus tanpa bergantung pada campuran status.
- Test sales tetap hijau setelah normalisasi status.

## Partial 2 - Guard Checkout Klien

Prioritas: High  
Tujuan: menutup celah direct access pada endpoint checkout klien supaya hanya kunjungan aktif saat ini yang bisa diproses.

### Masalah

- Endpoint checkout klien saat ini hanya memeriksa status klien, tetapi belum memastikan bahwa record itu adalah `current visit`.
- UI sudah membatasi tombol secara visual, jadi celah ini lebih ke keamanan dan integritas flow.

### Target Perbaikan

1. Tambahkan guard di controller untuk memastikan `jadwalKlien` yang di-checkout adalah visit aktif yang sedang berjalan.
2. Tolak request jika klien bukan urutan saat ini atau jika jadwal belum aktif.
3. Tambahkan test negatif direct access untuk memastikan guard benar-benar bekerja.
4. Pastikan redirect ke form hanya terjadi untuk data yang valid.

### File yang Kemungkinan Diubah

- [app/Http/Controllers/SalesPJPController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/SalesPJPController.php)
- [tests/Feature/SalesPJPVisitOrderTest.php](/D:/cyborg/sistem-sales/tests/Feature/SalesPJPVisitOrderTest.php)
- [tests/Feature/Phase10OperationalSmokeTest.php](/D:/cyborg/sistem-sales/tests/Feature/Phase10OperationalSmokeTest.php)

### Acceptance Criteria

- Endpoint checkout klien menolak request yang bukan urutan aktif saat ini.
- UI lama tetap berfungsi tanpa perubahan perilaku yang terlihat untuk alur normal.
- Ada test yang membuktikan direct access sudah ditutup.

## Partial 3 - Route Helper Untuk JavaScript View

Prioritas: Low  
Tujuan: mengurangi hardcoded URL string di JS agar maintenance lebih aman jika prefix atau nama route berubah.

### Masalah

- Beberapa `fetch()` masih memakai string URL langsung.
- Ini tidak merusak flow sekarang, tetapi rapuh untuk perubahan route di masa depan.

### Target Perbaikan

1. Ganti URL string langsung dengan `route()` atau helper Laravel lain yang setara.
2. Audit semua aksi JS di view sales agar konsisten.
3. Pastikan tidak ada regresi pada upload foto, signature, submit form, dan check-in PJP.

### File yang Kemungkinan Diubah

- [resources/views/sales/pjp/today.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/today.blade.php)
- [resources/views/sales/pjp/show.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/show.blade.php)
- [resources/views/sales/pjp/visit-form.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/visit-form.blade.php)

### Acceptance Criteria

- Tidak ada lagi URL internal sales yang hardcoded jika route helper bisa dipakai.
- Perilaku JS tetap sama.
- Test operasional sales tetap hijau.

## Missing

- Tidak ada gap fungsional besar yang benar-benar hilang dari modul sales inti.

### Catatan

- Fitur inti yang dibutuhkan sudah tersedia dan teruji: dashboard, absensi, PJP, GPS check-in, visit form, dan tracking lokasi.
- Jika ada pengerjaan lanjutan, itu sifatnya hardening, bukan implementasi fitur yang belum ada.

## Urutan Pengerjaan yang Disarankan

1. Kerjakan `Partial 2` dulu karena ini menyangkut integritas flow.
2. Kerjakan `Partial 1` setelah itu agar model, view, dan test lebih seragam.
3. Kerjakan `Partial 3` terakhir karena sifatnya perapian dan menurunkan risiko maintenance.
