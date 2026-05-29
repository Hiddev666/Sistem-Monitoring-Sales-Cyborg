<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Admin Analytics Dashboard
     * GET /admin/analytics/dashboard
     */
    public function adminDashboard(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $wilayahScope = $this->managerWilayahScope();

        $stats = [
            'total_schedules' => $this->scopeSchedules(JadwalKunjungan::query(), $wilayahScope)->whereBetween('tanggal', [$startDate, $endDate])->count(),
            'completed_visits' => $this->scopeVisitDateRange($this->scopeVisits(JadwalKlien::query(), $wilayahScope), $startDate, $endDate)->where('jadwal_klien.status', 'completed')->count(),
            'total_visits' => $this->scopeVisitDateRange($this->scopeVisits(JadwalKlien::query(), $wilayahScope), $startDate, $endDate)->count(),
            'total_revenue' => $this->scopeVisitDateRange($this->scopeVisits(JadwalKlien::query(), $wilayahScope), $startDate, $endDate)->sum('jadwal_klien.nominal_transaksi') ?? 0,
            'avg_visit_duration' => $this->getAverageVisitDuration($startDate, $endDate, $wilayahScope),
            'sales_reps' => User::role('sales')->when($wilayahScope !== null, fn ($q) => $q->where('wilayah_id', $wilayahScope))->count(),
            'total_klien' => Klien::query()->when($wilayahScope !== null, fn ($q) => $q->where('wilayah_id', $wilayahScope))->count(),
        ];

        // Visit results breakdown
        $resultsBreakdown = $this->scopeVisits(JadwalKlien::query(), $wilayahScope)
            ->whereHas('jadwalKunjungan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->where('jadwal_klien.status', 'completed')
            ->groupBy('hasil_tipe')
            ->select('hasil_tipe', DB::raw('COUNT(*) as count'))
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->hasil_tipe => $item->count
            ])
            ->toArray();

        // Top sales reps
        $topSalesReps = $this->getTopSalesReps($startDate, $endDate, 10, $wilayahScope);

        // Daily visits trend
        $dailyTrend = $this->getDailyVisitsTrend($startDate, $endDate, $wilayahScope);

        // Top klien by visits
        $topKlien = $this->getTopKlienByVisits($startDate, $endDate, 10, $wilayahScope);

        // Revenue by sales rep
        $revenueByRep = $this->getRevenueByRep($startDate, $endDate, 10, $wilayahScope);

        return view('admin.analytics.dashboard', compact(
            'stats',
            'resultsBreakdown',
            'topSalesReps',
            'dailyTrend',
            'topKlien',
            'revenueByRep',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Sales Rep Performance Report
     * GET /admin/analytics/sales-performance
     */
    public function salesPerformance(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $wilayahId = $request->get('wilayah_id');
        $wilayahScope = $this->managerWilayahScope();
        if ($wilayahScope !== null) {
            $wilayahId = $wilayahScope;
        }

        $query = User::role('sales')
            ->with(['jadwalKunjungan', 'absensi']);

        if ($wilayahId !== null) {
            $query->where('wilayah_id', $wilayahId);
        }

        $salesReps = $query->get()
            ->map(function ($user) use ($startDate, $endDate) {
                $schedules = $user->jadwalKunjungan()->whereBetween('tanggal', [$startDate, $endDate])->count();
                $visits = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
                    ->where('jadwal_kunjungan.user_id', $user->id)
                    ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
                    ->count();
                
                $completedVisits = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
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
                    ->avg('jadwal_klien.durasi_kunjungan');

                $purchaseRate = $visits > 0 ? round(($completedVisits / $visits) * 100, 2) : 0;

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'wilayah' => $user->wilayah->nama_wilayah ?? '-',
                    'schedules' => $schedules,
                    'visits' => $visits,
                    'completed_visits' => $completedVisits,
                    'revenue' => $revenue,
                    'avg_duration' => round($avgDuration ?? 0, 2),
                    'purchase_rate' => $purchaseRate,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        $wilayah = Wilayah::query()
            ->when($wilayahScope !== null, fn ($q) => $q->where('id', $wilayahScope))
            ->get();

        return view('admin.analytics.sales-performance', compact(
            'salesReps',
            'startDate',
            'endDate',
            'wilayah',
            'wilayahId'
        ));
    }

    /**
     * Klien Analysis Report
     * GET /admin/analytics/klien-analysis
     */
    public function klienAnalysis(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $wilayahScope = $this->managerWilayahScope();

        $klienData = Klien::with('jadwalKlien')
            ->when($wilayahScope !== null, fn ($q) => $q->where('wilayah_id', $wilayahScope))
            ->get()
            ->map(function ($klien) use ($startDate, $endDate) {
                $visits = JadwalKlien::where('klien_id', $klien->id)
                    ->whereHas('jadwalKunjungan', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('tanggal', [$startDate, $endDate]);
                    })
                    ->count();

                $purchases = JadwalKlien::where('klien_id', $klien->id)
                    ->where('hasil_tipe', 'pembelian')
                    ->whereHas('jadwalKunjungan', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('tanggal', [$startDate, $endDate]);
                    })
                    ->count();

                $revenue = JadwalKlien::where('klien_id', $klien->id)
                    ->whereHas('jadwalKunjungan', function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('tanggal', [$startDate, $endDate]);
                    })
                    ->sum('nominal_transaksi') ?? 0;

                return [
                    'klien_id' => $klien->id,
                    'nama_klien' => $klien->nama_klien,
                    'alamat' => $klien->alamat,
                    'visits' => $visits,
                    'purchases' => $purchases,
                    'purchase_rate' => $visits > 0 ? round(($purchases / $visits) * 100, 2) : 0,
                    'revenue' => $revenue,
                    'avg_transaction' => $purchases > 0 ? round($revenue / $purchases, 2) : 0,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        return view('admin.analytics.klien-analysis', compact(
            'klienData',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Regional Performance Report
     * GET /admin/analytics/regional-performance
     */
    public function regionalPerformance(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $wilayahScope = $this->managerWilayahScope();

        $regions = Wilayah::with('users')
            ->when($wilayahScope !== null, fn ($q) => $q->where('id', $wilayahScope))
            ->get()
            ->map(function ($wilayah) use ($startDate, $endDate) {
                $userIds = $wilayah->users->pluck('id')->toArray();

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

                return [
                    'wilayah_id' => $wilayah->id,
                    'nama_wilayah' => $wilayah->nama_wilayah,
                    'sales_count' => $wilayah->users->count(),
                    'visits' => $visits,
                    'purchases' => $purchases,
                    'purchase_rate' => $visits > 0 ? round(($purchases / $visits) * 100, 2) : 0,
                    'revenue' => $revenue,
                    'avg_revenue_per_rep' => $wilayah->users->count() > 0 ? round($revenue / $wilayah->users->count(), 2) : 0,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        return view('admin.analytics.regional-performance', compact(
            'regions',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get average visit duration
     */
    private function getAverageVisitDuration($startDate, $endDate, ?int $wilayahId = null)
    {
        $avg = $this->scopeVisits(JadwalKlien::query(), $wilayahId)
            ->whereHas('jadwalKunjungan', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            })
            ->where('jadwal_klien.status', 'completed')
            ->avg('jadwal_klien.durasi_kunjungan');

        return round($avg ?? 0, 2);
    }

    /**
     * Get top sales reps
     */
    private function getTopSalesReps($startDate, $endDate, $limit = 10, ?int $wilayahId = null)
    {
        return DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', User::class);
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->leftJoin('jadwal_kunjungan', 'users.id', '=', 'jadwal_kunjungan.user_id')
            ->leftJoin('jadwal_klien', 'jadwal_kunjungan.id', '=', 'jadwal_klien.jadwal_kunjungan_id')
            ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
            ->where('roles.name', 'sales')
            ->when($wilayahId !== null, fn ($q) => $q->where('users.wilayah_id', $wilayahId))
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(DISTINCT jadwal_kunjungan.id) as schedules'),
                DB::raw('COUNT(jadwal_klien.id) as visits'),
                DB::raw('SUM(jadwal_klien.nominal_transaksi) as revenue')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get daily visits trend
     */
    private function getDailyVisitsTrend($startDate, $endDate, ?int $wilayahId = null)
    {
        return DB::table('jadwal_klien')
            ->join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
            ->when($wilayahId !== null, function ($query) use ($wilayahId) {
                $query->join('users', 'jadwal_kunjungan.user_id', '=', 'users.id')
                    ->where('users.wilayah_id', $wilayahId);
            })
            ->select(
                DB::raw('DATE(jadwal_kunjungan.tanggal) as date'),
                DB::raw('COUNT(*) as visits'),
                DB::raw('SUM(CASE WHEN jadwal_klien.status = "completed" THEN 1 ELSE 0 END) as completed'),
                DB::raw('SUM(jadwal_klien.nominal_transaksi) as revenue')
            )
            ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(jadwal_kunjungan.tanggal)'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Get top klien by visits
     */
    private function getTopKlienByVisits($startDate, $endDate, $limit = 10, ?int $wilayahId = null)
    {
        return DB::table('jadwal_klien')
            ->join('klien', 'jadwal_klien.klien_id', '=', 'klien.id')
            ->join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
            ->when($wilayahId !== null, fn ($q) => $q->where('klien.wilayah_id', $wilayahId))
            ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
            ->select(
                'klien.id',
                'klien.nama_klien',
                DB::raw('COUNT(*) as visits'),
                DB::raw('SUM(jadwal_klien.nominal_transaksi) as revenue')
            )
            ->groupBy('klien.id', 'klien.nama_klien')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();
    }

    /**
     * Get revenue by sales rep
     */
    private function getRevenueByRep($startDate, $endDate, $limit = 10, ?int $wilayahId = null)
    {
        return DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', User::class);
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->leftJoin('jadwal_kunjungan', 'users.id', '=', 'jadwal_kunjungan.user_id')
            ->leftJoin('jadwal_klien', 'jadwal_kunjungan.id', '=', 'jadwal_klien.jadwal_kunjungan_id')
            ->whereBetween('jadwal_kunjungan.tanggal', [$startDate, $endDate])
            ->where('roles.name', 'sales')
            ->when($wilayahId !== null, fn ($q) => $q->where('users.wilayah_id', $wilayahId))
            ->select(
                'users.name',
                DB::raw('SUM(jadwal_klien.nominal_transaksi) as revenue')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    private function managerWilayahScope(): ?int
    {
        $user = auth()->user();

        if (!$user?->isManager()) {
            return null;
        }

        return (int) ($user->wilayah_id ?? 0);
    }

    private function scopeSchedules($query, ?int $wilayahId)
    {
        return $query->when($wilayahId !== null, function ($query) use ($wilayahId) {
            $query->whereHas('user', fn ($userQuery) => $userQuery->where('wilayah_id', $wilayahId));
        });
    }

    private function scopeVisits($query, ?int $wilayahId)
    {
        return $query->when($wilayahId !== null, function ($query) use ($wilayahId) {
            $query->whereHas('jadwalKunjungan.user', fn ($userQuery) => $userQuery->where('wilayah_id', $wilayahId));
        });
    }

    private function scopeVisitDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereHas('jadwalKunjungan', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        });
    }
}
