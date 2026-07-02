# Planning Implementasi Tanda Tangan Manager pada Export PDF

## Ringkasan Kebutuhan

Semua fitur export PDF harus menampilkan bagian tanda tangan manager di bagian bawah dokumen. Format dibuat seperti tanda tangan surat pada umumnya, berisi lokasi/tanggal opsional, jabatan, ruang kosong untuk tanda tangan, dan nama manager. Nama tanda tangan diambil dari `users.name` milik user yang memiliki role `manager`.

## Analisis Codebase

### Titik Export PDF

Export laporan dikelola oleh `App\Http\Controllers\Admin\ReportExportController`.

Route PDF yang tersedia:

- `admin.reports.export-sales-performance`
- `admin.reports.export-regional-performance`
- `admin.reports.export-klien-analysis`
- `manager.reports.export-sales-performance`
- `manager.reports.export-regional-performance`
- `manager.reports.export-klien-analysis`

Semua route tersebut memanggil method di `App\Services\ReportService`:

- `generateSalesPerformancePdf()`
- `generateRegionalPerformancePdf()`
- `generateKlienAnalysisPdf()`

Ketiga method PDF tersebut berakhir pada satu method private:

- `ReportService::writePdf(string $title, string $startDate, string $endDate, array $rows, string $filenamePrefix)`

Template PDF yang dipakai:

- `resources/views/reports/pdf.blade.php`

Karena semua PDF laporan memakai `writePdf()` dan `reports.pdf`, implementasi tanda tangan sebaiknya dipusatkan di `ReportService::writePdf()` dan template `reports.pdf` agar perubahan berlaku konsisten untuk semua fitur export PDF yang ada.

### Struktur Database Terkait User dan Role

Tabel utama:

- `users`
  - `id`
  - `name`
  - `email`
  - `password`
  - `phone`
  - `photo`
  - `wilayah_id`
  - `is_active`
  - `deleted_at`
  - timestamps

Role memakai Spatie Laravel Permission:

- `roles`
  - `id`
  - `name`
  - `guard_name`
  - `description`
  - timestamps

- `model_has_roles`
  - `role_id`
  - `model_type`
  - `model_id`

Seeder role membuat role berikut:

- `admin`
- `manager`
- `sales`

Model `App\Models\User` memakai trait `Spatie\Permission\Traits\HasRoles`, sehingga user manager bisa diambil dengan query:

```php
User::role('manager')
```

Model juga memiliki scope:

```php
User::active()
```

Scope ini memfilter `is_active = true` dan `deleted_at = null`.

## Strategi Implementasi

### 1. Tambahkan Resolver Nama Manager

Tambahkan method private di `App\Services\ReportService`, misalnya:

```php
private function resolveManagerSignatureName(?int $wilayahId = null): ?string
```

Rencana aturan pengambilan manager:

1. Prioritaskan manager aktif sesuai `wilayah_id` laporan jika `$wilayahId` tersedia.
2. Jika tidak ada manager pada wilayah tersebut, fallback ke manager aktif pertama secara global.
3. Jika tidak ada manager aktif, fallback ke manager pertama termasuk yang tidak aktif.
4. Jika tetap tidak ada, kembalikan `null` agar template bisa menampilkan placeholder `Manager`.

Alasan:

- Laporan manager sudah dibatasi oleh `ReportExportController::effectiveWilayahId()`.
- Untuk admin, laporan bisa lintas wilayah atau memakai filter wilayah.
- Fallback mencegah PDF gagal dibuat ketika data manager belum lengkap.

### 2. Kirim Data Tanda Tangan ke Template PDF

Ubah `ReportService::writePdf()` agar menerima atau menghitung nama manager:

```php
$managerName = $this->resolveManagerSignatureName($wilayahId);
```

Karena `writePdf()` saat ini belum menerima `$wilayahId`, ada dua opsi:

- Opsi disarankan: tambahkan parameter opsional `?int $wilayahId = null` ke `writePdf()`, lalu teruskan dari `generateSalesPerformancePdf()`, `generateRegionalPerformancePdf()`, dan `generateKlienAnalysisPdf()`.
- Opsi alternatif: ambil manager global tanpa mempertimbangkan wilayah. Ini lebih sederhana, tetapi kurang akurat untuk laporan per wilayah.

Opsi disarankan lebih sesuai karena struktur aplikasi sudah mengenal wilayah manager.

Data yang dikirim ke Blade:

```php
compact('title', 'startDate', 'endDate', 'rows', 'managerName')
```

### 3. Tambahkan Blok Tanda Tangan di `reports.pdf`

Tambahkan bagian di bawah tabel:

```html
<div class="signature-section">
    <div class="signature-box">
        <div>Mengetahui,</div>
        <div>Manager</div>
        <div class="signature-space"></div>
        <div class="signature-name">{{ $managerName ?? 'Manager' }}</div>
    </div>
</div>
```

Style DomPDF yang aman:

- Gunakan CSS sederhana berbasis `float`, `width`, `text-align`, `margin-top`, dan `height`.
- Hindari flex/grid kompleks karena DomPDF tidak selalu konsisten.
- Beri `page-break-inside: avoid;` pada blok tanda tangan agar tidak terpotong.

Contoh style:

```css
.signature-section {
    margin-top: 36px;
    width: 100%;
    page-break-inside: avoid;
}
.signature-box {
    width: 240px;
    float: right;
    text-align: center;
    font-size: 11px;
}
.signature-space {
    height: 64px;
}
.signature-name {
    font-weight: bold;
    text-decoration: underline;
}
```

### 4. Pastikan Semua PDF Mendapat Tanda Tangan

Update pemanggilan:

- `generateSalesPerformancePdf(..., $wilayahId)` memanggil `writePdf(..., 'laporan_penjualan', $wilayahId)`
- `generateRegionalPerformancePdf(..., $wilayahId)` memanggil `writePdf(..., 'performa_regional', $wilayahId)`
- `generateKlienAnalysisPdf(..., $wilayahId, $search)` memanggil `writePdf(..., 'analisis_klien', $wilayahId)`

Jika nanti ada PDF baru yang juga memakai `writePdf()`, tanda tangan otomatis ikut muncul.

## Rencana Perubahan File

### `app/Services/ReportService.php`

Perubahan:

- Tambahkan method private `resolveManagerSignatureName()`.
- Tambahkan parameter opsional `$wilayahId` pada `writePdf()`.
- Teruskan `$wilayahId` dari tiga method generator PDF.
- Kirim `$managerName` ke view `reports.pdf`.

### `resources/views/reports/pdf.blade.php`

Perubahan:

- Tambahkan CSS untuk blok tanda tangan.
- Tambahkan HTML tanda tangan setelah tabel.
- Gunakan fallback nama jika manager tidak ditemukan.

### Test yang Direkomendasikan

Tambahkan atau update feature test export PDF, idealnya di file baru atau file existing seperti:

- `tests/Feature/AdminReportExportTest.php`
- `tests/Feature/ManagerReportAccessTest.php`

Skenario minimal:

1. Buat user manager dengan role `manager` dan `name = "Nama Manager Test"`.
2. Trigger export PDF sales performance.
3. Pastikan response download berhasil.
4. Opsional: render view `reports.pdf` secara langsung dengan `$managerName` dan assert HTML berisi nama manager.

Catatan: verifikasi isi PDF biner secara langsung bisa lebih rumit. Untuk cakupan cepat, test HTML template atau test service dengan mock/render view lebih stabil.

## Urutan Implementasi

1. Update `ReportService` untuk mengambil nama manager.
2. Update parameter `writePdf()` dan semua pemanggil PDF.
3. Update template `resources/views/reports/pdf.blade.php` untuk menampilkan blok tanda tangan.
4. Jalankan validasi sintaks PHP:

```bash
php -l app/Services/ReportService.php
```

5. Jalankan test export/report yang relevan:

```bash
php artisan test --filter=AdminReportExportTest
php artisan test --filter=ManagerReportAccessTest
```

6. Generate satu PDF manual dari route export dan inspeksi hasil visual.

## Risiko dan Mitigasi

- Risiko: Ada lebih dari satu manager.
  - Mitigasi: prioritaskan manager aktif sesuai wilayah laporan, lalu fallback global.

- Risiko: Tidak ada user role manager.
  - Mitigasi: tampilkan fallback `Manager` agar PDF tetap berhasil dibuat.

- Risiko: DomPDF memotong tanda tangan ke halaman baru atau layout rusak.
  - Mitigasi: gunakan CSS sederhana dan `page-break-inside: avoid`.

- Risiko: Laporan admin lintas wilayah tidak punya satu manager spesifik.
  - Mitigasi: gunakan manager aktif pertama sebagai fallback global. Jika aturan bisnis mengharuskan beberapa tanda tangan per wilayah, perlu requirement tambahan.

## Estimasi Dampak

Perubahan ini bersifat kecil dan terpusat:

- Tidak perlu migrasi database.
- Tidak perlu mengubah route.
- Tidak perlu mengubah controller.
- Semua export PDF existing otomatis terdampak karena memakai template dan writer yang sama.

