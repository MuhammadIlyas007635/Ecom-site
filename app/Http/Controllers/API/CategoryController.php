<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller; // ✅ Correct import
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $category = new Category();
        $category->title = $request->input('title');
        $category->save();

        return response()->json([
            'message' => 'Category added successfully.',
            'category' => $category
        ], 201);
    }

    public function categories()
    {
        $categories = Category::paginate(10);

        return response()->json([
            'success' => true,
            'data' => $categories
        ], 200);
    }

    public function update(Request $request, $id)
    
    {
        
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($id);
        $category->title = $request->input('title');
        $category->save();

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category
        ], 200);
    }

    public function destroy($id)
{
    
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.'
        ], 200);

    }
}
