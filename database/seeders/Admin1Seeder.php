<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Seeder;
use RuntimeException;

class Admin1Seeder extends Seeder
{
    public function run(): void
    {
        $password = config('admin.password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('Set ADMIN_PASSWORD before running the admin1 seeder.');
        }

        $this->call(PermissionSeeder::class);

        $admin = User::updateOrCreate(
            ['email' => 'admin1@gmail.com'],
            ['name' => 'Admin 1', 'password' => $password],
        );

        $admin->syncRoles(Rbac::ADMIN);
    }
}
