PRODUCT REQUIREMENTS DOCUMENT
(PRD)
Aplikasi Monitoring Aktivitas dan Kinerja Sales Force Berbasis Web
pada PT. Tridaya Sakti Medima

Atribut	Keterangan
Nama Sistem	Aplikasi Monitoring Aktivitas dan Kinerja Sales Force
Studi Kasus	PT. Tridaya Sakti Medima
Platform	Web Responsif (Mobile & Desktop)
Tech Stack	Laravel 12, MySQL, Bootstrap 5, Yajra DataTables, Leaflet.js
Versi Dokumen	1.0
Tanggal	Maret 2026
Status	Draft Awal


1.  PENDAHULUAN

1.1  Latar Belakang
PT. Tridaya Sakti Medima adalah perusahaan yang bergerak di bidang distribusi alat kesehatan dan farmasi. Dalam menjalankan operasionalnya, perusahaan memiliki tim sales force yang aktif melakukan kunjungan ke toko, apotek, klinik, dan rumah sakit setiap harinya.
Saat ini proses pelaporan aktivitas sales masih dilakukan secara manual, yaitu melalui pesan WhatsApp, telepon, atau catatan kertas yang kemudian direkap ulang oleh manajer. Metode ini menimbulkan berbagai permasalahan antara lain: sulitnya verifikasi lokasi kunjungan secara akurat (potensi ghost check-in), keterlambatan informasi yang diterima manajer, tidak adanya histori kunjungan yang terstruktur, serta proses evaluasi kinerja yang membutuhkan waktu lama.
Untuk mengatasi permasalahan tersebut, dibutuhkan sebuah sistem informasi berbasis web yang mampu memonitor aktivitas sales secara real-time, memvalidasi kunjungan melalui GPS dan foto, serta menghasilkan laporan kinerja secara otomatis.

1.2  Tujuan Dokumen
Dokumen PRD ini bertujuan untuk:
•	Mendefinisikan ruang lingkup, fitur, dan persyaratan fungsional sistem secara lengkap.
•	Menjadi acuan pengembangan bagi tim developer selama proses pembangunan sistem.
•	Menjadi referensi evaluasi dan validasi kesesuaian sistem dengan kebutuhan pengguna.
•	Mendukung penyusunan laporan skripsi di Politeknik Negeri Sriwijaya.

1.3  Ruang Lingkup Sistem
Sistem yang dibangun mencakup:
•	Manajemen data master: karyawan/sales, klien/toko, dan jadwal kunjungan (PJP).
•	Fitur check-in dan check-out berbasis validasi GPS dengan radius toleransi.
•	Pengambilan foto bukti kunjungan langsung dari kamera (tanpa galeri).
•	Pengisian formulir laporan kunjungan (Visit Form) secara digital.
•	Dashboard monitoring real-time pergerakan sales di atas peta.
•	Pelaporan kinerja sales yang dapat diekspor ke Excel dan PDF.
•	Sistem absensi harian terintegrasi dengan aktivitas kunjungan.

 
2.  DESKRIPSI PRODUK

2.1  Gambaran Umum Sistem
Sistem ini merupakan aplikasi web responsif yang dapat diakses melalui peramban di perangkat smartphone maupun komputer. Sistem dirancang dengan dua antarmuka utama:
•	Antarmuka Mobile (Sales): Dioptimalkan untuk penggunaan di lapangan melalui smartphone. Menampilkan jadwal kunjungan, tombol check-in/check-out, form kunjungan, dan kamera.
•	Antarmuka Desktop (Manager & Admin): Dioptimalkan untuk layar lebar. Menampilkan dashboard peta real-time, tabel laporan, dan fitur manajemen data.

2.2  Pengguna Sistem (Aktor)
Peran	Deskripsi & Hak Akses
Super Admin	Mengelola seluruh data sistem: manajemen user, master data klien, master data wilayah, konfigurasi sistem. Memiliki akses penuh ke semua modul.
Admin	Mengelola jadwal kunjungan (PJP), input data klien/toko, verifikasi laporan. Tidak dapat mengakses konfigurasi sistem.
Sales	Akses via mobile web: absensi, melihat jadwal PJP, check-in/check-out dengan GPS, pengisian Visit Form, upload foto bukti.
Manager / Pimpinan	Akses dashboard monitoring real-time, melihat laporan kinerja tim, export laporan. Read-only untuk data kunjungan.

2.3  Asumsi & Batasan
Asumsi:
•	Setiap sales memiliki smartphone dengan fitur GPS aktif dan koneksi internet.
•	Peramban yang digunakan mendukung Web API Geolocation dan kamera (Chrome/Firefox versi terkini).
•	Server aplikasi terhubung ke internet dan dapat diakses 24/7.
•	Data lokasi klien/toko sudah tersedia dan diinput oleh Admin sebelum operasional.

Batasan:
•	Sistem tidak memiliki fitur navigasi turn-by-turn (bukan aplikasi maps).
•	Sistem tidak terintegrasi langsung dengan sistem ERP atau akuntansi perusahaan.
•	Validasi GPS bergantung pada akurasi perangkat sales dan kondisi sinyal.
•	Fitur notifikasi push hanya melalui email dan tampilan dashboard (bukan push notification native).

 
3.  KEBUTUHAN FUNGSIONAL

3.1  Modul Autentikasi & Manajemen Akun
ID	Fitur	Deskripsi	Prioritas
F-01	Login Sistem	Pengguna dapat masuk ke sistem menggunakan email dan password. Sistem menggunakan Laravel Breeze sebagai fondasi autentikasi.	Tinggi
F-02	Manajemen Role	Super Admin dapat menetapkan role kepada pengguna (Sales, Admin, Manager, Super Admin) menggunakan Spatie Laravel Permission.	Tinggi
F-03	Ganti Password	Pengguna dapat mengganti password melalui halaman profil.	Sedang
F-04	Logout	Pengguna dapat keluar dari sesi aktif secara aman.	Tinggi

3.2  Modul Absensi
ID	Fitur	Deskripsi	Prioritas
F-05	Absensi Masuk	Sales melakukan check-in absensi harian dengan mencatat waktu dan lokasi GPS saat tombol ditekan.	Tinggi
F-06	Absensi Pulang	Sales melakukan check-out absensi harian. Sistem menghitung total jam kerja secara otomatis.	Tinggi
F-07	Rekap Absensi	Admin dan Manager dapat melihat rekap absensi harian, mingguan, dan bulanan seluruh sales.	Sedang

3.3  Modul Jadwal Kunjungan (PJP)
ID	Fitur	Deskripsi	Prioritas
F-08	Input PJP	Admin dapat membuat jadwal kunjungan harian untuk setiap sales beserta daftar klien yang harus dikunjungi.	Tinggi
F-09	Tampil Jadwal Sales	Sales melihat daftar kunjungan hari ini yang sudah dijadwalkan, ditampilkan berurutan berdasarkan prioritas.	Tinggi
F-10	Mulai Perjalanan	Sales menekan tombol 'Mulai Perjalanan' untuk memulai sesi kunjungan. Sistem mencatat waktu keberangkatan.	Tinggi
F-11	Manajemen Klien	Admin dapat menambah, mengubah, dan menghapus data klien beserta koordinat GPS lokasi toko/klinik/apotek.	Tinggi

3.4  Modul Check-In & Validasi GPS
ID	Fitur	Deskripsi	Prioritas
F-12	Check-In Kunjungan	Sales menekan tombol Check-in saat tiba di lokasi klien. Sistem membaca koordinat GPS secara otomatis.	Tinggi
F-13	Validasi Radius GPS	Sistem membandingkan koordinat sales dengan koordinat klien. Jika jarak <= 100 meter, check-in diizinkan. Jika > 100m, sistem menampilkan peringatan.	Tinggi
F-14	Foto Bukti Kunjungan	Setelah check-in berhasil, sales wajib mengambil foto langsung dari kamera (bukan galeri). Sistem menonaktifkan opsi upload dari galeri.	Tinggi
F-15	Check-Out Kunjungan	Sales menekan tombol Check-out setelah selesai. Sistem mencatat waktu keluar dan menghitung durasi kunjungan otomatis.	Tinggi

3.5  Modul Visit Form (Laporan Kunjungan)
ID	Fitur	Deskripsi	Prioritas
F-16	Isi Visit Form	Sales mengisi formulir digital berisi: Status Kunjungan (dropdown), Catatan lapangan (textarea), kondisi stok (opsional).	Tinggi
F-17	Status Kunjungan	Pilihan status: Order Diterima / Follow-up / Toko Tutup / Stok Masih Ada / Lainnya.	Tinggi
F-18	Simpan & Sinkronisasi	Data Visit Form, foto, GPS, dan timestamp dikirim ke server segera setelah tombol Simpan ditekan.	Tinggi
F-19	Riwayat Kunjungan	Sales dapat melihat riwayat kunjungan yang pernah dilakukan beserta statusnya.	Sedang

3.6  Modul Dashboard Monitoring (Manager)
ID	Fitur	Deskripsi	Prioritas
F-20	Peta Real-Time	Dashboard menampilkan peta (Leaflet.js + OpenStreetMap) dengan pin lokasi setiap sales secara real-time.	Tinggi
F-21	Status Pin Kunjungan	Pin klien berwarna: Abu-abu (belum dikunjungi), Hijau (sudah selesai), Kuning (sedang dikunjungi).	Tinggi
F-22	Deteksi Tidak Bergerak	Sistem memberikan indikator peringatan pada dashboard jika sales tidak bergerak lebih dari 60 menit.	Sedang
F-23	Info Pop-up	Manager dapat mengklik pin sales untuk melihat detail: nama, lokasi terakhir, status kunjungan hari ini.	Sedang
F-24	Ringkasan Dashboard	Menampilkan statistik ringkas: total sales aktif, total kunjungan hari ini, kunjungan selesai vs target.	Tinggi

3.7  Modul Laporan & Export
ID	Fitur	Deskripsi	Prioritas
F-25	Laporan Kinerja Sales	Manager dapat melihat laporan performa per-sales: jumlah kunjungan, sesuai/di luar jadwal, total jam lapangan.	Tinggi
F-26	Filter Rentang Tanggal	Laporan dapat difilter berdasarkan rentang tanggal (harian, mingguan, bulanan, kustom).	Tinggi
F-27	Export Excel	Laporan kinerja dapat diekspor ke format .xlsx menggunakan Laravel Excel / Maatwebsite.	Tinggi
F-28	Export PDF	Laporan kinerja dapat diekspor ke format .pdf menggunakan DomPDF / Barryvdh.	Tinggi
F-29	Laporan Absensi	Admin dapat mengekspor rekap absensi bulanan seluruh sales ke Excel.	Sedang

3.8  Modul Manajemen Data Master (Admin)
ID	Fitur	Deskripsi	Prioritas
F-30	CRUD User/Sales	Super Admin dan Admin dapat mengelola data pengguna: tambah, ubah, hapus, reset password.	Tinggi
F-31	CRUD Data Klien	Admin dapat mengelola data klien/toko: nama, alamat, koordinat GPS, kategori (apotek/klinik/RS/toko).	Tinggi
F-32	CRUD Wilayah	Admin dapat mengelola data wilayah/area kerja yang kemudian dikaitkan dengan sales dan klien.	Sedang
F-33	Konfigurasi Radius	Super Admin dapat mengubah radius toleransi GPS check-in (default 100 meter).	Rendah

 
4.  KEBUTUHAN NON-FUNGSIONAL

Kategori	Persyaratan
Performa	Halaman utama dan dashboard harus memuat dalam waktu <= 3 detik pada koneksi 4G. Data GPS dan status kunjungan diperbarui maksimal setiap 30 detik.
Keamanan	Autentikasi menggunakan session-based (Laravel Breeze). Setiap endpoint API dilindungi middleware auth dan role. Password di-hash menggunakan bcrypt.
Ketersediaan	Sistem tersedia minimal 99% waktu operasional (07.00–20.00 WIB). Downtime terjadwal di luar jam operasional.
Skalabilitas	Sistem mampu menangani hingga 50 pengguna aktif simultan tanpa penurunan performa signifikan.
Kegunaan (Usability)	Antarmuka mobile harus dapat dioperasikan dengan satu tangan. Ukuran tombol minimal 44x44 piksel. Tulisan minimal 14pt.
Kompatibilitas	Berjalan di Chrome v100+, Firefox v100+, Edge v100+ di Android 8+ dan iOS 13+. Desktop: Windows 10/11, macOS.
Maintainability	Kode mengikuti standar PSR-12. Struktur Laravel MVC. Setiap fitur memiliki migration database terpisah.
Integritas Data	Foto bukti kunjungan disimpan di server. Koordinat GPS disimpan dengan presisi 6 desimal. Data tidak dapat dihapus oleh Sales.

 
5.  DESAIN DATABASE

Database sistem terdiri dari 13 tabel utama yang saling berelasi. Berikut adalah skema setiap tabel:

Tabel: users
Kolom	Tipe Data	Constraint	Keterangan
id	BIGINT UNSIGNED	PK, AI	Primary key
name	VARCHAR(100)	NOT NULL	Nama lengkap pengguna
email	VARCHAR(100)	UNIQUE, NOT NULL	Email untuk login
password	VARCHAR(255)	NOT NULL	Password terenkripsi (bcrypt)
phone	VARCHAR(20)	NULLABLE	Nomor telepon
photo	VARCHAR(255)	NULLABLE	Path foto profil
wilayah_id	BIGINT UNSIGNED	FK, NULLABLE	Relasi ke tabel wilayah
is_active	TINYINT(1)	DEFAULT 1	Status aktif akun
remember_token	VARCHAR(100)	NULLABLE	Token remember me
created_at	TIMESTAMP	NULLABLE	Waktu dibuat
updated_at	TIMESTAMP	NULLABLE	Waktu diperbarui

Tabel: wilayah
Kolom	Tipe Data	Constraint	Keterangan
id	BIGINT UNSIGNED	PK, AI	Primary key
nama_wilayah	VARCHAR(100)	NOT NULL	Nama area kerja
keterangan	TEXT	NULLABLE	Deskripsi wilayah
created_at	TIMESTAMP	NULLABLE	Waktu dibuat
updated_at	TIMESTAMP	NULLABLE	Waktu diperbarui

Tabel: klien
Kolom	Tipe Data	Constraint	Keterangan
id	BIGINT UNSIGNED	PK, AI	Primary key
nama_klien	VARCHAR(150)	NOT NULL	Nama toko/apotek/klinik/RS
kategori	ENUM	NOT NULL	apotek, klinik, rs, toko, lainnya
alamat	TEXT	NOT NULL	Alamat lengkap
wilayah_id	BIGINT UNSIGNED	FK, NOT NULL	Relasi ke tabel wilayah
latitude	DECIMAL(10,7)	NOT NULL	Koordinat GPS - latitude
longitude	DECIMAL(10,7)	NOT NULL	Koordinat GPS - longitude
contact_person	VARCHAR(100)	NULLABLE	Nama PIC klien
phone	VARCHAR(20)	NULLABLE	Nomor telepon klien
is_active	TINYINT(1)	DEFAULT 1	Status aktif klien
created_at	TIMESTAMP	NULLABLE	Waktu dibuat
updated_at	TIMESTAMP	NULLABLE	Waktu diperbarui

Tabel: jadwal_kunjungan
Kolom	Tipe Data	Constraint	Keterangan
id	BIGINT UNSIGNED	PK, AI	Primary key
user_id	BIGINT UNSIGNED	FK, NOT NULL	Sales yang ditugaskan
tanggal	DATE	NOT NULL	Tanggal jadwal kunjungan
keterangan	TEXT	NULLABLE	Catatan jadwal dari admin
status	ENUM	DEFAULT pending	pending, aktif, selesai
created_by	BIGINT UNSIGNED	FK, NOT NULL	Admin yang membuat jadwal
created_at	TIMESTAMP	NULLABLE	Waktu dibuat
updated_at	TIMESTAMP	NULLABLE	Waktu diperbarui

Tabel: jadwal_klien
Kolom	Tipe Data	Constraint	Keterangan
id	BIGINT UNSIGNED	PK, AI	Primary key
jadwal_id	BIGINT UNSIGNED	FK, NOT NULL	Relasi ke jadwal_kunjungan
klien_id	BIGINT UNSIGNED	FK, NOT NULL	Relasi ke klien
urutan	SMALLINT	DEFAULT 1	Urutan prioritas kunjungan
status	ENUM	DEFAULT pending	pending, dikunjungi, dilewati
created_at	TIMESTAMP	NULLABLE	Waktu dibuat
updated_at	TIMESTAMP	NULLABLE	Waktu diperbarui

Tabel: kunjungan
Kolom	Tipe Data	Constraint	Keterangan
id	BIGINT UNSIGNED	PK, AI	Primary key
user_id	BIGINT UNSIGNED	FK, NOT NULL	Sales pelaksana kunjungan
klien_id	BIGINT UNSIGNED	FK, NOT NULL	Klien yang dikunjungi
jadwal_klien_id	BIGINT UNSIGNED	FK, NULLABLE	Relasi ke jadwal (null jika kunjungan luar jadwal)
tanggal	DATE	NOT NULL	Tanggal kunjungan
waktu_checkin	DATETIME	NOT NULL	Waktu check-in
waktu_checkout	DATETIME	NULLABLE	Waktu check-out
durasi_menit	SMALLINT	NULLABLE	Durasi kunjungan dalam menit
lat_checkin	DECIMAL(10,7)	NOT NULL	Latitude saat check-in
lng_checkin	DECIMAL(10,7)	NOT NULL	Longitude saat check-in
lat_checkout	DECIMAL(10,7)	NULLABLE	Latitude saat check-out
lng_checkout	DECIMAL(10,7)	NULLABLE	Longitude saat check-out
jarak_meter	DECIMAL(8,2)	NOT NULL	Jarak GPS dari titik klien (meter)
foto_bukti	VARCHAR(255)	NOT NULL	Path foto bukti kunjungan
is_sesuai_jadwal	TINYINT(1)	DEFAULT 0	1 jika sesuai PJP, 0 jika di luar jadwal
created_at	TIMESTAMP	NULLABLE	Waktu dibuat
updated_at	TIMESTAMP	NULLABLE	Waktu diperbarui

Tabel: visit_form
Kolom	Tipe Data	Constraint	Keterangan
id	BIGINT UNSIGNED	PK, AI	Primary key
kunjungan_id	BIGINT UNSIGNED	FK, UNIQUE, NOT NULL	Relasi 1:1 ke kunjungan
status_kunjungan	ENUM	NOT NULL	order, followup, tutup, stok_ada, lainnya
catatan	TEXT	NULLABLE	Catatan temuan lapangan
kondisi_stok	ENUM	NULLABLE	kosong, menipis, aman, berlebih
nominal_order	DECIMAL(15,2)	NULLABLE	Nominal order jika ada (Rp)
created_at	TIMESTAMP	NULLABLE	Waktu dibuat
updated_at	TIMESTAMP	NULLABLE	Waktu diperbarui

Tabel: absensi
Kolom	Tipe Data	Constraint	Keterangan
id	BIGINT UNSIGNED	PK, AI	Primary key
user_id	BIGINT UNSIGNED	FK, NOT NULL	Sales bersangkutan
tanggal	DATE	NOT NULL	Tanggal absensi
waktu_masuk	DATETIME	NOT NULL	Waktu absensi masuk
waktu_keluar	DATETIME	NULLABLE	Waktu absensi pulang
total_jam	DECIMAL(4,2)	NULLABLE	Total jam kerja
lat_masuk	DECIMAL(10,7)	NOT NULL	Koordinat saat absensi masuk
lng_masuk	DECIMAL(10,7)	NOT NULL	Koordinat saat absensi masuk
keterangan	VARCHAR(255)	NULLABLE	Catatan absensi
created_at	TIMESTAMP	NULLABLE	Waktu dibuat
updated_at	TIMESTAMP	NULLABLE	Waktu diperbarui

Tabel: lokasi_realtime
Kolom	Tipe Data	Constraint	Keterangan
id	BIGINT UNSIGNED	PK, AI	Primary key
user_id	BIGINT UNSIGNED	FK, NOT NULL	Sales bersangkutan
latitude	DECIMAL(10,7)	NOT NULL	Koordinat latitude terkini
longitude	DECIMAL(10,7)	NOT NULL	Koordinat longitude terkini
akurasi_meter	DECIMAL(6,2)	NULLABLE	Akurasi GPS perangkat (meter)
recorded_at	DATETIME	NOT NULL	Waktu pencatatan lokasi
created_at	TIMESTAMP	NULLABLE	Waktu dibuat

 
6.  ARSITEKTUR & TEKNOLOGI

6.1  Tech Stack
Komponen	Teknologi / Library
Backend Framework	Laravel 12 (PHP 8.2+)
Autentikasi	Laravel Breeze (Session-based)
Manajemen Role	Spatie Laravel Permission
Database	MySQL 8.0+
ORM	Eloquent ORM (built-in Laravel)
Frontend Styling	Bootstrap 5.3
Tabel Interaktif	Yajra DataTables (server-side)
Peta & GPS	Leaflet.js + OpenStreetMap (gratis, no API key)
Export Excel	Maatwebsite Laravel Excel
Export PDF	Barryvdh Laravel DomPDF
Kamera Web API	JavaScript MediaDevices.getUserMedia()
Server	Apache/Nginx + PHP-FPM
Version Control	Git + GitHub

6.2  Struktur Direktori Laravel
app/
  ├── Http/Controllers/         → AuthController, KunjunganController, DashboardController, LaporanController, ...
  ├── Http/Middleware/           → RoleMiddleware
  ├── Models/                   → User, Klien, Kunjungan, VisitForm, Absensi, JadwalKunjungan, ...
  └── Services/                 → GpsValidationService, KunjunganService
database/
  ├── migrations/               → Satu file per tabel
  └── seeders/                  → RoleSeeder, UserSeeder, KlienSeeder
resources/views/
  ├── layouts/                  → app.blade.php (desktop), mobile.blade.php
  ├── sales/                    → jadwal, checkin, visitform
  ├── dashboard/                → index (peta real-time)
  └── laporan/                  → performa, absensi, export
routes/
  └── web.php                   → Routing dengan middleware auth & role

6.3  Pola Arsitektur
•	MVC Pattern: Model menangani logika data dan relasi Eloquent. Controller memproses request. View merender tampilan Blade.
•	Service Layer: GpsValidationService untuk kalkulasi jarak Haversine. KunjunganService untuk logika bisnis check-in/check-out.
•	Middleware Role: Setiap grup route dilindungi middleware role (role:sales, role:manager, dsb.) dari Spatie Permission.
•	AJAX / DataTables: Tabel data menggunakan Yajra DataTables server-side untuk efisiensi query pada data besar.

 
7.  ALUR KERJA SISTEM (USER FLOW)

7.1  Alur Sales (Mobile Web)
1.	Login menggunakan email dan password.
2.	Lakukan Absensi Masuk – sistem mencatat waktu dan GPS.
3.	Buka menu Jadwal Kunjungan Hari Ini (PJP).
4.	Tekan tombol Mulai Perjalanan ke klien pertama.
5.	Tiba di lokasi → tekan Check-In. Sistem validasi GPS (radius 100m).
6.	Ambil Foto Bukti Kunjungan langsung dari kamera.
7.	Isi Visit Form: status, catatan, kondisi stok.
8.	Tekan Check-Out. Sistem catat durasi otomatis.
9.	Ulangi langkah 4–8 untuk klien berikutnya.
10.	Akhir hari: lakukan Absensi Pulang.

7.2  Alur Manager (Desktop Web)
11.	Login menggunakan email dan password.
12.	Buka Dashboard Monitoring – tampil peta real-time seluruh sales.
13.	Monitor status pin: Abu-abu (belum), Kuning (sedang), Hijau (selesai).
14.	Cek alert jika ada sales tidak bergerak >60 menit.
15.	Buka menu Laporan Performa, pilih rentang tanggal.
16.	Lihat ringkasan kinerja per-sales. Tekan Export Excel/PDF.

7.3  Alur Admin
17.	Login menggunakan email dan password.
18.	Kelola data Master: Klien (dengan koordinat GPS), Wilayah, User/Sales.
19.	Buat Jadwal Kunjungan (PJP) harian untuk setiap sales.
20.	Tentukan daftar klien dan urutan kunjungan untuk setiap sales.
21.	Monitor rekap absensi dan kunjungan melalui tabel laporan.

 
8.  RENCANA PENGEMBANGAN (SPRINT PLAN)

Minggu / Sprint	Aktivitas Pengembangan
Minggu 1	Setup project Laravel 12, konfigurasi database MySQL, install package (Breeze, Spatie, DataTables, Excel, DomPDF). Buat semua migration dan seeder awal.
Minggu 2	Implementasi modul Autentikasi: login, logout, manajemen role (Super Admin, Admin, Manager, Sales). Buat layout blade desktop dan mobile.
Minggu 3	Modul Master Data: CRUD User, CRUD Klien (dengan input koordinat GPS + peta Leaflet), CRUD Wilayah.
Minggu 4	Modul Jadwal Kunjungan (PJP): CRUD jadwal, assign klien ke jadwal, tampilan jadwal untuk sales.
Minggu 5	Modul Absensi: check-in/check-out absensi harian dengan pencatatan GPS dan waktu otomatis.
Minggu 6	Modul Kunjungan: check-in dengan validasi radius GPS (Haversine formula), foto bukti kamera, check-out otomatis.
Minggu 7	Modul Visit Form: pengisian status kunjungan, catatan, kondisi stok. Sinkronisasi data ke server.
Minggu 8	Dashboard Monitoring: integrasi Leaflet.js, pin real-time, perubahan warna status, pop-up info sales, alert tidak bergerak.
Minggu 9	Modul Laporan & Export: tabel performa per-sales, filter tanggal, export Excel dan PDF.
Minggu 10	Testing menyeluruh (unit test, UAT), perbaikan bug, optimasi performa query, dokumentasi akhir.

 
9.  KRITERIA KEBERHASILAN

Kriteria	Target / Indikator
Fungsionalitas	Seluruh 33 fitur yang terdefinisi di Bab 3 berjalan tanpa error pada skenario uji normal.
Akurasi GPS	Validasi GPS berhasil menolak check-in di luar radius 100m pada minimal 95% skenario uji.
Performa Halaman	Waktu muat halaman <= 3 detik pada koneksi 4G untuk semua halaman utama.
Keamanan Role	Pengguna tidak dapat mengakses halaman di luar role-nya (uji manual 100% route).
Export Laporan	Laporan Excel dan PDF berhasil digenerate dengan data yang akurat dan format yang rapi.
Kegunaan Mobile	Proses check-in hingga submit Visit Form dapat diselesaikan dalam <= 3 menit oleh pengguna baru.
Integritas Data	Tidak ada data kunjungan yang hilang atau berubah tanpa otorisasi setelah proses simpan.

 
10.  RISIKO & MITIGASI

Risiko	Mitigasi
Akurasi GPS rendah di area gedung/basement	Tambahkan toleransi radius yang dapat dikonfigurasi Admin. Berikan opsi manual override dengan persetujuan Manager.
Sales menggunakan gambar dari galeri (kecurangan)	Implementasikan capture langsung dari kamera menggunakan MediaDevices API. Nonaktifkan opsi file input dari galeri.
Koneksi internet tidak stabil di lapangan	Simpan data kunjungan sementara di localStorage browser, kirim ulang otomatis saat koneksi pulih.
Server tidak tersedia (downtime)	Siapkan backup server atau hosting dengan SLA uptime tinggi. Tambahkan halaman maintenance yang informatif.
Data GPS disalahgunakan (spoofing)	Rekam metadata waktu server saat data diterima. Bandingkan timestamp GPS dengan waktu server untuk deteksi anomali.
Performa lambat saat data besar	Gunakan Yajra DataTables server-side, tambahkan index database pada kolom yang sering di-query (user_id, tanggal, klien_id).


Dokumen PRD ini merupakan acuan pengembangan Aplikasi Monitoring Aktivitas dan Kinerja Sales Force Berbasis Web pada PT. Tridaya Sakti Medima.
Versi 1.0  |  Maret 2026  |  Program Studi Manajemen Informatika – Politeknik Negeri Sriwijaya

