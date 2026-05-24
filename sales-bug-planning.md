# Planning Perbaikan Bug Sales Dashboard Dan Layout Sales

Tanggal dibuat: 2026-05-23  
Area: Sales login, dashboard sales, absensi sales, PJP sales, layout navigasi sales.

## Ringkasan Masalah

Saat login sebagai sales, user diarahkan ke `sales.dashboard`. Halaman ini terlihat seperti halaman mobile terpisah dan tidak berfungsi penuh. Namun ketika user membuka `/sales/pjp/today` atau `/sales/attendance`, halaman yang muncul memakai layout desktop/sidebar berbeda.

Masalah ini bukan hanya tampilan. Ada dua sumber masalah:

1. Sales dashboard masih berupa mock/static page.
2. Halaman sales tidak memakai satu layout operasional yang konsisten.

## Bukti Dari Codebase

### Login Redirect

File: `app/Http/Controllers/Auth/LoginController.php`

Login sales diarahkan ke:

```php
route('sales.dashboard')
```

Route ini memanggil:

```php
SalesDashboardController@index
```

### Sales Dashboard

File: `app/Http/Controllers/Dashboard/SalesDashboardController.php`

Controller saat ini hanya:

```php
return view('sales.dashboard');
```

Tidak ada data absensi, jadwal hari ini, progress kunjungan, atau riwayat kunjungan yang dikirim ke view.

File: `resources/views/sales/dashboard.blade.php`

Masalah yang ditemukan:

- Memakai `@extends('layouts.mobile')`.
- Status absensi hardcoded: `Belum Absensi`.
- Kunjungan hardcoded: `0 Kunjungan`.
- Jam kerja hardcoded: `0 Jam`.
- Jadwal hardcoded: `Tidak ada jadwal untuk hari ini`.
- Tombol check-in hanya menjalankan `alert()`.
- Komentar JS masih menyebut implementasi belum selesai:

```js
// This will be implemented in Phase 3
```

Artinya dashboard setelah login belum tersambung ke fitur nyata.

### Layout Mobile

File: `resources/views/layouts/mobile.blade.php`

Masalah yang ditemukan:

- Bottom navigation hanya `Beranda` yang mengarah ke route nyata.
- Menu lain masih placeholder:

```html
<a href="#">
```

untuk Jadwal, Lokasi, Riwayat, dan Profil.

Akibatnya user sales masuk ke dashboard dengan navigasi yang terlihat ada, tetapi tidak membawa ke fitur operasional.

### PJP Dan Absensi Sales

File:

- `resources/views/sales/pjp/today.blade.php`
- `resources/views/sales/pjp/show.blade.php`
- `resources/views/sales/pjp/visit-form.blade.php`
- `resources/views/sales/attendance/index.blade.php`

Halaman-halaman ini memakai:

```php
@extends('layouts.app')
```

`layouts.app` adalah layout desktop/sidebar yang juga dipakai admin dan manager. Untuk sales, layout ini menampilkan menu operasional yang benar:

- Jadwal Hari Ini
- Absensi

Namun karena dashboard sales memakai `layouts.mobile`, user mengalami dua pengalaman UI berbeda:

- Setelah login: mobile dashboard dengan bottom nav placeholder.
- Ketika buka PJP/absensi: desktop sidebar layout.

## Akar Masalah

### 1. Sales Dashboard Belum Terintegrasi Ke Data Nyata

`SalesDashboardController@index` belum mengambil:

- Absensi hari ini.
- Jadwal kunjungan hari ini.
- Daftar klien pada jadwal hari ini.
- Total kunjungan.
- Kunjungan selesai.
- Durasi kerja.
- Kunjungan terakhir.

View dashboard akhirnya memakai angka dan status hardcoded.

### 2. Layout Sales Terbelah Dua

Ada dua layout untuk sales:

- `layouts.mobile` untuk `sales.dashboard`.
- `layouts.app` untuk `sales.pjp.*` dan `sales.attendance.*`.

Keduanya memiliki pola navigasi berbeda dan tidak saling sinkron.

### 3. Navigasi Mobile Belum Disambungkan

`layouts.mobile` memiliki bottom nav tetapi sebagian besar link masih `#`. Ini membuat dashboard sales terasa tidak bisa digunakan.

### 4. Dashboard Mengimplementasikan Ulang Absensi Secara Mock

Dashboard punya tombol `Check-In` sendiri, tetapi tidak memakai route nyata:

- `sales.attendance.checkin`
- `sales.attendance.checkout`

Fungsi nyata sudah ada di halaman `/sales/attendance`, sehingga dashboard sebaiknya tidak punya implementasi mock yang berbeda.

## Risiko Jika Dibiarkan

- Sales mengira aplikasi rusak setelah login.
- User harus tahu route manual `/sales/pjp/today` atau `/sales/attendance` agar bisa bekerja.
- Flow mobile dan desktop saling tidak konsisten.
- Ada risiko bug ganda karena absensi bisa dipicu dari dashboard mock dan halaman absensi nyata.
- Test bisa tetap lulus karena sebagian besar hanya memverifikasi route/status, bukan kualitas flow UI setelah login.

## Keputusan Desain Yang Perlu Diambil

Ada dua opsi.

### Opsi A - Gunakan Layout Desktop `layouts.app` Untuk Semua Halaman Sales

Semua halaman sales, termasuk dashboard, memakai `layouts.app`.

Kelebihan:

- Perubahan kecil.
- Konsisten dengan PJP dan absensi saat ini.
- Sidebar sales sudah punya menu operasional nyata.
- Risiko regresi lebih rendah.

Kekurangan:

- Pengalaman mobile sales kurang optimal.

### Opsi B - Gunakan Layout Mobile Untuk Semua Halaman Sales

PJP, detail PJP, visit form, dan absensi dipindah ke `layouts.mobile`, lalu bottom nav disambungkan ke route nyata.

Kelebihan:

- Lebih cocok untuk sales di lapangan.
- UI mobile lebih konsisten.

Kekurangan:

- Perubahan lebih luas.
- Perlu audit responsive semua halaman PJP, detail, form, dan absensi.
- Risiko regressi tampilan lebih tinggi.

### Rekomendasi

Gunakan pendekatan bertahap:

1. Fase 1 memakai Opsi A untuk stabilisasi cepat.
2. Fase 2 baru rapikan `layouts.mobile` jika ingin pengalaman mobile-first.

Dengan begitu bug setelah login bisa cepat hilang tanpa refactor UI besar.

## Rencana Perbaikan

## Fase 1 - Stabilkan Sales Dashboard

Prioritas: Tinggi  
Tujuan: Setelah login, sales masuk ke halaman dashboard yang memakai data nyata dan navigasi yang berfungsi.

### Task

1. Ubah `resources/views/sales/dashboard.blade.php` agar memakai layout yang sama dengan PJP dan absensi:

```php
@extends('layouts.app')
```

2. Update `SalesDashboardController@index` untuk mengambil data nyata:
   - `Absensi::todayFor($user->id)`
   - `JadwalKunjungan::todayFor($user->id)`
   - daftar `jadwal_klien` hari ini
   - total kunjungan
   - kunjungan selesai
   - progress kunjungan
   - riwayat kunjungan terakhir

3. Hilangkan nilai hardcoded di dashboard:
   - `Belum Absensi`
   - `0 Kunjungan`
   - `0 Jam`
   - `Tidak ada jadwal untuk hari ini`

4. Ganti tombol mock `checkInAbsensi()` dengan link atau tombol yang mengarah ke halaman absensi nyata:

```php
route('sales.attendance.index')
```

5. Tambahkan tombol operasional utama:
   - `Absensi`
   - `Jadwal Hari Ini`
   - `Detail PJP` jika ada jadwal hari ini

6. Pastikan dashboard tidak melakukan check-in absensi sendiri kecuali menggunakan endpoint nyata dan pola JS yang sama dengan halaman absensi.

### Acceptance Criteria

- Setelah login sales, halaman dashboard tidak berisi data hardcoded yang menyesatkan.
- Dashboard memakai layout yang sama dengan `/sales/pjp/today` dan `/sales/attendance`.
- Dari dashboard sales, user bisa masuk ke Absensi dan Jadwal Hari Ini lewat tombol/menu nyata.
- Tidak ada fungsi JS mock `checkInAbsensi()` yang hanya menampilkan alert.

### Verifikasi

- `php artisan test`
- `php artisan view:cache`
- Login sales manual lalu cek:
  - `/sales/dashboard`
  - `/sales/attendance`
  - `/sales/pjp/today`

### Status Fase 1 - Selesai 2026-05-23

Dashboard sales sudah distabilkan.

Perubahan:

- `SalesDashboardController@index` sekarang mengambil data nyata:
  - absensi hari ini
  - jadwal kunjungan hari ini
  - daftar klien dalam jadwal
  - total kunjungan
  - kunjungan selesai
  - progress kunjungan
  - riwayat kunjungan terakhir
- `resources/views/sales/dashboard.blade.php` sekarang memakai `layouts.app`, sama seperti halaman PJP dan absensi.
- Dashboard tidak lagi memakai data hardcoded `0 Kunjungan`, `0 Jam`, atau JS mock `checkInAbsensi()`.
- Aksi dashboard diarahkan ke route nyata:
  - `sales.attendance.index`
  - `sales.pjp.today`
  - `sales.pjp.show` jika ada jadwal.

## Fase 2 - Rapikan Navigasi Sales

Prioritas: Tinggi  
Tujuan: Menu sales konsisten dan tidak ada link mati.

### Task

1. Jika `layouts.mobile` tetap dipakai, sambungkan bottom nav ke route nyata:
   - Beranda: `sales.dashboard`
   - Jadwal: `sales.pjp.today`
   - Absensi: `sales.attendance.index`
   - Profil atau akun: `password.change`

2. Jika memilih `layouts.app` sebagai standar sementara, pastikan semua halaman sales memakai `layouts.app`:
   - `sales.dashboard`
   - `sales.pjp.today`
   - `sales.pjp.show`
   - `sales.pjp.visit-form`
   - `sales.pjp.no-schedule`
   - `sales.attendance.index`

3. Hapus atau tandai `layouts.mobile` sebagai legacy jika belum dipakai.

4. Perbaiki active state menu sales:
   - Dashboard aktif hanya untuk `sales.dashboard`.
   - Jadwal aktif untuk `sales.pjp.*`.
   - Absensi aktif untuk `sales.attendance.*`.

### Acceptance Criteria

- Tidak ada link utama sales yang `href="#"`.
- User sales tidak berpindah layout secara mengejutkan antar halaman utama.
- Sidebar/bottom nav menunjukkan active state yang benar.

### Status Fase 2 - Selesai 2026-05-23

Navigasi sales sudah dirapikan.

Perubahan:

- Semua halaman sales utama sekarang konsisten memakai `layouts.app`:
  - dashboard
  - absensi
  - PJP hari ini
  - detail PJP
  - visit form
  - no schedule
- `layouts.mobile` belum dihapus, tetapi bottom nav yang sebelumnya berisi placeholder sekarang diarahkan ke route nyata:
  - Beranda: `sales.dashboard`
  - Jadwal: `sales.pjp.today`
  - Absensi: `sales.attendance.index`
  - Kunjungan: `sales.pjp.today`
  - Akun: `password.change`
- Scan `href="#"`, `checkInAbsensi`, dan komentar mock phase lama pada view sales tidak menemukan hasil.

## Fase 3 - Tambah Test Flow Sales Setelah Login

Prioritas: Sedang  
Tujuan: Bug layout/dashboard tidak muncul lagi tanpa terdeteksi.

### Task

1. Tambahkan test bahwa login sales redirect ke `sales.dashboard`.
2. Tambahkan test dashboard sales render berhasil dengan data:
   - tanpa absensi
   - dengan absensi check-in
   - tanpa jadwal
   - dengan jadwal hari ini
3. Tambahkan assertion bahwa dashboard berisi link ke:
   - `sales.attendance.index`
   - `sales.pjp.today`
4. Tambahkan assertion bahwa dashboard tidak mengandung teks mock seperti:
   - `This will be implemented in Phase 3`
   - `Tidak ada jadwal untuk hari ini` ketika jadwal sebenarnya ada.

### Acceptance Criteria

- Test dashboard sales gagal jika controller tidak mengirim data nyata.
- Test gagal jika tombol operasional utama hilang.
- Test gagal jika view kembali ke mock lama.

### Status Fase 3 - Selesai 2026-05-23

Test regresi flow sales setelah login sudah ditambahkan.

File:

- `tests/Feature/SalesDashboardTest.php`

Coverage:

- Login sales redirect ke `sales.dashboard`.
- Dashboard sales render saat belum ada absensi dan jadwal.
- Dashboard sales menampilkan link nyata ke absensi dan PJP.
- Dashboard sales render data nyata saat ada absensi dan jadwal hari ini.
- Test memastikan dashboard tidak kembali memuat teks/komentar mock lama.

Verifikasi:

| Command | Status | Ringkasan |
| --- | --- | --- |
| `php -l app\Http\Controllers\Dashboard\SalesDashboardController.php` | Berhasil | Tidak ada error sintaks. |
| `php -l tests\Feature\SalesDashboardTest.php` | Berhasil | Tidak ada error sintaks. |
| `php artisan test tests\Feature\SalesDashboardTest.php` | Berhasil | 3 passed, 19 assertions. |
| `rg 'href="#"|This will be implemented in Phase 3|checkInAbsensi|0 Kunjungan' resources\views\sales resources\views\layouts\mobile.blade.php` | Berhasil | Tidak ada hasil. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan route:list` | Berhasil | Route registration berhasil, 79 route. |
| `php artisan test` | Berhasil | 82 passed, 257 assertions, durasi sekitar 44.94 detik. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali untuk environment development. |

## Fase 4 - Optional Mobile-First Sales UX

Prioritas: Sedang-Rendah  
Tujuan: Jika target utama sales adalah mobile, semua halaman sales dibuat mobile-first secara konsisten.

### Task

1. Evaluasi apakah sales memang harus memakai `layouts.mobile`.
2. Jika ya, pindahkan halaman sales utama ke layout mobile:
   - dashboard
   - absensi
   - PJP hari ini
   - detail PJP
   - visit form
3. Pastikan bottom navigation hanya berisi fitur nyata.
4. Uji tampilan di viewport mobile.

### Acceptance Criteria

- Semua halaman sales nyaman digunakan di mobile.
- Tidak ada layout desktop/sidebar pada flow sales mobile.
- Semua tombol GPS/upload/form tetap bekerja.

### Status Fase 4 - Selesai 2026-05-23

Mobile-first UX sales sudah diterapkan tanpa mengorbankan tampilan desktop.

Keputusan implementasi:

- Dibuat layout khusus `resources/views/layouts/sales.blade.php`.
- Layout sales baru dipakai oleh semua halaman sales utama:
  - dashboard
  - absensi
  - PJP hari ini
  - detail PJP
  - visit form
  - no schedule
- Mobile memakai topbar ringkas dan bottom navigation sticky.
- Desktop memakai sidebar kiri khusus sales dan area konten dengan lebar maksimum agar tetap nyaman dibaca.
- Bottom navigation hanya berisi route nyata:
  - `sales.dashboard`
  - `sales.pjp.today`
  - `sales.attendance.index`
  - `password.change`
- `layouts.mobile` tidak dipakai lagi oleh flow sales utama setelah layout sales khusus tersedia.

Verifikasi:

| Command | Status | Ringkasan |
| --- | --- | --- |
| `rg "@extends\('layouts\.(app\|mobile)'\)" resources\views\sales` | Berhasil | Tidak ada view sales yang masih memakai `layouts.app` atau `layouts.mobile`. |
| `php artisan view:cache` | Berhasil | Blade templates berhasil dikompilasi. |
| `php artisan test tests\Feature\SalesDashboardTest.php tests\Feature\Phase10OperationalSmokeTest.php` | Berhasil | 5 passed, 50 assertions. |
| `php artisan test` | Berhasil | 82 passed, 257 assertions. |
| `php artisan route:list` | Berhasil | Route registration berhasil, 79 route. |
| `php artisan view:clear` | Berhasil | Cache view dibersihkan kembali untuk environment development. |

## File Yang Kemungkinan Diubah

- `app/Http/Controllers/Dashboard/SalesDashboardController.php`
- `resources/views/sales/dashboard.blade.php`
- `resources/views/layouts/mobile.blade.php`
- `resources/views/sales/pjp/no-schedule.blade.php`
- `tests/Feature/SalesDashboardTest.php`
- `tests/Feature/Auth/AuthenticationTest.php`

## Catatan Implementasi

Perbaikan sebaiknya dimulai dari Fase 1 dan Fase 3. Jangan langsung refactor semua halaman sales ke mobile layout sebelum dashboard stabil, karena halaman PJP dan absensi saat ini sudah berjalan dengan `layouts.app`.

## Definition Of Done

Perbaikan dianggap selesai jika:

1. Login sales masuk ke dashboard yang fungsional.
2. Dashboard sales memakai data nyata dari database.
3. Layout sales konsisten antar dashboard, absensi, dan PJP.
4. Tidak ada menu utama sales yang link-nya `#`.
5. `php artisan test` lulus.
6. `php artisan view:cache` lulus.
