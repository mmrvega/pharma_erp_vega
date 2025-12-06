<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // This seeder creates two roles: admin and seller.
        // If spatie/laravel-permission is installed, it will attempt to create roles and assign permissions.
        if (!class_exists(\Spatie\Permission\Models\Role::class)) {
            // package not installed; nothing to do
            return;
        }

        $permissionModel = config('permission.models.permission');
        $roleModel = config('permission.models.role');

        $adminRole = $roleModel::firstOrCreate(['name' => 'admin']);
        $sellerRole = $roleModel::firstOrCreate(['name' => 'seller']);

        // Grant all permissions to admin
        $allPermissions = $permissionModel::all();
        if ($allPermissions->count() > 0) {
            $adminRole->syncPermissions($allPermissions);
        }

        // For seller, give limited permissions if they exist
        $perms = [];
        foreach (['create-sale','view-dashboard','view-notifications','sales.index','sales.create','sales.pos'] as $p) {
            $perm = $permissionModel::where('name', $p)->first();
            if ($perm) $perms[] = $perm;
        }
        if (!empty($perms)) {
            $sellerRole->syncPermissions($perms);
        }
    }
}
