<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * @group Role
 * 
 * Manajemen Role (Level akses) + assign permission
 */
class RoleController extends Controller
{

    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['message' => 'Role created', 'role' => $role->load('permissions')]);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name'        => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['message' => 'Role updated', 'role' => $role->load('permissions')]);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(['message' => 'Role deleted']);
    }

    public function onlyNames()
    {
        $role = Role::pluck('name');
        return response()->json($role);
    }

}
