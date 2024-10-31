<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        //$this->authorize('roles.view', Role::class);

        // Ambil semua roles dengan nama permissions terkait
        $roles = Role::with('permissions:name')->get()->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->toArray()
            ];
        });

        // Ambil semua users dengan nama roles terkait
        $users = User::with('roles:name')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray()
            ];
        });

        // Ambil semua permissions dengan hanya 'id' dan 'name'
        $permissions = Permission::all(['id', 'name'])->map(function ($permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
            ];
        });

        return response()->json([
            'roles' => $roles,
            'users' => $users,
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        // Ambil semua permissions dari database
        $permissions = Permission::all();

        // Mengelompokkan permissions berdasarkan modul
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0]; // Menggunakan bagian pertama dari nama permission sebagai modul
        });

        return response()->json([
            'permissions' => $groupedPermissions,
        ]);
    }

    public function store(Request $request)
    {
        // $this->authorize('create', Role::class);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return response()->json(['message' => 'Role created successfully', 'role' => $role]);
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();

        // Mengubah permissions role menjadi array nama permission
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        // Mengelompokkan permissions berdasarkan modul
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0]; // Bagian pertama dari nama sebagai modul
        });

        return response()->json([
            'role' => $role,
            'rolePermissions' => $rolePermissions, // Mengirimkan array nama permissions
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        // $this->authorize('edit', $role);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return response()->json(['message' => 'Role updated successfully', 'role' => $role]);
    }

    public function destroy(Role $role)
    {
        // $this->authorize('destroy', $role);

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function assignRole(Request $request, User $user)
    {
        // Validasi roles yang dikirimkan dari request
        $request->validate([
            'roles' => 'array|required',
            'roles.*' => 'exists:roles,name', // Pastikan setiap role ada di tabel roles
        ]);

        // Update roles pengguna
        $user->syncRoles($request->roles);

        return response()->json(['message' => 'User roles updated successfully', 'user' => $user->load('roles')]);
    }

}
