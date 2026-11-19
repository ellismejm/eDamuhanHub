<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Manages weed species, users, imports, audit logs, and backups.']
        );

        Role::updateOrCreate(
            ['name' => 'Standard User'],
            ['description' => 'Can browse and search the weed species database.']
        );
    }
}
