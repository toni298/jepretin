<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');
        $name = config('admin.name');

        if (! is_string($email) || $email === '') {
            throw new RuntimeException('Set ADMIN_EMAIL before running the admin seeder.');
        }

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('Set ADMIN_PASSWORD to create the configured admin account.');
        }

        $this->call(PermissionSeeder::class);

        $admin = User::where('email', $email)->first();

        if ($admin === null) {
            $admin = new User();
            $admin->id = (string) Str::uuid();
        }

        $admin->fill([
            'email' => $email,
            'name' => is_string($name) && $name !== '' ? $name : 'Admin',
            'password' => $password,
        ]);
        $admin->save();

        $admin->syncRoles(Rbac::ADMIN);
    }
}
