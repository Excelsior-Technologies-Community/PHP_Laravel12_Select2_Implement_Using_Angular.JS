<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Color;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
        $query = Product::with(['colors', 'category', 'brand']);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
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

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
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
        $validated = $this->validateProduct($request);

        $product = Product::create([
            'title' => $validated['title'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'slug' => $validated['slug'] ?? Str::slug($validated['title']) . '-' . uniqid(),
            'category_id' => $validated['category_id'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'status' => $validated['status'] ?? 'active',
            'discount' => $validated['discount'] ?? 0,
            'image' => $request->hasFile('image')
                ? $request->file('image')->store('products', 'public')
                : null,
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
        $product = Product::with(['colors', 'category', 'brand'])
            ->findOrFail($id);

        return response()->json($product);
    }

    /**
     * Update Product
     */
    public function update(Request $request, $id)
    {
        $validated = $this->validateProduct($request, $id);

        $product = Product::findOrFail($id);

        $product->update([
            'title' => $validated['title'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'sku' => $validated['sku'] ?? null,
            'slug' => $validated['slug'] ?? $product->slug,
            'category_id' => $validated['category_id'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'status' => $validated['status'] ?? 'active',
            'discount' => $validated['discount'] ?? 0,
        ]);

        if ($request->hasFile('image')) {
            $product->update([
                'image' => $request->file('image')->store('products', 'public'),
            ]);
        }

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
        $product = Product::with(['colors', 'category', 'brand'])
            ->findOrFail($id);

        $duplicate = Product::create([
            'title' => $product->title . ' Copy',
            'price' => $product->price,
            'description' => $product->description,
            'sku' => $product->sku ? $product->sku . '-COPY' : null,
            'slug' => Str::slug($product->title . '-copy-' . uniqid()),
            'category_id' => $product->category_id,
            'brand_id' => $product->brand_id,
            'stock_quantity' => $product->stock_quantity,
            'status' => $product->status,
            'discount' => $product->discount,
            'image' => $product->image,
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
        $query = Product::with(['colors', 'category', 'brand']);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('title', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
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

        foreach (['category_id', 'brand_id', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->get($filter));
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
                'SKU',
                'Price',
                'Final Price',
                'Discount',
                'Stock',
                'Status',
                'Category',
                'Brand',
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
                    $product->sku,
                    $product->price,
                    $product->final_price,
                    $product->discount,
                    $product->stock_quantity,
                    $product->status,
                    optional($product->category)->name,
                    optional($product->brand)->name,
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

        $categoryStatistics = Category::withCount('products')
            ->orderByDesc('products_count')
            ->get(['id', 'name', 'products_count']);

        $stockValue = Product::sum(DB::raw('price * stock_quantity'));
        $lowStockProducts = Product::where('stock_quantity', '<', 5)->count();
        $monthlyProducts = Product::get()
            ->groupBy(fn ($product) => $product->created_at->format('Y-m'))
            ->map(fn ($products, $month) => ['month' => $month, 'total' => $products->count()])
            ->values();

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

            'total_categories' => Category::count(),
            'total_brands' => Brand::count(),
            'stock_value' => $stockValue,
            'low_stock_products' => $lowStockProducts,
            'category_statistics' => $categoryStatistics,
            'monthly_products' => $monthlyProducts,
        ]);
    }

    private function validateProduct(Request $request, $id = null)
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku,' . ($id ?: 'NULL') . ',id'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . ($id ?: 'NULL') . ',id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'color_ids' => ['nullable', 'array'],
            'color_ids.*' => ['exists:colors,id'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:products,id'],
            'status' => ['nullable', 'in:active,inactive'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $changes = array_filter([
            'status' => $validated['status'] ?? null,
            'price' => $validated['price'] ?? null,
        ], fn ($value) => $value !== null);

        if (!$changes) {
            return response()->json(['message' => 'Choose a status or price update.'], 422);
        }

        Product::whereIn('id', $validated['ids'])->update($changes);

        return response()->json(['success' => true, 'message' => count($validated['ids']) . ' product(s) updated.']);
    }

    public function trash()
    {
        return response()->json(Product::onlyTrashed()->with(['colors', 'category', 'brand'])->latest('deleted_at')->get());
    }

    public function restore($id)
    {
        Product::onlyTrashed()->findOrFail($id)->restore();

        return response()->json(['success' => true, 'message' => 'Product restored successfully.']);
    }

    public function import(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);
        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $headers = array_map('strtolower', array_map('trim', fgetcsv($handle)));
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            if (empty($data['title']) || !isset($data['price'])) {
                continue;
            }

            Product::updateOrCreate(
                ['sku' => $data['sku'] ?? null, 'title' => $data['title']],
                [
                    'price' => $data['price'],
                    'description' => $data['description'] ?? null,
                    'slug' => Str::slug($data['slug'] ?? $data['title']) . '-' . uniqid(),
                    'stock_quantity' => $data['stock_quantity'] ?? 0,
                    'status' => in_array($data['status'] ?? 'active', ['active', 'inactive']) ? ($data['status'] ?? 'active') : 'active',
                    'discount' => $data['discount'] ?? 0,
                ]
            );
            $count++;
        }
        fclose($handle);

        return response()->json(['success' => true, 'message' => $count . ' product(s) imported.']);
    }

    public function show($id)
    {
        return response()->json(Product::with(['colors', 'category', 'brand'])->findOrFail($id));
    }
}