# Analisis Perbandingan Hak Akses `super_admin` vs `admin`

Tanggal analisis: 2026-05-29

## Ringkasan

Berdasarkan PDF [Analisis_Alur_Aplikasi_Monitoring_Sales_Force (1).pdf](D:/cyborg/sistem-sales/Analisis_Alur_Aplikasi_Monitoring_Sales_Force (1).pdf), `super_admin` digambarkan sebagai role dengan akses penuh, sedangkan `admin` fokus pada operasional data dan pengelolaan jadwal kunjungan.

Setelah dicek ke codebase, hasilnya adalah:

- `super_admin` memang diberi seluruh permission di seeder.
- Tetapi di implementasi route, menu, dan redirect login, `super_admin` dan `admin` masih diperlakukan hampir sama.
- Tidak ada pemisahan fitur yang benar-benar eksklusif untuk `super_admin`.
- Jadi, secara konsep PDF masih cocok, tetapi secara teknis codebase belum membedakan `super_admin` sebagai role yang punya kemampuan tambahan yang jelas di atas `admin`.

## Perbandingan Hak Akses Dari PDF

| Area | `super_admin` menurut PDF | `admin` menurut PDF |
|---|---|---|
| Akses umum | Akses penuh ke seluruh sistem | Fokus ke operasional data dan jadwal |
| User management | Ya, bisa mengelola data user dan role | Ya, mengelola sales dan user |
| Wilayah | Ya | Ya |
| Klien | Ya | Ya |
| Konfigurasi sistem | Ya | Tidak disebut sebagai fokus utama, tetapi masih masuk area operasional |
| PJP / jadwal kunjungan | Ya | Ya, termasuk membuat jadwal harian dan menentukan urutan kunjungan |
| Monitoring dan evaluasi | Ya, mengakses seluruh dashboard dan laporan | Ya, memantau rekap absensi, dokumentasi kunjungan, dan evaluasi aktivitas |
| Export laporan | Ya | Ya, karena masih bagian dari pengawasan operasional |

## Hasil Analisis Codebase

### 1. Definisi role dan permission sudah membedakan secara konsep

Di [database/seeders/RoleSeeder.php](D:/cyborg/sistem-sales/database/seeders/RoleSeeder.php), `super_admin` dibuat dengan semua permission:

- `manage_users`
- `manage_roles`
- `manage_klien`
- `manage_wilayah`
- `create_pjp`
- `edit_pjp`
- `delete_pjp`
- `view_attendance`
- `view_kunjungan`
- `view_dashboard`
- `view_reports`
- `export_reports`
- `manage_config`

Sedangkan `admin` hanya mendapat subset operasional:

- `manage_klien`
- `manage_wilayah`
- `create_pjp`
- `edit_pjp`
- `delete_pjp`
- `view_attendance`
- `view_kunjungan`
- `manage_config`

Kesimpulan bagian ini:

- Secara data permission, `super_admin` memang lebih tinggi dari `admin`.
- Ini sesuai dengan narasi PDF bahwa `super_admin` adalah role penuh.

### 2. Di route, `super_admin` dan `admin` hampir selalu digabung

Di [routes/web.php](D:/cyborg/sistem-sales/routes/web.php), group admin memakai middleware:

- `role:admin,super_admin`

Artinya:

- Semua route `/admin/*` dibuka untuk kedua role.
- Route analytics dan report juga dibuka untuk `admin`, `super_admin`, dan `manager`.
- API monitoring lokasi juga dibuka untuk `manager,admin,super_admin`.

Implikasinya:

- Tidak ada route khusus yang hanya bisa diakses `super_admin`.
- Tidak ada pemisahan akses tambahan yang menegaskan "super admin" sebagai level di atas admin dalam bentuk route tersendiri.

### 3. Helper model juga menyamakan `super_admin` dengan `admin`

Di [app/Models/User.php](D:/cyborg/sistem-sales/app/Models/User.php):

- `isAdmin()` mengembalikan `true` untuk `admin` dan `super_admin`.
- `isSuperAdmin()` memang ada, tetapi hampir tidak dipakai untuk pemisahan perilaku yang nyata.

Artinya:

- Banyak logika aplikasi yang menganggap `super_admin` sebagai bagian dari kategori admin biasa.
- Secara praktis, `super_admin` tidak mendapat jalur khusus yang terpisah dari admin.

### 4. Redirect login juga tidak membedakan dashboard khusus super admin

Di [app/Http/Controllers/Auth/LoginController.php](D:/cyborg/sistem-sales/app/Http/Controllers/Auth/LoginController.php):

- `sales` diarahkan ke dashboard sales.
- `manager` diarahkan ke dashboard manager.
- `admin` dan `super_admin` sama-sama diarahkan ke `admin.dashboard`.

Di [routes/web.php](D:/cyborg/sistem-sales/routes/web.php), redirect root juga menggunakan `isAdmin()`, sehingga `super_admin` tetap jatuh ke dashboard admin yang sama.

Kesimpulan:

- Tidak ada dashboard khusus `super_admin`.
- Secara UX dan routing, `super_admin` dan `admin` diperlakukan sama.

### 5. Menu sidebar juga tidak membedakan admin vs super admin

Di [resources/views/layouts/app.blade.php](D:/cyborg/sistem-sales/resources/views/layouts/app.blade.php):

- Menu master data ditampilkan jika user lolos `isAdmin()`.
- Karena `isAdmin()` mencakup `super_admin`, maka menu yang muncul sama.
- Tidak ada item menu yang hanya muncul khusus untuk `super_admin`.

## Penilaian Kesesuaian

### Yang sudah sesuai

- `super_admin` memang diberi permission paling lengkap di seeder.
- Secara konsep, ini cocok dengan deskripsi PDF bahwa `super_admin` adalah role penuh.
- `admin` memang hanya diberi permission operasional.

### Yang belum sepenuhnya sesuai

- Codebase belum membedakan `super_admin` sebagai role yang benar-benar punya akses tambahan yang jelas dibanding `admin`.
- Di route, redirect, dan menu, `super_admin` pada praktiknya diperlakukan sama dengan `admin`.
- Tidak ada pembatasan route khusus yang menunjukkan pemisahan level akses `super_admin`.

## Kesimpulan Akhir

Jika pertanyaannya adalah: "Apakah `super_admin` punya akses lebih luas daripada `admin`?"

- Jawaban: ya, di level permission seeder.

Jika pertanyaannya adalah: "Apakah codebase saat ini sudah mengekspresikan perbedaan itu secara nyata di route, menu, dan alur aplikasi?"

- Jawaban: belum sepenuhnya.

Penilaian akhir:

- **Secara konsep dokumen PDF: sesuai.**
- **Secara implementasi codebase: belum ada pemisahan teknis yang tegas antara `super_admin` dan `admin`.**
- `super_admin` saat ini lebih tepat dianggap sebagai `admin` yang diberi seluruh permission, bukan role terpisah dengan jalur UI atau route khusus.

## Referensi Kode

- [database/seeders/RoleSeeder.php](D:/cyborg/sistem-sales/database/seeders/RoleSeeder.php)
- [app/Models/User.php](D:/cyborg/sistem-sales/app/Models/User.php)
- [app/Http/Controllers/Auth/LoginController.php](D:/cyborg/sistem-sales/app/Http/Controllers/Auth/LoginController.php)
- [routes/web.php](D:/cyborg/sistem-sales/routes/web.php)
- [resources/views/layouts/app.blade.php](D:/cyborg/sistem-sales/resources/views/layouts/app.blade.php)
