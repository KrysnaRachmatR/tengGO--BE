<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class InitialSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@tenggo.com'],
            [
                'name'      => 'Super Admin',
                'password'  => 'password123', // otomatis ke-hash (cast)
                'role'      => User::ROLE_SUPER_ADMIN,
                'is_active' => true,
                'po_id'     => null, // super admin tidak terikat PO
            ]
        );
    }
}