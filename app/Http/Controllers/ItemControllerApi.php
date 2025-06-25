<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use Illuminate\Http\JsonResponse;

class ItemControllerApi extends Controller
{
    public function index(): JsonResponse
    {
        $items = Item::all();
        return response()->json([
            'data' => ItemResource::collection($items)
        ]);
    }

    public function publicIndex()
    {
        $items = Item::all();

        return response()->json([
            'message' => 'Daftar barang berhasil diambil',
            'data' => $items
        ]);
    }



    public function store(StoreItemRequest $request)
    {
        $item = Item::create($request->validated());
        return response()->json([
            'message' => 'Item created successfully',
            'data' => new ItemResource($item),
        ], 201);
    }

    public function show($id)
    {
        $item = Item::findOrFail($id);
        return new ItemResource($item);
    }

    public function update(UpdateItemRequest $request, $id)
    {
        $item = Item::findOrFail($id);
        $item->update($request->validated());
        return response()->json([
            'message' => 'Item updated successfully',
            'data' => new ItemResource($item),
        ]);
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Item deleted successfully']);
    }
}
