<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController
{
    /**
     * Display list of users with DataTables
     */
    public function index(): View
    {
        return view('admin.user.index');
    }

    /**
     * Get users for DataTables AJAX
     */
    public function getUsers(Request $request)
    {
        $query = User::with('roles', 'wilayah');

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        // Filter by wilayah
        if ($request->has('wilayah_id') && $request->wilayah_id) {
            $query->where('wilayah_id', $request->wilayah_id);
        }

        // Filter by aktif
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $recordsTotal = User::count();
        $recordsFiltered = $query->count();

        // Sorting
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'name', 'email', 'phone', 'is_active', 'created_at'];
        
        if ($orderColumn < count($columns)) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $users = $query->offset($start)->limit($length)->get();

        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'wilayah' => $user->wilayah?->nama_wilayah ?? '-',
                'role' => $user->roles->first()?->name ?? '-',
                'is_active' => $user->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>',
                'created_at' => $user->created_at->format('d/m/Y H:i'),
                'actions' => view('admin.user.actions', ['user' => $user])->render(),
            ];
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Show create user form
     */
    public function create(): View
    {
        return view('admin.user.form', [
            'wilayahs' => Wilayah::all(),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Store new user
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'numeric', 'digits_between:10,12'],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'role' => ['required', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'wilayah_id' => $validated['wilayah_id'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $role = Role::findById($validated['role']);
        $user->assignRole($role);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' berhasil dibuat.");
    }

    /**
     * Show edit user form
     */
    public function edit(User $user): View
    {
        return view('admin.user.form', [
            'user' => $user,
            'wilayahs' => Wilayah::all(),
            'roles' => Role::all(),
        ]);
    }

    /**
     * Update user
     */
    public function update(User $user, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', "unique:users,email,{$user->id}"],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'numeric', 'digits_between:10,12'],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'role' => ['required', 'exists:roles,id'],
            'is_active' => ['boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'wilayah_id' => $validated['wilayah_id'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if ($validated['password']) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        $role = Role::findById($validated['role']);
        $user->syncRoles($role);

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Delete user (soft delete)
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' berhasil dihapus.");
    }
}
