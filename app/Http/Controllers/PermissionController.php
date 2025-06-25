<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

/**
 * @group Permission
 *
 * Manajemen Permission (akses fitur).
 */
class PermissionController extends Controller
{
    /**
     * Menampilkan semua permission.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(Permission::all());
    }

    /**
     * Menampilkan detail permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $permission = Permission::findOrFail($id);
        return response()->json($permission);
    }

    /**
     * Menambahkan permission baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Permission created', 'permission' => $permission]);
    }

    /**
     * Memperbarui permission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update([
            'name' => $request->name,
        ]);

        return response()->json(['message' => 'Permission updated', 'permission' => $permission]);
    }

    /**
     * Menghapus permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Permission deleted']);
    }
}
