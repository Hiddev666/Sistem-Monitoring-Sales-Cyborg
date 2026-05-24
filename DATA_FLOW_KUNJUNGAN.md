# Data Flow Kunjungan Aktual

Tanggal: 2026-05-22

Dokumen ini mencatat keputusan desain data kunjungan aktual pada codebase. PRD awal menyebut tabel `kunjungan` dan `visit_form`, tetapi implementasi aplikasi saat ini tidak memakai dua tabel tersebut.

## Keputusan Desain

Untuk stabilisasi codebase, aplikasi mempertahankan `jadwal_klien` sebagai record kunjungan aktual.

Artinya:

- `jadwal_kunjungan` adalah header jadwal harian sales.
- `jadwal_klien` adalah detail klien yang harus dikunjungi sekaligus record kunjungan aktual.
- Data check-in, check-out, GPS, foto, tanda tangan, hasil, catatan, nominal transaksi, dan status form disimpan di `jadwal_klien`.
- Tidak ada model/tabel `Kunjungan`.
- Tidak ada model/tabel `VisitForm`.

## Mapping Data

| Konsep Domain | Implementasi Aktual |
| --- | --- |
| Jadwal harian sales | `jadwal_kunjungan` / `App\Models\JadwalKunjungan` |
| Daftar klien pada jadwal | `jadwal_klien` / `App\Models\JadwalKlien` |
| Record kunjungan klien | `jadwal_klien` |
| Check-in GPS | `jadwal_klien.lat_checkin`, `jadwal_klien.lng_checkin`, `jadwal_klien.accuracy_checkin`, `jadwal_klien.waktu_checkin` |
| Check-out GPS | `jadwal_klien.lat_checkout`, `jadwal_klien.lng_checkout`, `jadwal_klien.accuracy_checkout`, `jadwal_klien.waktu_checkout` |
| Foto check-in | `jadwal_klien.foto_checkin` |
| Foto check-out | `jadwal_klien.foto_checkout` |
| Tanda tangan | `jadwal_klien.tanda_tangan` |
| Visit form | Kolom form pada `jadwal_klien` |
| Hasil dan catatan | `jadwal_klien.hasil_tipe`, `jadwal_klien.hasil_kunjungan`, `jadwal_klien.catatan_kunjungan`, `jadwal_klien.keterangan` |
| Nominal transaksi | `jadwal_klien.nominal_transaksi` |
| Form selesai | `jadwal_klien.waktu_form_selesai` |

## Mapping Status

Status sengaja belum diseragamkan bahasanya karena mengikuti schema dan UI yang sudah berjalan:

| Model | Kolom | Nilai | Makna |
| --- | --- | --- | --- |
| `JadwalKunjungan` | `status` | `pending` | Jadwal harian belum dimulai |
| `JadwalKunjungan` | `status` | `aktif` | Perjalanan sales sedang berjalan |
| `JadwalKunjungan` | `status` | `selesai` | Perjalanan sales sudah selesai |
| `JadwalKlien` | `status` | `pending` | Klien belum dikunjungi |
| `JadwalKlien` | `status` | `active` | Sales sedang mengunjungi klien ini |
| `JadwalKlien` | `status` | `checking_out` | Status antara yang dipakai form checkout bila diperlukan |
| `JadwalKlien` | `status` | `completed` | Kunjungan klien selesai |
| `JadwalKlien` | `status` | `skipped` | Kunjungan klien dilewati |

Gunakan konstanta status di `App\Models\JadwalKunjungan` dan `App\Models\JadwalKlien` untuk kode baru agar perbedaan `aktif` dan `active` tidak tertukar.

## Flow Operasional

1. Admin membuat PJP di `jadwal_kunjungan`.
2. Admin menambahkan klien ke jadwal melalui `jadwal_klien`.
3. Sales membuka jadwal harian dari `SalesPJPController`.
4. Sales check-in ke klien; data check-in ditulis ke `jadwal_klien`.
5. Sales mengisi visit form melalui `VisitFormController`.
6. Foto, tanda tangan, hasil kunjungan, GPS checkout, dan waktu form selesai ditulis ke `jadwal_klien`.
7. Gallery, dashboard, analytics, dan report membaca data kunjungan dari `jadwal_klien`.

## Konsekuensi

Keuntungan:

- Perubahan lebih kecil dan risiko regresi lebih rendah.
- Flow yang sudah ada tetap digunakan.
- Dashboard, gallery, dan report tidak perlu refactor besar.

Tradeoff:

- `JadwalKlien` memegang lebih banyak tanggung jawab daripada desain PRD awal.
- Jika aplikasi berkembang, pemisahan `kunjungan` dan `visit_form` mungkin tetap perlu dilakukan sebagai refactor data layer tersendiri.

## Aturan Implementasi Ke Depan

- Jangan import atau membuat dependency ke `App\Models\Kunjungan` kecuali keputusan desain berubah.
- Jangan membuat route/controller baru yang mengharuskan tabel `visit_form` sebelum ada migration dan model resminya.
- Query laporan dan dashboard harus memakai `jadwal_klien` sebagai sumber data kunjungan.
- Endpoint lokasi realtime saat ini sengaja didefinisikan di `routes/web.php` dengan prefix `/api` karena memakai session auth dari aplikasi web. Jangan pindahkan ke `routes/api.php` tanpa mengganti autentikasi dan test terkait.
- Jika PRD/dokumen UAT harus mengikuti implementasi, perbarui bagian schema agar menyebut `jadwal_klien` sebagai visit record.
