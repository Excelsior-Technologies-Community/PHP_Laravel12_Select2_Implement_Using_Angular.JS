<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json(Category::withCount('products')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:categories,name']]);
        $category = Category::create(['name' => $data['name'], 'slug' => Str::slug($data['name']) . '-' . uniqid()]);

        return response()->json(['success' => true, 'message' => 'Category created.', 'category' => $category], 201);
    }

    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Category deleted.']);
    }
}