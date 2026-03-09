<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\ItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount('items')->get();

        return response()->json([
            'message' => 'Daftar kategori berhasil diambil',
            'data' => $categories
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil dibuat',
            'data' => $category
        ], 201);
    }

    /**
     * Display the specified category
     */
    public function show($id): JsonResponse
    {
        $category = Category::with('items')->findOrFail($id);

        return response()->json([
            'message' => 'Detail kategori berhasil diambil',
            'data' => $category
        ]);
    }

    /**
     * Update the specified category
     */
    public function update(UpdateCategoryRequest $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->update($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil diperbarui',
            'data' => $category
        ]);
    }

    /**
     * Remove the specified category
     */
    public function destroy($id): JsonResponse
    {
        $category = Category::findOrFail($id);
        
        // Set category_id to null for all items in this category
        Item::where('category_id', $id)->update(['category_id' => null]);
        
        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus'
        ]);
    }

    /**
     * Get items by category
     */
    public function getItemsByCategory($id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $items = Item::where('category_id', $id)->get();

        return response()->json([
            'message' => 'Daftar item berdasarkan kategori berhasil diambil',
            'category' => $category,
            'data' => ItemResource::collection($items)
        ]);
    }
}
