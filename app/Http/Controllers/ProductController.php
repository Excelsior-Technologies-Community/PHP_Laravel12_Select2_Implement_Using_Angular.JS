<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Color;

class ProductController extends Controller
{
    /**
     * Product listing with:
     * - Product title search
     * - Minimum price filter
     * - Maximum price filter
     * - Multiple color filtering
     * - Pagination
     */
    public function index(Request $request)
    {
        $query = Product::with('colors');

        /*
        |--------------------------------------------------------------------------
        | Product Title Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(
                'title',
                'like',
                '%' . $search . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Price Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('min_price')) {

            $query->where(
                'price',
                '>=',
                $request->min_price
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Price Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('max_price')) {

            $query->where(
                'price',
                '<=',
                $request->max_price
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Multiple Color Filter
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | color_ids=1,2
        |
        | This returns products having either color 1 OR color 2.
        |
        */

        if ($request->filled('color_ids')) {

            $colorIds = is_array($request->color_ids)
                ? $request->color_ids
                : explode(',', $request->color_ids);

            /*
            |--------------------------------------------------------------------------
            | Clean Color IDs
            |--------------------------------------------------------------------------
            */

            $colorIds = array_filter(
                array_map(
                    'intval',
                    $colorIds
                )
            );

            if (!empty($colorIds)) {

                $query->whereHas(
                    'colors',
                    function ($colorQuery) use ($colorIds) {

                        $colorQuery->whereIn(
                            'colors.id',
                            $colorIds
                        );
                    }
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        return $query
            ->orderBy('id', 'asc')
            ->paginate(3)
            ->appends(
                $request->query()
            );
    }

    /**
     * Store Product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'title' => [
                'required',
                'string',
                'max:255'
            ],

            'price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'color_ids' => [
                'nullable',
                'array'
            ],

            'color_ids.*' => [
                'exists:colors,id'
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Product
        |--------------------------------------------------------------------------
        */

        $product = Product::create([

            'title' => $validated['title'],

            'price' => $validated['price'],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Attach Colors
        |--------------------------------------------------------------------------
        */

        $product->colors()->sync(
            $validated['color_ids'] ?? []
        );

        /*
        |--------------------------------------------------------------------------
        | Return Product
        |--------------------------------------------------------------------------
        */

        return response()->json(
            $product->load('colors'),
            201
        );
    }

    /**
     * Get Product For Editing
     */
    public function edit($id)
    {
        $product = Product::with('colors')
            ->findOrFail($id);

        return response()->json(
            $product
        );
    }

    /**
     * Update Product
     */
    public function update(
        Request $request,
        $id
    ) {
        $validated = $request->validate([

            'title' => [
                'required',
                'string',
                'max:255'
            ],

            'price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'color_ids' => [
                'nullable',
                'array'
            ],

            'color_ids.*' => [
                'exists:colors,id'
            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Find Product
        |--------------------------------------------------------------------------
        */

        $product = Product::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Update Product
        |--------------------------------------------------------------------------
        */

        $product->update([

            'title' => $validated['title'],

            'price' => $validated['price'],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Synchronize Colors
        |--------------------------------------------------------------------------
        */

        $product->colors()->sync(
            $validated['color_ids'] ?? []
        );

        /*
        |--------------------------------------------------------------------------
        | Return Updated Product
        |--------------------------------------------------------------------------
        */

        return response()->json(
            $product->load('colors')
        );
    }

    /**
     * Delete Product
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return response()->json([

            'success' => true,

            'message' =>
                'Product deleted successfully.'

        ]);
    }

    /**
     * Product-Color Analytics
     */
    public function analytics()
    {
        /*
        |--------------------------------------------------------------------------
        | Basic Statistics
        |--------------------------------------------------------------------------
        */

        $totalProducts =
            Product::count();

        $totalColors =
            Color::count();

        $productsWithoutColors =
            Product::doesntHave('colors')->count();

        /*
        |--------------------------------------------------------------------------
        | Price Statistics
        |--------------------------------------------------------------------------
        */

        $averagePrice =
            Product::avg('price');

        $highestPrice =
            Product::max('price');

        $lowestPrice =
            Product::min('price');

        /*
        |--------------------------------------------------------------------------
        | Most Used Color
        |--------------------------------------------------------------------------
        */

        $mostUsedColor =
            Color::withCount('products')
                ->orderByDesc('products_count')
                ->first();

        /*
        |--------------------------------------------------------------------------
        | Color-Wise Product Statistics
        |--------------------------------------------------------------------------
        */

        $colorStatistics =
            Color::withCount('products')
                ->orderByDesc('products_count')
                ->get()
                ->map(function ($color) {

                    return [

                        'id' =>
                            $color->id,

                        'name' =>
                            $color->name,

                        'products_count' =>
                            $color->products_count,

                    ];
                });

        /*
        |--------------------------------------------------------------------------
        | Return Analytics
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'total_products' =>
                $totalProducts,

            'total_colors' =>
                $totalColors,

            'products_without_colors' =>
                $productsWithoutColors,

            'average_price' =>
                round(
                    $averagePrice ?? 0,
                    2
                ),

            'highest_price' =>
                $highestPrice ?? 0,

            'lowest_price' =>
                $lowestPrice ?? 0,

            'most_used_color' =>
                $mostUsedColor
                    ? [

                        'id' =>
                            $mostUsedColor->id,

                        'name' =>
                            $mostUsedColor->name,

                        'products_count' =>
                            $mostUsedColor->products_count,

                    ]
                    : null,

            'color_statistics' =>
                $colorStatistics,

        ]);
    }
}