<?php

namespace App\Services;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ReportService
{
    /**
     * Generate Sales Performance Report
     */
    public function generateSalesPerformanceReport($startDate, $endDate, $wilayahId = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', 'LAPORAN PERFORMA PENJUALAN');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', "Periode: {$startDate} hingga {$endDate}");
        $sheet->mergeCells('A2:H2');

        // Column headers
        $headers = ['No', 'Nama Sales', 'Wilayah', 'Jadwal', 'Kunjungan', 'Selesai', 'Revenue', 'Rata-rata Durasi', 'Conversion Rate'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            $col++;
        }

        $salesReps = User::role('sales')
            ->when($wilayahId !== null, fn ($q) => $q->where('wilayah_id', $wilayahId))
            ->get();

        $row = 5;
        $no = 1;

        foreach ($salesReps as $user) {
            $schedules = JadwalKunjungan::where('user_id', $user->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->count();

            $visits = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->where('jadwal_kunjungan.user_id', $user->id)
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->count();

            $completed = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->where('jadwal_kunjungan.user_id', $user->id)
                ->where('jadwal_klien.status', 'completed')
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->count();

            $revenue = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->where('jadwal_kunjungan.user_id', $user->id)
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->sum('jadwal_klien.nominal_transaksi') ?? 0;

            $avgDuration = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->where('jadwal_kunjungan.user_id', $user->id)
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->avg('jadwal_klien.durasi_kunjungan') ?? 0;

            $conversionRate = $visits > 0 ? ($completed / $visits) * 100 : 0;

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $user->name);
            $sheet->setCellValue('C' . $row, $user->wilayah->nama_wilayah ?? '-');
            $sheet->setCellValue('D' . $row, $schedules);
            $sheet->setCellValue('E' . $row, $visits);
            $sheet->setCellValue('F' . $row, $completed);
            $sheet->setCellValue('G' . $row, $revenue);
            $sheet->getCell('G' . $row)->setDataType('n');
            $sheet->setCellValue('H' . $row, round($avgDuration, 2));
            $sheet->setCellValue('I' . $row, round($conversionRate, 2) . '%');

            $row++;
            $no++;
        }

        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setAutoSize(true);

        $filename = "laporan_penjualan_{$startDate}_to_{$endDate}.xlsx";
        $path = storage_path("exports/{$filename}");

        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }

    /**
     * Generate Klien Analysis Report
     */
    public function generateKlienAnalysisReport($startDate, $endDate, $wilayahId = null, ?string $search = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', 'LAPORAN ANALISIS KLIEN');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', "Periode: {$startDate} hingga {$endDate}");
        $sheet->mergeCells('A2:F2');

        // Column headers
        $headers = ['No', 'Nama Klien', 'Kunjungan', 'Pembelian', 'Revenue', 'Conversion Rate'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            $col++;
        }

        $klien = Klien::query()
            ->when($wilayahId !== null, fn ($q) => $q->where('wilayah_id', $wilayahId))
            ->when($search !== null && trim($search) !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nama_klien', 'like', '%' . trim($search) . '%')
                        ->orWhere('alamat', 'like', '%' . trim($search) . '%');
                });
            })
            ->get();
        $row = 5;
        $no = 1;

        foreach ($klien as $k) {
            $visits = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->where('jadwal_klien.klien_id', $k->id)
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->count();

            $purchases = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->where('jadwal_klien.klien_id', $k->id)
                ->where('jadwal_klien.hasil_tipe', 'pembelian')
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->count();

            $revenue = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->where('jadwal_klien.klien_id', $k->id)
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->sum('jadwal_klien.nominal_transaksi') ?? 0;

            $conversionRate = $visits > 0 ? ($purchases / $visits) * 100 : 0;

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $k->nama_klien);
            $sheet->setCellValue('C' . $row, $visits);
            $sheet->setCellValue('D' . $row, $purchases);
            $sheet->setCellValue('E' . $row, $revenue);
            $sheet->getCell('E' . $row)->setDataType('n');
            $sheet->setCellValue('F' . $row, round($conversionRate, 2) . '%');

            $row++;
            $no++;
        }

        // Auto-size columns
        for ($col = 'A'; $col <= 'F'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "analisis_klien_{$startDate}_to_{$endDate}.xlsx";
        $path = storage_path("exports/{$filename}");

        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }

    /**
     * Generate Regional Performance Report
     */
    public function generateRegionalPerformanceReport($startDate, $endDate, $wilayahId = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', 'LAPORAN PERFORMA REGIONAL');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', "Periode: {$startDate} hingga {$endDate}");
        $sheet->mergeCells('A2:H2');

        // Column headers
        $headers = ['No', 'Wilayah', 'Jumlah Sales', 'Total Kunjungan', 'Pembelian', 'Total Revenue', 'Konversi %', 'Avg Revenue/Rep'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '4', $header);
            $sheet->getStyle($col . '4')->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC000']],
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);
            $col++;
        }

        $wilayah = Wilayah::with('users')
            ->when($wilayahId !== null, fn ($q) => $q->where('id', $wilayahId))
            ->get();
        $row = 5;
        $no = 1;

        foreach ($wilayah as $w) {
            $userIds = $w->users->pluck('id')->toArray();
            
            $visits = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->whereIn('jadwal_kunjungan.user_id', $userIds ?: [null])
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->count();

            $purchases = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->whereIn('jadwal_kunjungan.user_id', $userIds ?: [null])
                ->where('jadwal_klien.hasil_tipe', 'pembelian')
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->count();

            $revenue = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                ->whereIn('jadwal_kunjungan.user_id', $userIds ?: [null])
                ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                ->sum('jadwal_klien.nominal_transaksi') ?? 0;

            $conversionRate = $visits > 0 ? ($purchases / $visits) * 100 : 0;
            $avgRevenuePerRep = $w->users->count() > 0 ? $revenue / $w->users->count() : 0;

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $w->nama_wilayah);
            $sheet->setCellValue('C' . $row, $w->users->count());
            $sheet->setCellValue('D' . $row, $visits);
            $sheet->setCellValue('E' . $row, $purchases);
            $sheet->setCellValue('F' . $row, $revenue);
            $sheet->getCell('F' . $row)->setDataType('n');
            $sheet->setCellValue('G' . $row, round($conversionRate, 2) . '%');
            $sheet->setCellValue('H' . $row, round($avgRevenuePerRep, 0));

            $row++;
            $no++;
        }

        // Auto-size columns
        for ($col = 'A'; $col <= 'H'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "performa_regional_{$startDate}_to_{$endDate}.xlsx";
        $path = storage_path("exports/{$filename}");

        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }

    public function generateSalesPerformancePdf($startDate, $endDate, $wilayahId = null): string
    {
        $rows = User::role('sales')
            ->when($wilayahId !== null, fn ($q) => $q->where('wilayah_id', $wilayahId))
            ->get()
            ->map(function ($user) use ($startDate, $endDate) {
                $visits = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                    ->where('jadwal_kunjungan.user_id', $user->id)
                    ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                    ->count();
                $completed = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                    ->where('jadwal_kunjungan.user_id', $user->id)
                    ->where('jadwal_klien.status', JadwalKlien::STATUS_COMPLETED)
                    ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                    ->count();

                return [
                    'Nama Sales' => $user->name,
                    'Wilayah' => $user->wilayah->nama_wilayah ?? '-',
                    'Jadwal' => JadwalKunjungan::where('user_id', $user->id)->whereBetween('tanggal', [$startDate, $endDate])->count(),
                    'Kunjungan' => $visits,
                    'Selesai' => $completed,
                    'Revenue' => JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                        ->where('jadwal_kunjungan.user_id', $user->id)
                        ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                        ->sum('jadwal_klien.nominal_transaksi') ?? 0,
                    'Konversi' => $visits > 0 ? round(($completed / $visits) * 100, 2) . '%' : '0%',
                ];
            })->all();

        return $this->writePdf('Laporan Performa Penjualan', $startDate, $endDate, $rows, 'laporan_penjualan');
    }

    public function generateRegionalPerformancePdf($startDate, $endDate, $wilayahId = null): string
    {
        $rows = Wilayah::with('users')
            ->when($wilayahId !== null, fn ($q) => $q->where('id', $wilayahId))
            ->get()
            ->map(function ($wilayah) use ($startDate, $endDate) {
                $userIds = $wilayah->users->pluck('id')->toArray() ?: [null];
                $visits = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                    ->whereIn('jadwal_kunjungan.user_id', $userIds)
                    ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                    ->count();
                $purchases = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                    ->whereIn('jadwal_kunjungan.user_id', $userIds)
                    ->where('jadwal_klien.hasil_tipe', 'pembelian')
                    ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                    ->count();

                return [
                    'Wilayah' => $wilayah->nama_wilayah,
                    'Jumlah Sales' => $wilayah->users->count(),
                    'Kunjungan' => $visits,
                    'Pembelian' => $purchases,
                    'Revenue' => JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                        ->whereIn('jadwal_kunjungan.user_id', $userIds)
                        ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                        ->sum('jadwal_klien.nominal_transaksi') ?? 0,
                    'Konversi' => $visits > 0 ? round(($purchases / $visits) * 100, 2) . '%' : '0%',
                ];
            })->all();

        return $this->writePdf('Laporan Performa Regional', $startDate, $endDate, $rows, 'performa_regional');
    }

    public function generateKlienAnalysisPdf($startDate, $endDate, $wilayahId = null, ?string $search = null): string
    {
        $rows = Klien::query()
            ->when($wilayahId !== null, fn ($q) => $q->where('wilayah_id', $wilayahId))
            ->when($search !== null && trim($search) !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('nama_klien', 'like', '%' . trim($search) . '%')
                        ->orWhere('alamat', 'like', '%' . trim($search) . '%');
                });
            })
            ->get()
            ->map(function ($klien) use ($startDate, $endDate) {
                $visits = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                    ->where('jadwal_klien.klien_id', $klien->id)
                    ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                    ->count();
                $purchases = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                    ->where('jadwal_klien.klien_id', $klien->id)
                    ->where('jadwal_klien.hasil_tipe', 'pembelian')
                    ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                    ->count();

                return [
                    'Nama Klien' => $klien->nama_klien,
                    'Kunjungan' => $visits,
                    'Pembelian' => $purchases,
                    'Revenue' => JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                        ->where('jadwal_klien.klien_id', $klien->id)
                        ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                        ->sum('jadwal_klien.nominal_transaksi') ?? 0,
                    'Konversi' => $visits > 0 ? round(($purchases / $visits) * 100, 2) . '%' : '0%',
                ];
            })->all();

        return $this->writePdf('Laporan Analisis Klien', $startDate, $endDate, $rows, 'analisis_klien');
    }

    private function writePdf(string $title, string $startDate, string $endDate, array $rows, string $filenamePrefix): string
    {
        $path = storage_path("exports/{$filenamePrefix}_{$startDate}_to_{$endDate}.pdf");

        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        Pdf::loadView('reports.pdf', compact('title', 'startDate', 'endDate', 'rows'))
            ->setPaper('a4', 'landscape')
            ->save($path);

        return $path;
    }

    /**
     * Generate Visit Details Export
     */
    public function generateVisitDetailsExport($startDate, $endDate, $userId = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title
        $sheet->setCellValue('A1', 'LAPORAN DETAIL KUNJUNGAN');
        $sheet->mergeCells('A1:M1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Column headers
        $headers = ['No', 'Tanggal', 'Sales', 'Klien', 'Check-in', 'Check-out', 'Durasi (menit)', 'Hasil', 'Nominal', 'GPS Checkin', 'GPS Checkout', 'Catatan', 'Form Selesai'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '366092']],
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]
            ]);
            $col++;
        }

        // Query visit data
        $query = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
            ->join('users', 'jadwal_kunjungan.user_id', '=', 'users.id')
            ->join('klien', 'jadwal_klien.klien_id', '=', 'klien.id')
            ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
            ->select([
                'jadwal_kunjungan.tanggal',
                'users.name',
                'klien.nama_klien',
                'jadwal_klien.waktu_checkin',
                'jadwal_klien.waktu_checkout',
                'jadwal_klien.durasi_kunjungan',
                'jadwal_klien.hasil_tipe',
                'jadwal_klien.nominal_transaksi',
                'jadwal_klien.lat_checkin',
                'jadwal_klien.lng_checkin',
                'jadwal_klien.lat_checkout',
                'jadwal_klien.lng_checkout',
                'jadwal_klien.catatan_kunjungan',
                'jadwal_klien.waktu_form_selesai',
            ]);

        if ($userId) {
            $query->where('jadwal_kunjungan.user_id', $userId);
        }

        $visits = $query->orderBy('jadwal_kunjungan.tanggal')->get();

        $row = 4;
        $no = 1;

        foreach ($visits as $visit) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $visit->tanggal);
            $sheet->setCellValue('C' . $row, $visit->name);
            $sheet->setCellValue('D' . $row, $visit->nama_klien);
            $sheet->setCellValue('E' . $row, $visit->waktu_checkin);
            $sheet->setCellValue('F' . $row, $visit->waktu_checkout);
            $sheet->setCellValue('G' . $row, $visit->durasi_kunjungan);
            $sheet->setCellValue('H' . $row, $visit->hasil_tipe ?? '-');
            $sheet->setCellValue('I' . $row, $visit->nominal_transaksi ?? 0);
            $sheet->setCellValue('J' . $row, $visit->lat_checkin . ', ' . $visit->lng_checkin);
            $sheet->setCellValue('K' . $row, $visit->lat_checkout . ', ' . $visit->lng_checkout);
            $sheet->setCellValue('L' . $row, substr($visit->catatan_kunjungan ?? '', 0, 50));
            $sheet->setCellValue('M' . $row, $visit->waktu_form_selesai ?? '-');

            $row++;
            $no++;
        }

        // Auto-size columns
        for ($col = 'A'; $col <= 'M'; $col++) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "detail_kunjungan_{$startDate}_to_{$endDate}.xlsx";
        $path = storage_path("exports/{$filename}");

        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }
}
