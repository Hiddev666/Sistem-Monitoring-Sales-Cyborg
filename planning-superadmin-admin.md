# Planning Pengerjaan Akses `super_admin` vs `admin`

Tanggal planning: 2026-05-29

Sumber: `analisis-superadmin-admin.md`

## Tujuan

Menyesuaikan implementasi codebase dengan rancangan peran pada PDF dan hasil analisis terbaru, khususnya agar:

- `super_admin` benar-benar menjadi role tertinggi dengan akses penuh.
- `admin` tetap fokus pada operasional data dan monitoring.
- Hak akses, menu, dan route tidak lagi menyamakan `super_admin` dengan `admin` untuk area yang seharusnya eksklusif.

## Status Umum

Hasil analisis menunjukkan:

- Permission seeder sudah membedakan `super_admin` dan `admin` di level data.
- Namun, route, helper model, redirect login, dan sidebar masih memperlakukan keduanya hampir sama.
- `admin` masih punya akses ke area yang secara rancangan lebih cocok untuk `super_admin`, terutama konfigurasi sistem.
- Belum ada pemisahan UI dan guard yang tegas untuk fitur `super_admin` saja.

## Prioritas Pengerjaan

1. Pisahkan area eksklusif `super_admin` dari `admin`.
2. Kunci akses konfigurasi dan pengelolaan role agar tidak terbuka ke `admin`.
3. Rapikan route, middleware, dan menu agar selaras dengan matriks akses baru.
4. Tambahkan test regresi untuk memastikan batas akses tidak bocor lagi.

## Gap 1 - `super_admin` Belum Punya Area Eksklusif yang Jelas

### Masalah

Saat ini `super_admin` dan `admin` sama-sama diarahkan ke dashboard admin yang sama, dan tidak ada route atau menu khusus yang hanya muncul untuk `super_admin`.

### Target

`super_admin` punya akses tambahan yang jelas, minimal pada:

- pengelolaan role,
- pengaturan konfigurasi sistem,
- dan akses penuh ke area admin operasional tanpa dibatasi rule tambahan.

### File Terdampak

- `app/Models/User.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/admin/user/form.blade.php`
- `resources/views/admin/user/index.blade.php`
- `resources/views/admin/configuration/index.blade.php`
- `app/Http/Controllers/Admin/UserController.php`
- `app/Http/Controllers/Admin/ConfigurationController.php`

### Langkah Teknis

1. Tambahkan pemisahan perilaku berbasis `isSuperAdmin()` untuk fitur yang memang eksklusif.
2. Pertahankan `isAdmin()` hanya untuk akses bersama area operasional.
3. Tambahkan penanda UI yang hanya tampil untuk `super_admin`, misalnya section menu "Kontrol Super Admin".
4. Jika perlu, sediakan halaman atau blok khusus untuk pengelolaan role dan pengaturan sistem.
5. Pastikan `super_admin` tetap bisa membuka seluruh area admin, tetapi `admin` tidak mendapat akses ke fitur eksklusif tersebut.

### Verifikasi

- Login sebagai `super_admin` dan pastikan menu eksklusif muncul.
- Login sebagai `admin` dan pastikan menu eksklusif tidak muncul.
- Pastikan route eksklusif menolak `admin` dengan status yang benar.

### Risiko

- Jangan memecah dashboard utama secara berlebihan kalau tidak ada kebutuhan UX yang kuat.
- Perubahan UI harus tetap konsisten dengan layout yang sudah ada.

## Gap 2 - `admin` Masih Punya Akses Konfigurasi yang Terlalu Luas

### Masalah

Di seeder, `admin` masih mendapat `manage_config`, padahal rancangan menunjukkan konfigurasi sistem lebih dekat ke tanggung jawab `super_admin`.

### Target

Konfigurasi sistem hanya dikelola oleh `super_admin`.

### File Terdampak

- `database/seeders/RoleSeeder.php`
- `app/Http/Controllers/Admin/ConfigurationController.php`
- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/admin/configuration/index.blade.php`
- `tests/Feature/AdminDashboardTest.php`
- `tests/Feature/Phase10OperationalSmokeTest.php`

### Langkah Teknis

1. Hapus `manage_config` dari permission `admin` pada seeder.
2. Pastikan `manage_config` tetap dimiliki `super_admin`.
3. Tambahkan guard di route atau controller agar akses konfigurasi menolak `admin`.
4. Sembunyikan menu konfigurasi dari `admin` di sidebar.
5. Pastikan form update/reset konfigurasi hanya bisa diakses oleh `super_admin`.

### Verifikasi

- `super_admin` bisa membuka dan menyimpan konfigurasi.
- `admin` mendapat forbidden atau redirect saat membuka halaman konfigurasi.
- Menu konfigurasi tidak tampil untuk `admin`.

### Risiko

- Ada kemungkinan test atau flow lama masih mengasumsikan `admin` bisa membuka konfigurasi.
- Jika ada job atau fitur lain yang membaca nilai konfigurasi, jangan ubah mekanisme baca nilainya, hanya akses pengelolaannya.

## Gap 3 - Pengelolaan User Belum Memisahkan CRUD User dan Assign Role

### Masalah

Rancangan menyebut `super_admin` mengelola user dan role, sedangkan `admin` hanya fokus pada data operasional. Saat ini pengelolaan user masih satu paket, sehingga `admin` berpotensi ikut mengatur aspek yang seharusnya hanya boleh dilakukan `super_admin`.

### Target

- `admin` bisa mengelola user operasional sesuai kebutuhan, misalnya data sales.
- `super_admin` menjadi satu-satunya role yang bisa mengubah role pengguna.

### File Terdampak

- `app/Http/Controllers/Admin/UserController.php`
- `resources/views/admin/user/form.blade.php`
- `resources/views/admin/user/index.blade.php`
- `resources/views/admin/user/actions.blade.php`
- `routes/web.php`
- `database/seeders/RoleSeeder.php`
- `tests/Feature/AdminDashboardTest.php`

### Langkah Teknis

1. Pisahkan field dan validasi form user untuk data umum dan field role.
2. Tampilkan field pilihan role hanya untuk `super_admin`.
3. Batasi aksi assign/ubah role pada controller dengan check `isSuperAdmin()` atau permission `manage_roles`.
4. Jika `admin` tetap boleh membuat user, pastikan role default yang bisa dipilih dibatasi.
5. Tambahkan guard untuk mencegah `admin` mengubah role ke level yang lebih tinggi.

### Verifikasi

- `super_admin` bisa membuat user dan menetapkan role.
- `admin` bisa mengelola user operasional sesuai kebijakan, tetapi tidak bisa mengubah role sembarang user.
- Jika `admin` mencoba mengubah role, sistem menolak.

### Risiko

- Jika implementasi saat ini menggabungkan CRUD user dan assign role di satu form, perubahan ini perlu dilakukan hati-hati agar tidak merusak alur input yang sudah ada.

## Gap 4 - Route dan Middleware Masih Terlalu Bergantung Pada Group Role Gabungan

### Masalah

Route admin, analytics, dan API monitoring masih banyak memakai group gabungan seperti `role:admin,super_admin` atau `role:manager,admin,super_admin`. Pola ini aman secara akses dasar, tetapi tidak cukup presisi untuk membedakan fitur eksklusif `super_admin`.

### Target

Route dibuat lebih spesifik berdasarkan kebutuhan:

- route operasional bersama boleh tetap memakai group gabungan,
- route eksklusif memakai `role:super_admin` atau permission khusus.

### File Terdampak

- `routes/web.php`
- `app/Http/Middleware/RoleMiddleware.php`
- `app/Http/Controllers/Admin/ConfigurationController.php`
- `app/Http/Controllers/Admin/UserController.php`

### Langkah Teknis

1. Audit route yang memang shared dan route yang harus eksklusif.
2. Pertahankan group gabungan hanya untuk fitur yang benar-benar boleh diakses bersama.
3. Tambahkan route guard khusus untuk area role/config.
4. Bila perlu, gunakan permission middleware untuk memisahkan akses berdasarkan capability, bukan hanya role.
5. Pastikan route analytics dan monitoring yang memang dibutuhkan `admin` tetap tersedia.

### Verifikasi

- Route operasional tetap bisa diakses `admin` dan `super_admin`.
- Route eksklusif hanya bisa diakses `super_admin`.
- Tidak ada route publik baru yang tidak sengaja terbuka.

### Risiko

- Mengganti middleware terlalu agresif bisa memutus akses laporan atau monitoring yang masih dibutuhkan `admin`.
- Perubahan harus dilakukan bertahap, bukan sekaligus semua route.

## Gap 5 - Sidebar dan Teks Navigasi Belum Mencerminkan Hierarki Role

### Masalah

Sidebar saat ini memakai `isAdmin()` untuk sebagian besar menu, sehingga `super_admin` dan `admin` terlihat sama dari sisi navigasi.

### Target

Navigasi mencerminkan hierarki role:

- menu operasional tampil untuk `admin` dan `super_admin`,
- menu kontrol sistem tampil hanya untuk `super_admin`.

### File Terdampak

- `resources/views/layouts/app.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/user/index.blade.php`
- `resources/views/admin/configuration/index.blade.php`

### Langkah Teknis

1. Tambahkan kondisi `isSuperAdmin()` untuk item menu eksklusif.
2. Kelompokkan menu operasional dan menu kontrol sistem ke section terpisah.
3. Perjelas label agar user memahami bahwa sebagian menu hanya milik `super_admin`.
4. Pastikan active state route tetap bekerja setelah pemisahan.

### Verifikasi

- Login `admin`: menu operasional tampil, menu kontrol sistem tidak tampil.
- Login `super_admin`: semua menu relevan tampil.

### Risiko

- Jika menu dipisah terlalu banyak, sidebar bisa jadi lebih panjang dan kurang rapi.
- Jaga hierarki visual agar tetap sederhana.

## Gap 6 - Test Coverage Belum Mengunci Batas Akses Baru

### Masalah

Saat akses diperketat, perlu test regresi yang memastikan `admin` tidak bisa masuk ke area `super_admin` dan sebaliknya semua akses operasional tetap jalan.

### Target

Ada test yang eksplisit untuk:

- akses `super_admin`,
- akses `admin`,
- penolakan akses untuk area eksklusif,
- dan konsistensi tampilan menu.

### File Terdampak

- `tests/Feature/AdminDashboardTest.php`
- `tests/Feature/AdminMonitoringTest.php`
- `tests/Feature/AdminReportExportTest.php`
- `tests/Feature/AdminPJPTest.php`
- `tests/Feature/Auth/AuthenticationTest.php`
- Baru: test khusus untuk konfigurasi dan role management

### Langkah Teknis

1. Tambahkan test bahwa `super_admin` bisa membuka area eksklusif.
2. Tambahkan test bahwa `admin` ditolak pada area eksklusif.
3. Tambahkan test menu atau response view untuk memastikan item tertentu tidak tampil.
4. Tambahkan test login redirect bila ada perubahan route tujuan.
5. Jalankan seluruh suite test setelah perubahan inti.

### Verifikasi

- `php artisan test`
- Fokus tambahan:
  - test user management
  - test configuration access
  - test menu visibility

### Risiko

- Test view bisa rapuh kalau selector atau label UI berubah.
- Lebih baik menguji perilaku akses daripada detail markup yang terlalu spesifik.

## Urutan Eksekusi yang Disarankan

1. Kunci konfigurasi sistem agar hanya `super_admin` yang bisa mengelola.
2. Pisahkan pengelolaan role dari CRUD user.
3. Rapikan menu dan route untuk menampilkan hierarki role yang benar.
4. Audit ulang permission seeder dan controller terkait.
5. Tambahkan test regresi untuk seluruh batas akses baru.

## Verifikasi Akhir

Setelah pengerjaan selesai:

- `php artisan test`
- Manual check login `admin`
- Manual check login `super_admin`
- Manual check menu sidebar
- Manual check halaman user management
- Manual check halaman configuration

## Output yang Diharapkan

- `super_admin` punya akses eksklusif yang benar-benar terlihat dan terlindungi.
- `admin` hanya mendapat akses operasional sesuai rancangan.
- Konfigurasi sistem tidak lagi terbuka ke `admin`.
- Pengelolaan role tidak bisa dilakukan oleh role yang tidak berhak.
- Route, menu, dan test sudah selaras dengan rancangan dan hasil analisis.
