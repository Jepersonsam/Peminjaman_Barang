<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    // Tampilkan semua lokasi
    public function index()
    {
        return response()->json([
            'data' => Location::all()
        ]);
    }

    // Simpan lokasi baru dengan secret_id otomatis
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $location = Location::create([
            'name' => $request->name,
            'secret_id' => Str::uuid(),
        ]);

        return response()->json([
            'message' => 'Lokasi berhasil ditambahkan',
            'data' => $location
        ]);
    }

    // Update lokasi
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $location = Location::findOrFail($id);
        $location->name = $request->name;
        $location->save();

        return response()->json([
            'message' => 'Lokasi berhasil diperbarui',
            'data' => $location
        ]);
    }

    // Validasi secret ID dari frontend
    public function validateSecret(Request $request)
    {
        $secret = $request->query('secret_id');

        $location = Location::where('secret_id', $secret)->first();

        if ($location) {
            return response()->json([
                'valid' => true,
                'location' => $location
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Secret ID tidak valid'
        ], 404);
    }

    // Tampilkan detail 1 lokasi
    public function show($id)
    {
        $location = Location::findOrFail($id);

        return response()->json([
            'data' => $location
        ]);
    }

    // Hapus lokasi
    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return response()->json([
            'message' => 'Lokasi berhasil dihapus'
        ]);
    }
}
