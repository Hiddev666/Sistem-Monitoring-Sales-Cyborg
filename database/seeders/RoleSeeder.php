<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = [
            ['name' => 'admin', 'description' => 'Administrator - Data Management'],
            ['name' => 'manager', 'description' => 'Manager - Monitoring & Reporting (Read-Only)'],
            ['name' => 'sales', 'description' => 'Sales - Field Operations'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                ['guard_name' => 'web', 'description' => $roleData['description']]
            );
        }

        // Create permissions
        $permissions = [
            // User Management
            ['name' => 'manage_users', 'description' => 'Create, read, update, delete users'],
            ['name' => 'manage_roles', 'description' => 'Assign roles to users'],
            
            // Master Data
            ['name' => 'manage_klien', 'description' => 'CRUD klien/toko data'],
            ['name' => 'manage_wilayah', 'description' => 'CRUD wilayah/area data'],
            
            // Scheduling
            ['name' => 'create_pjp', 'description' => 'Create PJP (Jadwal Kunjungan)'],
            ['name' => 'create_pjp_self', 'description' => 'Create own PJP (Sales self-service)'],
            ['name' => 'view_pjp', 'description' => 'View PJP'],
            ['name' => 'edit_pjp', 'description' => 'Edit PJP'],
            ['name' => 'delete_pjp', 'description' => 'Delete PJP'],
            
            // Attendance
            ['name' => 'checkin_attendance', 'description' => 'Absensi check-in'],
            ['name' => 'view_attendance', 'description' => 'View attendance records'],
            
            // Kunjungan (Visits)
            ['name' => 'create_kunjungan', 'description' => 'Create visit records'],
            ['name' => 'upload_photo', 'description' => 'Upload visit photos'],
            ['name' => 'view_kunjungan', 'description' => 'View visit records'],
            
            // Dashboard
            ['name' => 'view_dashboard', 'description' => 'View monitoring dashboard'],
            
            // Reports
            ['name' => 'view_reports', 'description' => 'View performance reports'],
            ['name' => 'export_reports', 'description' => 'Export reports to Excel/PDF'],
            
            // Configuration
            ['name' => 'manage_config', 'description' => 'System configuration'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(
                ['name' => $permData['name']],
                ['guard_name' => 'web', 'description' => $permData['description']]
            );
        }

        // Assign permissions to roles
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());
        // $adminRole->givePermissionTo([
        //     'manage_users', 'manage_roles', 'manage_klien', 'manage_wilayah', 'create_pjp', 'edit_pjp', 'delete_pjp',
        //     'view_attendance', 'view_kunjungan', 'view_reports', 'export_reports', 'manage_config'
        // ]);

        $managerRole = Role::findByName('manager');
        $managerRole->givePermissionTo([
            'view_dashboard', 'view_pjp', 'view_kunjungan', 'view_reports', 'export_reports'
        ]);

        $salesRole = Role::findByName('sales');
        $salesRole->givePermissionTo([
            'checkin_attendance', 'create_kunjungan', 'upload_photo', 'view_pjp',
            'create_pjp_self'
        ]);
    }
}
