<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'admin',
            'customer'
        ];

        Schema::disableForeignKeyConstraints();

        DB::table('roles')->truncate();

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        Schema::enableForeignKeyConstraints();
    }
}
