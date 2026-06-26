<?php

namespace App\Http\Controllers\Admin;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportController
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
    }

    public function salesPerformance(Request $request): BinaryFileResponse
    {
        $validated = $this->validatedFilters($request, [
            'wilayah_id' => ['nullable', 'integer', 'exists:wilayah,id'],
            'format' => ['nullable', 'in:xlsx,pdf'],
        ]);
        $wilayahId = $this->effectiveWilayahId($request, $validated['wilayah_id'] ?? null);

        $path = ($validated['format'] ?? 'xlsx') === 'pdf'
            ? $this->reportService->generateSalesPerformancePdf($validated['start_date'], $validated['end_date'], $wilayahId)
            : $this->reportService->generateSalesPerformanceReport($validated['start_date'], $validated['end_date'], $wilayahId);

        return response()->download($path)->deleteFileAfterSend();
    }

    public function regionalPerformance(Request $request): BinaryFileResponse
    {
        $validated = $this->validatedFilters($request, [
            'format' => ['nullable', 'in:xlsx,pdf'],
        ]);
        $wilayahId = $this->effectiveWilayahId($request);

        $path = ($validated['format'] ?? 'xlsx') === 'pdf'
            ? $this->reportService->generateRegionalPerformancePdf($validated['start_date'], $validated['end_date'], $wilayahId)
            : $this->reportService->generateRegionalPerformanceReport($validated['start_date'], $validated['end_date'], $wilayahId);

        return response()->download($path)->deleteFileAfterSend();
    }

    public function klienAnalysis(Request $request): BinaryFileResponse
    {
        $validated = $this->validatedFilters($request, [
            'search' => ['nullable', 'string', 'max:255'],
            'format' => ['nullable', 'in:xlsx,pdf'],
        ]);
        $wilayahId = $this->effectiveWilayahId($request);
        $search = $validated['search'] ?? null;

        $path = ($validated['format'] ?? 'xlsx') === 'pdf'
            ? $this->reportService->generateKlienAnalysisPdf($validated['start_date'], $validated['end_date'], $wilayahId, $search)
            : $this->reportService->generateKlienAnalysisReport($validated['start_date'], $validated['end_date'], $wilayahId, $search);

        return response()->download($path)->deleteFileAfterSend();
    }

    private function effectiveWilayahId(Request $request, ?int $requestedWilayahId = null): ?int
    {
        $user = $request->user();

        if (!$user?->isManager()) {
            return $requestedWilayahId;
        }

        if (!$user->wilayah_id) {
            return 0;
        }

        if ($requestedWilayahId && (int) $requestedWilayahId !== (int) $user->wilayah_id) {
            abort(403, 'Manager hanya dapat mengakses laporan wilayah sendiri.');
        }

        return (int) $user->wilayah_id;
    }

    private function validatedFilters(Request $request, array $extraRules = []): array
    {
        $validated = $request->validate(array_merge([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ], $extraRules));

        return array_merge([
            'start_date' => Carbon::now()->subDays(30)->toDateString(),
            'end_date' => Carbon::now()->toDateString(),
        ], $validated);
    }
}
