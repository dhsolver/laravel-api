<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        // app()['cache']->forget('spatie.permission.cache');

        // $superAdmin = Role::create(['name' => 'superadmin']);
        // $admin = Role::create(['name' => 'admin']);
        // $user = Role::create(['name' => 'user']);
        // $client = Role::create(['name' => 'client']);

        // $useCms = Permission::create(['name' => 'use-cms']);
        // $useMobile = Permission::create(['name' => 'use-mobile']);
        // $useAdmin = Permission::create(['name' => 'use-admin']);

        // $superAdmin->syncPermissions(['use-cms', 'use-mobile', 'use-admin']);
        // $admin->syncPermissions(['use-cms', 'use-mobile', 'use-admin']);
        // $user->syncPermissions(['use-mobile']);
        // $client->syncPermissions(['use-cms']);
    }
}
