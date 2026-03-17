<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;

class InitialSeeder extends Seeder
{
    public function run(): void
    {
        // 🔥 1. SUPER ADMIN
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@tenggo.com',
            'password' => 'password',
            'is_super_admin' => true,
        ]);

        // 🏢 2. COMPANY (MTrans)
        $company = Company::create([
            'name' => 'MTrans'
        ]);

        // 🎭 3. ROLES
        $adminRole = Role::create([
            'name' => 'admin',
            'company_id' => $company->id
        ]);

        $operasionalRole = Role::create([
            'name' => 'operasional',
            'company_id' => $company->id
        ]);

        // 👤 4. USER ADMIN MTRANS
        $adminUser = User::create([
            'name' => 'Admin MTrans',
            'email' => 'admin@mtrans.com',
            'password' => 'password',
            'company_id' => $company->id,
        ]);

        $adminUser->roles()->attach($adminRole->id);

        // 👤 5. USER OPERASIONAL
        $opsUser = User::create([
            'name' => 'Operasional MTrans',
            'email' => 'ops@mtrans.com',
            'password' => 'password',
            'company_id' => $company->id,
        ]);

        $opsUser->roles()->attach($operasionalRole->id);
    }
}