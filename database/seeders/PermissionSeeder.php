<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\Rbac;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect(Rbac::EVENT_PERMISSIONS)
            ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'));

        Role::findOrCreate(Rbac::ADMIN, 'web')->syncPermissions($permissions);
        Role::findOrCreate(Rbac::SUPER_ADMIN, 'web');
    }
}
