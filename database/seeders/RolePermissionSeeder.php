<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'supplier.view', 'supplier.create', 'supplier.edit', 'supplier.delete',
            'customer.view', 'customer.create', 'customer.edit', 'customer.delete',
            'item.view', 'item.create', 'item.edit', 'item.delete',
            'item type.view', 'item type.create', 'item type.edit', 'item type.delete',
            'item status.view', 'item status.create', 'item status.edit', 'item status.delete',
            'requirement type.view', 'requirement type.create', 'requirement type.edit', 'requirement type.delete',
            'incoming item.view', 'incoming item.create', 'incoming item.edit', 'incoming item.delete',
            'outbound item.view', 'outbound item.create', 'outbound item.edit', 'outbound item.delete',
            'item request.viewFilterbyUser', 'item request.viewAll', 'item request.create', 'item request.confirm',
            'report.view stock', 'report.export stock', 'report.view incoming item', 'report.export incoming item', 'report.view outbound item', 'report.export outbound item',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
        ];

        // Membuat permissions jika belum ada
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Buat role Admin dan tambahkan semua izin
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());

        // Buat user Admin dan berikan role Admin
        $admin = User::find(1);

        $admin->assignRole($adminRole);    
    }
}
