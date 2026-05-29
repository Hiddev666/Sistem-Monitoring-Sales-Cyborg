<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController
{
    private const ROLE_LABELS = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin',
        'manager' => 'Manager',
        'sales' => 'Sales',
    ];

    private function actingUser(): User
    {
        $user = request()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    private function canManageRoles(): bool
    {
        return $this->actingUser()->can('manage_roles');
    }

    private function canManageUser(User $user): bool
    {
        return $this->actingUser()->isSuperAdmin() || $user->hasRole('sales') || $user->roles->isEmpty();
    }

    private function getRoleOptions(): Collection
    {
        if ($this->canManageRoles()) {
            return Role::orderBy('name')->get();
        }

        return Role::where('name', 'sales')->get();
    }

    private function getDefaultRoleId(): ?int
    {
        return Role::findByName('sales')?->id;
    }

    /**
     * Display list of users with DataTables
     */
    public function index(): View
    {
        return view('admin.user.index', [
            'currentUserRole' => $this->actingUser()->roles->first()?->name,
        ]);
    }

    /**
     * Get users for DataTables AJAX
     */
    public function getUsers(Request $request)
    {
        $baseQuery = User::with('roles', 'wilayah');

        if (!$request->user()?->isSuperAdmin()) {
            $baseQuery->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', 'sales');
            });
        }

        $query = clone $baseQuery;

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

        $recordsTotal = $baseQuery->count();
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
            $roleName = $user->roles->first()?->name ?? '-';
            $roleLabel = self::ROLE_LABELS[$roleName] ?? ucfirst($roleName);

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'wilayah' => $user->wilayah?->nama_wilayah ?? '-',
                'role' => $roleName !== '-' ? sprintf(
                    '<span class="badge bg-%s">%s</span>',
                    match ($roleName) {
                        'super_admin' => 'danger',
                        'admin' => 'primary',
                        'manager' => 'info',
                        'sales' => 'warning',
                        default => 'secondary',
                    },
                    e($roleLabel)
                ) : '<span class="badge bg-secondary">-</span>',
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
            'roles' => $this->getRoleOptions(),
            'canManageRoles' => $this->canManageRoles(),
            'defaultRoleId' => $this->getDefaultRoleId(),
        ]);
    }

    /**
     * Store new user
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'numeric', 'digits_between:10,12'],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'is_active' => ['boolean'],
        ];

        if ($this->canManageRoles()) {
            $rules['role'] = ['required', 'exists:roles,id'];
        } else {
            $defaultRoleId = $this->getDefaultRoleId();
            $rules['role'] = ['required', Rule::in([$defaultRoleId])];
        }

        $validated = $request->validate($rules);

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
        abort_unless($this->canManageUser($user), 403, 'Anda tidak memiliki akses untuk mengubah user ini.');

        return view('admin.user.form', [
            'user' => $user,
            'wilayahs' => Wilayah::all(),
            'roles' => $this->getRoleOptions(),
            'canManageRoles' => $this->canManageRoles(),
            'defaultRoleId' => $this->getDefaultRoleId(),
        ]);
    }

    /**
     * Update user
     */
    public function update(User $user, Request $request): RedirectResponse
    {
        abort_unless($this->canManageUser($user), 403, 'Anda tidak memiliki akses untuk mengubah user ini.');

        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', "unique:users,email,{$user->id}"],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'numeric', 'digits_between:10,12'],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'is_active' => ['boolean'],
        ];

        if ($this->canManageRoles()) {
            $rules['role'] = ['required', 'exists:roles,id'];
        }

        $validated = $request->validate($rules);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'wilayah_id' => $validated['wilayah_id'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        if ($this->canManageRoles()) {
            $role = Role::findById($validated['role']);
            $user->syncRoles($role);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' berhasil diperbarui.");
    }

    /**
     * Delete user (soft delete)
     */
    public function destroy(User $user): RedirectResponse
    {
        abort_unless($this->canManageUser($user), 403, 'Anda tidak memiliki akses untuk menghapus user ini.');

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' berhasil dihapus.");
    }
}
