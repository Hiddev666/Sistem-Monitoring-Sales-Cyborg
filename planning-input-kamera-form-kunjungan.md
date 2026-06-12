# Planning Implementasi Input Kamera Pada Form Kunjungan Sales

Tanggal dibuat: 2026-06-11  
Area: form kunjungan sales, upload foto check-in/check-out, validasi upload foto, dan test otomatis.

## Tujuan

Mengubah form kunjungan sales agar foto bukti:

- `check-in`
- `check-out`

harus diambil langsung dari kamera handphone, bukan dipilih dari file yang sudah tersimpan di perangkat.

## Aturan Bisnis Yang Disepakati

1. Di form kunjungan sales, tombol foto check-in dan check-out harus membuka kamera, bukan file picker biasa.
2. User tidak boleh mengunggah foto dari galeri atau storage handphone melalui alur normal UI.
3. Jika user mencoba mengirim file non-kamera lewat request manual, backend tetap harus memvalidasi dan menolak bila tidak sesuai aturan.
4. Alur upload foto yang sudah ada tetap dipakai untuk menyimpan foto ke sistem.
5. Perubahan ini hanya untuk akun sales pada form kunjungan, bukan untuk seluruh fitur foto di aplikasi kecuali memang dipakai ulang.

## Titik Masuk Kode Yang Perlu Diubah

- [resources/views/sales/pjp/visit-form.blade.php](/D:/cyborg/sistem-sales/resources/views/sales/pjp/visit-form.blade.php)
- [app/Http/Controllers/VisitFormController.php](/D:/cyborg/sistem-sales/app/Http/Controllers/VisitFormController.php)
- [app/Services/PhotoService.php](/D:/cyborg/sistem-sales/app/Services/PhotoService.php)
- [tests/Feature/VisitFormSubmitTest.php](/D:/cyborg/sistem-sales/tests/Feature/VisitFormSubmitTest.php)
- Potensi file test baru untuk validasi kamera-only upload, jika ingin dipisah dari test submit form.

## Rencana Implementasi

### Fase 1 - Ubah UI Agar Meminta Kamera

Prioritas: Tinggi  
Tujuan: alur normal di browser mobile langsung mengarah ke kamera.

#### Langkah

1. Ganti tombol upload foto check-in dan check-out di form kunjungan agar tidak membuka file input biasa secara default.
2. Gunakan input file dengan konfigurasi kamera-only, misalnya:
   - `accept="image/*"`
   - `capture="environment"` untuk mendorong kamera belakang
3. Jika diperlukan, ubah teks tombol menjadi lebih eksplisit, misalnya:
   - `Ambil Foto dari Kamera`
   - bukan `Pilih File`
4. Pastikan preview dan alur upload setelah foto diambil tetap sama seperti sekarang.
5. Pada perangkat yang tidak mendukung `capture`, tetap tampilkan pesan bahwa fitur ini ditujukan untuk kamera handphone.

#### Catatan Implementasi

- `accept="image/*"` saja belum cukup karena masih bisa membuka galeri di banyak browser.
- `capture` adalah petunjuk browser, bukan jaminan mutlak, jadi backend tetap harus memeriksa.
- UX harus tetap sederhana agar sales bisa memakai kamera dengan satu langkah.

#### Acceptance Criteria

- Tombol foto check-in dan check-out di form kunjungan tidak lagi memunculkan alur upload file biasa secara default.
- Dari browser mobile yang mendukung, kamera terbuka saat user mengambil foto.
- Alur upload dan preview foto tetap berjalan normal.

### Fase 2 - Tambahkan Guard Backend Untuk File Non-Kamera

Prioritas: Tinggi  
Tujuan: mencegah bypass via direct request atau manipulasi client-side.

#### Langkah

1. Audit endpoint `uploadPhoto()` di `VisitFormController`.
2. Tambahkan validasi tambahan untuk memastikan file yang diterima benar-benar gambar yang valid dan sesuai batas yang ditentukan.
3. Jika memungkinkan secara teknis, tambahkan pemeriksaan metadata/file characteristics yang bisa membedakan upload kamera dengan file biasa, atau minimal pastikan request hanya bisa lewat dari alur form resmi yang disediakan UI.
4. Tolak request yang tidak memenuhi aturan kamera-only dengan pesan error yang jelas.
5. Pastikan file yang disimpan tetap diproses oleh `PhotoService` tanpa mengubah format penyimpanan yang sudah ada.

#### Catatan Implementasi

- Secara teknis, browser tidak selalu memberi penanda yang 100% bisa diverifikasi sebagai “asal kamera”.
- Karena itu, guard backend sebaiknya difokuskan pada pencegahan bypass alur UI dan pembatasan jenis file yang diterima.
- Jika ada kebutuhan keamanan yang lebih ketat, bisa dipertimbangkan penandaan request dari UI resmi melalui token/flag tambahan.

#### Acceptance Criteria

- Request upload manual yang tidak sesuai alur resmi ditolak.
- Backend tidak menerima file yang jelas bukan gambar valid.
- Alur upload kamera dari UI tetap berhasil.

### Fase 3 - Perbaiki Copy Dan Petunjuk Di UI

Prioritas: Medium  
Tujuan: user paham bahwa sistem mengharuskan foto dari kamera.

#### Langkah

1. Ubah teks bantuan di area foto check-in dan check-out.
2. Tambahkan instruksi singkat seperti:
   - `Gunakan kamera handphone untuk mengambil foto bukti.`
3. Jika perlu, tampilkan catatan kecil bahwa galeri tidak digunakan untuk upload bukti kunjungan.
4. Pastikan pesan error dari backend cukup jelas ketika upload gagal.

#### Acceptance Criteria

- User tahu bahwa foto harus diambil lewat kamera.
- Tidak ada kebingungan apakah foto boleh dipilih dari galeri.

### Fase 4 - Tambahkan Dan Sesuaikan Test

Prioritas: Tinggi  
Tujuan: perubahan tidak mudah rusak saat ada refactor berikutnya.

#### Langkah

1. Tambahkan test untuk memastikan endpoint upload menerima file gambar yang valid dari alur yang disediakan.
2. Tambahkan test negatif untuk file yang tidak valid atau request yang tidak sesuai aturan.
3. Jika ada penandaan request resmi dari UI, tambahkan test yang memverifikasi token/flag tersebut ikut dicek.
4. Pastikan test existing terkait submit form dan foto tetap lulus.

#### Catatan Implementasi

- Test browser-level untuk memastikan kamera terbuka bisa jadi sulit di level feature test biasa.
- Karena itu, test terutama harus fokus ke:
  - validasi request
  - format file
  - penolakan bypass
  - keberhasilan upload normal

#### Acceptance Criteria

- Ada test yang menutup skenario upload yang valid.
- Ada test yang menutup skenario upload yang tidak valid.
- Test fitur kunjungan yang sudah ada tetap hijau.

## Risiko Yang Perlu Diwaspadai

1. Dukungan `capture` berbeda antar browser dan perangkat.
2. Browser mobile tetap bisa memberi opsi galeri tergantung implementasi vendor.
3. Backend tidak bisa sepenuhnya membuktikan sumber foto hanya dari metadata standar.
4. Perubahan yang terlalu ketat bisa mengganggu sales jika tidak diuji di perangkat nyata.

## Urutan Pengerjaan Yang Disarankan

1. Ubah UI agar foto mengarah ke kamera.
2. Tambahkan guard backend untuk request yang menyimpang.
3. Perbarui copy/petunjuk di halaman form kunjungan.
4. Tambahkan test dan verifikasi di perangkat mobile.

## Definisi Selesai

Implementasi dianggap selesai jika:

- Form kunjungan sales mendorong penggunaan kamera untuk foto check-in dan check-out.
- Upload foto dari alur normal tidak lagi bergantung pada pemilihan file biasa.
- Request bypass ditolak oleh backend.
- Test penting tetap hijau.

