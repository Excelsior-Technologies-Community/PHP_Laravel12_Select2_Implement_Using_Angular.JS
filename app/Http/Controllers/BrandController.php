<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        return response()->json(Brand::withCount('products')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', 'unique:brands,name']]);
        $brand = Brand::create(['name' => $data['name'], 'slug' => Str::slug($data['name']) . '-' . uniqid()]);

        return response()->json(['success' => true, 'message' => 'Brand created.', 'brand' => $brand], 201);
    }

    public function destroy($id)
    {
        Brand::findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Brand deleted.']);
    }
}