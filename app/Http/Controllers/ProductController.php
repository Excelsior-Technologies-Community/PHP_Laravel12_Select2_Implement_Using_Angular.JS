<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    // Get products with colors (pagination)
    public function index()
    {
        return Product::with('colors')->paginate(5);
    }

    // Store new product
    public function store(Request $request)
    {
        $product = Product::create($request->only('title','price'));

        // Sync colors
        $product->colors()->sync($request->color_ids ?? []);

        return $product->load('colors');
    }

    // Get product for edit
    public function edit($id)
    {
        return Product::with('colors')->find($id);
    }

    // Update product
    public function update(Request $request,$id)
    {
        $product = Product::find($id);

        $product->update($request->only('title','price'));

        // Update colors
        $product->colors()->sync($request->color_ids ?? []);

        return $product->load('colors');
    }

    // Delete product
    public function destroy($id)
    {
        Product::find($id)->delete();
        return response()->json(true);
    }
}
