<?php

namespace Database\Seeders;

use App\Filament\Resources\RoleResource;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Fetch ALL permissions dynamically from the centralized Resource
        // This ensures the Seeder is NEVER missing permissions!
        $permissionGroups = RoleResource::permissionGroups();
        
        $allPermissions = [];
        foreach ($permissionGroups as $groupPerms) {
            $allPermissions = array_merge($allPermissions, $groupPerms);
        }

        // 2. Create the permissions
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ── superadmin: EVERYTHING ─────────────────────────────────────────
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $superadmin->syncPermissions(Permission::all());

        // ── subadmin: Full business ops, no user management ────────────────
        $subadmin = Role::firstOrCreate(['name' => 'subadmin']);
        $subadmin->syncPermissions(array_merge(
            $permissionGroups['Admin Management'],
            $permissionGroups['Work Orders'],
            $permissionGroups['Attendance'],
            $permissionGroups['Clients'],
            $permissionGroups['HR / Employees'],
            $permissionGroups['Contracts'],
            $permissionGroups['Contract Attachments'],
            $permissionGroups['Invoices'],
            $permissionGroups['Compliance Documents'],
            $permissionGroups['Compliance Checklists']
        ));

        // ── hr: HR & Employee modules + specific read-only  ───────────────
        $hr = Role::firstOrCreate(['name' => 'hr']);
        // Note: Because the UI now syncs FULL modules, if an admin edits the HR role 
        // in Filament and leaves the checkboxes checked, it will upgrade them to full access.
        $hr->syncPermissions(array_merge(
            $permissionGroups['HR / Employees'],
            $permissionGroups['Attendance'],
            
            // Legacy Specific read-only access (If needed behind the scenes)
            [
                'view_any_contract', 'view_contract',
                'view_any_invoice',  'view_invoice',
                'view_any_compliance_document',
            ]
        ));

        // ── employee: Self-service only ────────────────────────────────────
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions($permissionGroups['Self-service']);

        $this->command->info('✅ Roles and missing permissions (Modules based) seeded successfully.');
    }
}