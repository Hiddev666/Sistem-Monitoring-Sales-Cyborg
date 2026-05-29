<?php

namespace App\Http\Controllers\Admin;

use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConfigurationController
{
    /**
     * Ensure only super admin can manage configuration.
     */
    private function abortIfNotSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403, 'Anda tidak memiliki akses ke konfigurasi sistem.');
    }

    /**
     * Show configuration form
     */
    public function index(): View
    {
        $this->abortIfNotSuperAdmin();

        $gpsRadius = Configuration::getGpsRadiusTolerance();
        $sessionTimeout = Configuration::getValue('session_timeout_minutes', 120);
        $exportFormat = Configuration::getValue('export_format', 'pdf');

        return view('admin.configuration.index', [
            'gpsRadius' => $gpsRadius,
            'sessionTimeout' => $sessionTimeout,
            'exportFormat' => $exportFormat,
        ]);
    }

    /**
     * Update configuration
     */
    public function update(Request $request): RedirectResponse
    {
        $this->abortIfNotSuperAdmin();

        $validated = $request->validate([
            'gps_radius_tolerance' => ['required', 'integer', 'min:10', 'max:1000'],
            'session_timeout_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'export_format' => ['required', 'in:pdf,excel,csv'],
        ], [
            'gps_radius_tolerance.required' => 'GPS radius tolerance harus diisi.',
            'gps_radius_tolerance.between' => 'GPS radius harus antara 10-1000 meter.',
            'session_timeout_minutes.required' => 'Session timeout harus diisi.',
            'session_timeout_minutes.between' => 'Session timeout harus antara 15-480 menit.',
            'export_format.required' => 'Format export harus diisi.',
            'export_format.in' => 'Format export tidak valid.',
        ]);

        Configuration::setValue(
            Configuration::GPS_RADIUS_TOLERANCE_KEY,
            $validated['gps_radius_tolerance'],
            'integer',
            'Toleransi radius GPS untuk validasi check-in (dalam meter)'
        );
        Configuration::setValue('session_timeout_minutes', $validated['session_timeout_minutes'], 'integer', 'Timeout sesi user (dalam menit)');
        Configuration::setValue('export_format', $validated['export_format'], 'string', 'Format ekspor laporan default');

        return redirect()->route('admin.configuration.index')
            ->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }

    /**
     * Reset configuration to default
     */
    public function reset(): RedirectResponse
    {
        $this->abortIfNotSuperAdmin();

        Configuration::setValue(
            Configuration::GPS_RADIUS_TOLERANCE_KEY,
            Configuration::DEFAULT_GPS_RADIUS_TOLERANCE,
            'integer',
            'Toleransi radius GPS untuk validasi check-in (dalam meter)'
        );
        Configuration::setValue('session_timeout_minutes', 120, 'integer', 'Timeout sesi user (dalam menit)');
        Configuration::setValue('export_format', 'pdf', 'string', 'Format ekspor laporan default');

        return redirect()->route('admin.configuration.index')
            ->with('success', 'Konfigurasi sistem berhasil di-reset ke nilai default.');
    }
}
