<?php

namespace App\Http\Controllers\Admin;

use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WilayahController
{
    /**
     * Display list of wilayah
     */
    public function index(): View
    {
        $wilayahs = Wilayah::withCount(['users', 'klien'])->paginate(15);
        return view('admin.wilayah.index', compact('wilayahs'));
    }

    /**
     * Show create wilayah form
     */
    public function create(): View
    {
        return view('admin.wilayah.form');
    }

    /**
     * Store new wilayah
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_wilayah' => ['required', 'string', 'max:100', 'unique:wilayah'],
            'keterangan' => ['nullable', 'string'],
        ]);

        Wilayah::create($validated);

        return redirect()->route('admin.wilayah.index')
            ->with('success', "Wilayah '{$validated['nama_wilayah']}' berhasil dibuat.");
    }

    /**
     * Show edit wilayah form
     */
    public function edit(Wilayah $wilayah): View
    {
        return view('admin.wilayah.form', compact('wilayah'));
    }

    /**
     * Update wilayah
     */
    public function update(Wilayah $wilayah, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_wilayah' => ['required', 'string', 'max:100', "unique:wilayah,nama_wilayah,{$wilayah->id}"],
            'keterangan' => ['nullable', 'string'],
        ]);

        $wilayah->update($validated);

        return redirect()->route('admin.wilayah.index')
            ->with('success', "Wilayah '{$wilayah->nama_wilayah}' berhasil diperbarui.");
    }

    /**
     * Delete wilayah
     */
    public function destroy(Wilayah $wilayah): RedirectResponse
    {
        // Check if wilayah has users or klien
        if ($wilayah->users()->count() > 0 || $wilayah->klien()->count() > 0) {
            return redirect()->route('admin.wilayah.index')
                ->with('error', "Tidak dapat menghapus Wilayah karena masih memiliki data user atau klien.");
        }

        $nama = $wilayah->nama_wilayah;
        $wilayah->delete();

        return redirect()->route('admin.wilayah.index')
            ->with('success', "Wilayah '$nama' berhasil dihapus.");
    }
}
