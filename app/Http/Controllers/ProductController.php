<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Color;

class ProductController extends Controller
{
    /**
     * Product listing
     *
     * Features:
     * - Search
     * - Minimum price
     * - Maximum price
     * - Multiple color filter
     * - Sorting
     * - Pagination
     */
    public function index(Request $request)
    {
        $query = Product::with('colors');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where('title', 'like', '%' . $search . '%');
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Price
        |--------------------------------------------------------------------------
        */

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Price
        |--------------------------------------------------------------------------
        */

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        /*
        |--------------------------------------------------------------------------
        | Multiple Color Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('color_ids')) {

            $colorIds = is_array($request->color_ids)
                ? $request->color_ids
                : explode(',', $request->color_ids);

            $colorIds = array_values(
                array_filter(
                    array_map('intval', $colorIds)
                )
            );

            if (!empty($colorIds)) {
                $query->whereHas('colors', function ($colorQuery) use ($colorIds) {
                    $colorQuery->whereIn('colors.id', $colorIds);
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'id',
            'title',
            'price',
            'created_at',
        ];

        $sort = $request->get('sort', 'id');

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        $direction = strtolower(
            $request->get('direction', 'desc')
        );

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $query->orderBy($sort, $direction);

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        return response()->json(
            $query
                ->paginate(5)
                ->appends($request->query())
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
                'max:255',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'color_ids' => [
                'nullable',
                'array',
            ],

            'color_ids.*' => [
                'exists:colors,id',
            ],
        ]);

        $product = Product::create([
            'title' => $validated['title'],
            'price' => $validated['price'],
        ]);

        $product->colors()->sync(
            $validated['color_ids'] ?? []
        );

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

        return response()->json($product);
    }

    /**
     * Update Product
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'color_ids' => [
                'nullable',
                'array',
            ],

            'color_ids.*' => [
                'exists:colors,id',
            ],
        ]);

        $product = Product::findOrFail($id);

        $product->update([
            'title' => $validated['title'],
            'price' => $validated['price'],
        ]);

        $product->colors()->sync(
            $validated['color_ids'] ?? []
        );

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

        $product->colors()->detach();

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully.',
        ]);
    }

    /**
     * Duplicate Product
     */
    public function duplicate($id)
    {
        $product = Product::with('colors')
            ->findOrFail($id);

        $duplicate = Product::create([
            'title' => $product->title . ' Copy',
            'price' => $product->price,
        ]);

        $duplicate->colors()->sync(
            $product->colors->pluck('id')->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Product duplicated successfully.',
            'product' => $duplicate->load('colors'),
        ], 201);
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => [
                'required',
                'array',
                'min:1',
            ],

            'ids.*' => [
                'integer',
                'exists:products,id',
            ],
        ]);

        $products = Product::whereIn(
            'id',
            $validated['ids']
        )->get();

        foreach ($products as $product) {
            $product->colors()->detach();
            $product->delete();
        }

        return response()->json([
            'success' => true,
            'message' => count($validated['ids'])
                . ' product(s) deleted successfully.',
        ]);
    }

    /**
     * Export filtered products as CSV
     */
    public function export(Request $request)
    {
        $query = Product::with('colors');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $query->where(
                'title',
                'like',
                '%' . trim($request->search) . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Price Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('min_price')) {
            $query->where(
                'price',
                '>=',
                $request->min_price
            );
        }

        if ($request->filled('max_price')) {
            $query->where(
                'price',
                '<=',
                $request->max_price
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Color Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('color_ids')) {

            $colorIds = is_array($request->color_ids)
                ? $request->color_ids
                : explode(',', $request->color_ids);

            $colorIds = array_values(
                array_filter(
                    array_map('intval', $colorIds)
                )
            );

            if (!empty($colorIds)) {
                $query->whereHas('colors', function ($colorQuery) use ($colorIds) {
                    $colorQuery->whereIn(
                        'colors.id',
                        $colorIds
                    );
                });
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        $allowedSorts = [
            'id',
            'title',
            'price',
            'created_at',
        ];

        $sort = $request->get('sort', 'id');

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }

        $direction = strtolower(
            $request->get('direction', 'desc')
        );

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $products = $query
            ->orderBy($sort, $direction)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CSV Response
        |--------------------------------------------------------------------------
        */

        $fileName = 'products-' . date('Y-m-d-H-i-s') . '.csv';

        return response()->streamDownload(function () use ($products) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID',
                'Title',
                'Price',
                'Colors',
                'Created At',
            ]);

            foreach ($products as $product) {

                $colors = $product->colors
                    ->pluck('name')
                    ->implode(', ');

                fputcsv($handle, [
                    $product->id,
                    $product->title,
                    $product->price,
                    $colors,
                    $product->created_at,
                ]);
            }

            fclose($handle);

        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Product Analytics
     */
    public function analytics()
    {
        $totalProducts = Product::count();

        $totalColors = Color::count();

        $productsWithoutColors =
            Product::doesntHave('colors')->count();

        $averagePrice =
            Product::avg('price');

        $highestPrice =
            Product::max('price');

        $lowestPrice =
            Product::min('price');

        $mostUsedColor =
            Color::withCount('products')
                ->orderByDesc('products_count')
                ->first();

        $colorStatistics =
            Color::withCount('products')
                ->orderByDesc('products_count')
                ->get()
                ->map(function ($color) {

                    return [
                        'id' => $color->id,
                        'name' => $color->name,
                        'products_count' =>
                            $color->products_count,
                    ];
                });

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