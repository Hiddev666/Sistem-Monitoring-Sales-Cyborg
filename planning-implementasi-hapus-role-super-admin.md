# Planning Implementasi Hapus Role Super Admin

Tanggal dibuat: 2026-06-11  
Area: autentikasi, role/permission, route middleware, user management admin, seeder, view, dan test.

## Tujuan

Menghapus role `super_admin` dari sistem, sehingga role yang tersisa hanya:

- `admin`
- `manager`
- `sales`

Selain itu, semua hal yang masih berkaitan dengan role admin harus disesuaikan agar sistem tetap konsisten setelah role `super_admin` dihapus.

## Ruang Lingkup Perubahan

1. Hapus definisi role `super_admin` dari seeder dan data test.
2. Ganti logika yang masih menganggap `super_admin` sebagai role khusus.
3. Ubah akses route dan controller yang masih mencantumkan `super_admin`.
4. Rapikan UI dan label yang menampilkan role `super_admin`.
5. Perbarui test agar tidak lagi bergantung pada role `super_admin`.
6. Pastikan admin tetap bisa menjalankan fungsi yang memang menjadi tanggung jawab admin.

## Aturan Baru Yang Disepakati

1. Sistem hanya mengenal tiga role utama:
   - `admin`
   - `manager`
   - `sales`
2. Tidak ada lagi hak akses berbasis role `super_admin`.
3. Semua kemampuan yang dulu hanya dimiliki `super_admin` harus dialihkan atau disederhanakan ke role yang tersisa.
4. Jika ada fitur yang sebelumnya hanya bisa diakses `super_admin`, perlu ditentukan apakah:
   - tetap menjadi akses `admin`, atau
   - dibatasi dengan permission tertentu.
5. Redirection login dan middleware harus tetap valid untuk tiga role baru tersebut.

## Titik Masuk Kode Yang Perlu Diubah

- [app/Models/User.php](/D:/cyborg/sistem-sales/app/Models/User.php)
- [app/Http/Controllers/Auth/LoginController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Auth/LoginController.php)
- [app/Http/Controllers/Admin/UserController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Admin/UserController.php)
- [app/Http/Controllers/Admin/ConfigurationController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/Admin/ConfigurationController.php)
- [routes/web.php](/D:/cyborg/sistem-sales/routes/web.php)
- [database/seeders/RoleSeeder.php](/D:/cyborg/sistem-sales/database/seeders/RoleSeeder.php)
- [database/seeders/CreateTestUsersSeeder.php](/D:/cyborg/sistem-sales/database/seeders/CreateTestUsersSeeder.php)
- [resources/views/admin/user/index.blade.php](/D:/cyborg/sistem-sales/resources/views/admin/user/index.blade.php)
- [resources/views/layouts/app.blade.php](/D:/cyborg/sistem-sales/resources/views/layouts/app.blade.php)
- [tests/Feature/SuperAdminAdminAccessTest.php](/D:/cyborg/sistem-sales/tests/Feature/SuperAdminAdminAccessTest.php)
- Potensi file test lain yang masih menyebut `super_admin`.

## Rencana Implementasi

### Fase 1 - Hapus Definisi Role Super Admin Dari Data Inti

Prioritas: Tinggi  
Tujuan: memastikan role `super_admin` tidak lagi dibuat saat seeding dan tidak ada data baru yang bergantung padanya.

#### Langkah

1. Ubah `RoleSeeder` agar hanya membuat `admin`, `manager`, dan `sales`.
2. Hapus assignment permission khusus untuk `super_admin`.
3. Audit `CreateTestUsersSeeder` agar tidak lagi membuat user `super_admin`.
4. Jika ada data test atau fixture lain yang menggunakan `super_admin`, ubah ke role yang relevan.

#### Catatan Implementasi

- Jika sistem membutuhkan akun dengan akses penuh untuk kebutuhan internal, perlu diputuskan apakah akun itu akan memakai role `admin` dengan permission lengkap.
- Jangan menyisakan `super_admin` sebagai role tersembunyi di data seed, karena itu akan membuat perilaku aplikasi ambigu.

#### Acceptance Criteria

- Seeder tidak lagi membuat role `super_admin`.
- Tidak ada user seed baru yang memakai role `super_admin`.
- Database hasil seed hanya berisi tiga role utama.

### Fase 2 - Bersihkan Logika Role Di Model Dan Login

Prioritas: Tinggi  
Tujuan: helper role dan redirect login hanya mengenal role yang tersisa.

#### Langkah

1. Ubah helper di `User`:
   - `isAdmin()` jangan lagi menganggap `super_admin` sebagai admin tambahan.
   - hapus atau nonaktifkan `isSuperAdmin()` jika sudah tidak dipakai.
2. Ubah `getRoleLabel()` agar hanya memetakan `admin`, `manager`, dan `sales`.
3. Audit `LoginController` supaya redirect hanya mempertimbangkan tiga role tersebut.
4. Pastikan fallback redirect tetap aman bila user tidak punya role valid.

#### Catatan Implementasi

- Kalau ada kode lama yang memanggil `isSuperAdmin()`, harus diberi pengganti sebelum method dihapus.
- Jika ada kondisi `if ($user->isSuperAdmin())`, ubah menjadi logika berbasis `admin` atau permission.

#### Acceptance Criteria

- Tidak ada lagi helper yang menganggap `super_admin` setara dengan `admin`.
- Redirect login tetap bekerja untuk admin, manager, dan sales.

### Fase 3 - Ubah Route Middleware Dan Akses Admin

Prioritas: Tinggi  
Tujuan: route yang sebelumnya menyertakan `super_admin` harus disederhanakan ke role yang ada.

#### Langkah

1. Audit seluruh middleware `role:` yang masih mencantumkan `super_admin`.
2. Ubah route admin/manager/API agar hanya memakai `admin`, `manager`, atau `sales` sesuai kebutuhan.
3. Tentukan ulang akses halaman konfigurasi dan halaman admin lain yang sebelumnya hanya bisa diakses `super_admin`.
4. Pastikan route yang bersifat monitoring, laporan, dan konfigurasi tetap aman setelah perubahan.

#### Catatan Implementasi

- Area paling sensitif adalah:
  - `admin` route group
  - `manager` route group yang masih mencantumkan `super_admin`
  - endpoint API yang memakai middleware `role:manager,admin,super_admin`
- Untuk halaman konfigurasi sistem, kemungkinan besar akses perlu dipindahkan ke `admin` dengan permission `manage_config`, atau tetap berbasis permission saja.

#### Acceptance Criteria

- Tidak ada route yang masih memakai middleware `super_admin`.
- Role `admin`, `manager`, dan `sales` tetap bisa mengakses halaman yang sesuai.
- Halaman yang sensitif tetap dibatasi dengan permission bila perlu.

### Fase 4 - Rapikan User Management Dan Label UI

Prioritas: Medium  
Tujuan: tampilan dan pilihan role di UI tidak lagi menampilkan `super_admin`.

#### Langkah

1. Ubah opsi role pada form user admin agar hanya menampilkan role yang valid.
2. Perbarui label, badge, dan teks penjelasan di:
   - daftar user
   - form user
   - layout yang menampilkan badge role
3. Hapus teks yang masih menjelaskan mode `Super Admin`.
4. Pastikan alur tambah/edit user tetap konsisten untuk admin, manager, dan sales.

#### Catatan Implementasi

- Jangan hanya menyembunyikan `super_admin` di UI; data dan backend juga harus dibersihkan.
- Jika ada user lama dengan role `super_admin`, perlu strategi migrasi ke `admin` atau role lain sebelum UI disederhanakan.

#### Acceptance Criteria

- UI tidak lagi menampilkan role `super_admin`.
- Badge role hanya menunjukkan tiga role yang tersisa.
- Form user tidak menawarkan role yang sudah dihapus.

### Fase 5 - Migrasi Data Lama Yang Masih Memakai Super Admin

Prioritas: Tinggi  
Tujuan: memastikan data existing tidak membuat sistem gagal setelah role dihapus.

#### Langkah

1. Identifikasi user yang masih memiliki role `super_admin`.
2. Tentukan pemetaan migrasi, umumnya:
   - `super_admin` -> `admin`
3. Update data user lama sebelum atau saat deployment perubahan.
4. Bersihkan role lama dari tabel roles dan permission cache setelah migrasi selesai.

#### Catatan Implementasi

- Ini penting karena menghapus role di kode tanpa migrasi data bisa menyebabkan user lama kehilangan akses atau error saat login.
- Jika ada akun lama yang memang harus tetap punya akses penuh, permission `admin` harus ditinjau agar mencukupi.

#### Acceptance Criteria

- Tidak ada user aktif yang masih terikat ke role `super_admin`.
- Sistem tetap bisa login dan berjalan setelah migrasi.

### Fase 6 - Perbarui Test Dan Skenario Akses

Prioritas: Tinggi  
Tujuan: memastikan penghapusan role tidak merusak perilaku yang diharapkan.

#### Langkah

1. Ubah test `SuperAdminAdminAccessTest` menjadi test yang relevan dengan role baru.
2. Hapus atau ganti assertion yang mengharapkan akses `super_admin`.
3. Tambahkan test untuk memastikan:
   - login admin tetap benar
   - configuration page hanya bisa diakses role yang diizinkan
   - user management hanya menampilkan role valid
4. Audit feature test lain yang masih menyebut `super_admin`.

#### Catatan Implementasi

- Nama test file boleh diganti agar tidak lagi menyesatkan.
- Fokus test bukan pada nama role lama, tetapi pada perilaku akses yang benar.

#### Acceptance Criteria

- Tidak ada test yang masih bergantung pada role `super_admin`.
- Test akses admin, manager, dan sales tetap hijau.
- Perubahan role tidak memutus fitur utama.

## Risiko Yang Perlu Diwaspadai

1. Ada banyak referensi tersembunyi ke `super_admin` di view dan test, bukan hanya di controller.
2. User lama bisa kehilangan akses jika data role tidak dimigrasikan.
3. Beberapa route sensitif sebelumnya bergantung pada `super_admin`, sehingga perlu keputusan akses pengganti yang jelas.
4. Permission `manage_config` dan `manage_roles` mungkin perlu ditinjau ulang agar admin tetap bisa menjalankan tugasnya.

## Urutan Pengerjaan Yang Disarankan

1. Migrasi data role lama terlebih dahulu.
2. Hapus definisi role `super_admin` dari seeder dan helper model.
3. Ubah middleware route dan controller.
4. Rapikan UI dan label role.
5. Perbarui test dan jalankan seluruh suite.

## Definisi Selesai

Implementasi dianggap selesai jika:

- Role `super_admin` sudah tidak ada di kode, seeder, UI, dan test.
- Sistem hanya memakai `admin`, `manager`, dan `sales`.
- Akses admin yang sebelumnya bergantung pada `super_admin` sudah dialihkan dengan jelas.
- User lama tetap bisa dipakai setelah migrasi.
- Test penting tetap hijau.

