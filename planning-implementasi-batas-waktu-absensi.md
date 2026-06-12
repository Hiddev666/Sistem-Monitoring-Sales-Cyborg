# Planning Implementasi Batas Waktu Absensi Sales

Tanggal dibuat: 2026-06-11  
Area: absensi sales, validasi waktu check-in/check-out, UI absensi, dan test otomatis.

## Tujuan

Menambahkan batas waktu absensi untuk sales بحيث:

- Sales hanya bisa `check-in` dan `check-out` pada rentang pukul `08:00` sampai `16:30`.
- Di luar rentang itu, request harus ditolak di backend.
- UI juga harus memberi informasi yang jelas supaya user tidak mencoba aksi yang sudah pasti gagal.

## Aturan Bisnis Yang Disepakati

1. Batas waktu berlaku untuk dua aksi:
   - `check-in`
   - `check-out`
2. Rentang waktu aktif adalah:
   - mulai `08:00:00`
   - sampai `16:30:00` inclusive
3. Validasi harus mengikuti timezone aplikasi/server yang dipakai sistem.
4. Penolakan harus konsisten:
   - `check-in` di luar jam ditolak.
   - `check-out` di luar jam ditolak.
5. Jika sales sudah punya absensi hari ini, aturan lama tetap berlaku:
   - tidak bisa check-in dua kali.
   - tidak bisa check-out jika belum check-in.
   - tidak bisa check-out jika masih ada kunjungan aktif yang belum selesai.

## Titik Masuk Kode Yang Perlu Diubah

- [app/Http/Controllers/AbsensiController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/AbsensiController.php)
- [resources/views/sales/attendance/index.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/attendance/index.blade.php)
- [tests/Feature/SalesAttendanceCheckoutRuleTest.php](/D:/cyborg/sistem-sales/tests/Feature/SalesAttendanceCheckoutRuleTest.php)
- Potensi file test baru untuk skenario jam absensi, jika ingin dipisah agar rapi.

## Rencana Implementasi

### Fase 1 - Tambahkan Guard Waktu Di Backend

Prioritas: Tinggi  
Tujuan: memastikan batas jam benar-benar ditegakkan walaupun request dikirim langsung ke endpoint.

#### Langkah

1. Tambahkan helper waktu di `AbsensiController` untuk mengecek apakah jam saat ini berada di antara `08:00` dan `16:30`.
2. Panggil helper tersebut di awal method `checkIn()` dan `checkOut()`.
3. Jika di luar jam, kembalikan response JSON dengan status `400` dan pesan yang jelas, misalnya:
   - `Absensi hanya dapat dilakukan antara pukul 08:00 sampai 16:30.`
4. Pastikan aturan ini dievaluasi sebelum proses validasi lokasi atau update database, supaya request yang tidak valid berhenti lebih awal.

#### Catatan Implementasi

- Gunakan perbandingan jam yang eksplisit, bukan hanya `hour`.
- Jika ingin lebih aman, gunakan objek waktu yang konsisten seperti Carbon.
- Karena batas atas `16:30`, aksi pada `16:30:00` tetap diperbolehkan.

#### Acceptance Criteria

- Request `check-in` sebelum `08:00` ditolak.
- Request `check-in` setelah `16:30` ditolak.
- Request `check-out` sebelum `08:00` ditolak.
- Request `check-out` setelah `16:30` ditolak.
- Request valid dalam rentang waktu tetap berfungsi seperti biasa.

### Fase 2 - Beri Feedback Di UI Absensi

Prioritas: Medium  
Tujuan: user langsung tahu kapan absensi tersedia tanpa harus menunggu error dari server.

#### Langkah

1. Tambahkan informasi jam operasional pada halaman absensi sales.
2. Saat halaman dibuka di luar jam absensi:
   - tampilkan banner/peringatan bahwa absensi hanya aktif pukul `08:00-16:30`
   - nonaktifkan tombol `Check-In` dan `Check-Out`
3. Jika halaman dibuka saat masih dalam jam operasional:
   - tombol tetap aktif sesuai status absensi harian
4. Tampilkan pesan fallback dari backend jika user tetap memaksa request lewat devtools atau direct call.

#### Catatan Implementasi

- UI tidak boleh menjadi satu-satunya penjaga aturan.
- Disable tombol hanya untuk UX; backend tetap sumber kebenaran.

#### Acceptance Criteria

- Halaman absensi menampilkan status jam operasional dengan jelas.
- Tombol aksi tidak terlihat aktif saat di luar jam absensi.
- Pesan error dari backend tetap informatif jika request dipaksa.

### Fase 3 - Tambahkan Dan Perbarui Test

Prioritas: Tinggi  
Tujuan: aturan jam absensi tidak mudah rusak saat ada perubahan berikutnya.

#### Langkah

1. Tambahkan test untuk `check-in`:
   - berhasil saat jam berada dalam rentang `08:00-16:30`
   - gagal saat jam sebelum `08:00`
   - gagal saat jam setelah `16:30`
2. Tambahkan test untuk `check-out`:
   - berhasil saat jam berada dalam rentang `08:00-16:30`
   - gagal saat jam sebelum `08:00`
   - gagal saat jam setelah `16:30`
3. Pertahankan test existing untuk aturan checkout kunjungan aktif yang sudah ada.
4. Jika perlu, pecah test menjadi file terpisah agar skenario jam lebih mudah dibaca.

#### Catatan Implementasi

- Gunakan time travel/freeze time di test agar hasil stabil.
- Test harus memverifikasi:
   - status HTTP
   - pesan error
   - database tidak berubah jika request ditolak

#### Acceptance Criteria

- Ada test yang membuktikan batas waktu bekerja di backend.
- Test existing checkout tidak rusak.
- Semua skenario jam penting ter-cover.

### Fase 4 - Verifikasi Perilaku Akhir

Prioritas: Medium  
Tujuan: memastikan alur operasional sales tetap masuk akal setelah batas waktu ditambahkan.

#### Checklist Verifikasi

1. Login sebagai sales sebelum jam 08:00:
   - halaman absensi tampil
   - tombol aksi nonaktif
   - request manual ditolak
2. Login sebagai sales pada jam kerja:
   - check-in berfungsi
   - checkout berfungsi sesuai aturan kunjungan aktif
3. Login sebagai sales setelah jam 16:30:
   - tombol aksi nonaktif
   - request manual ditolak
4. Pastikan pesan yang muncul cukup jelas untuk dipahami sales di lapangan.

## Risiko Yang Perlu Diwaspadai

1. Timezone server berbeda dengan timezone yang dipahami user.
2. Logika pembanding waktu yang terlalu longgar, sehingga `16:30` malah dianggap lewat.
3. UI menonaktifkan tombol, tetapi backend belum memblokir request.
4. Test menjadi flaky kalau tidak memakai freeze time.

## Urutan Pengerjaan Yang Disarankan

1. Kerjakan guard waktu di backend terlebih dahulu.
2. Update UI supaya user tidak bingung.
3. Tambahkan test waktu dan jalankan seluruh test absensi.
4. Lakukan verifikasi manual untuk jam sebelum, di dalam, dan sesudah window absensi.

## Definisi Selesai

Implementasi dianggap selesai jika:

- Sales hanya bisa check-in/check-out antara `08:00` dan `16:30`.
- Request di luar jam selalu ditolak oleh backend.
- UI menampilkan informasi jam operasional.
- Test otomatis menutup skenario penting dan tetap hijau.
