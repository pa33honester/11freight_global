<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SetupUsersAndRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin',
            'warehouse_staff',
            'operation_manager',
            'finance_manager',
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }

        $users = [
            ['name' => 'Administrator', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Warehouse Staff', 'email' => 'warehouse@example.com', 'role' => 'warehouse_staff'],
            ['name' => 'Operation Manager', 'email' => 'ops@example.com', 'role' => 'operation_manager'],
            ['name' => 'Finance Manager', 'email' => 'finance@example.com', 'role' => 'finance_manager'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                ]
            );

            // Ensure role assigned
            $user->syncRoles([$u['role']]);
        }
    }
}
