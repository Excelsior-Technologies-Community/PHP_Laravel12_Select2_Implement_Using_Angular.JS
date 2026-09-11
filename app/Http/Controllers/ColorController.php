<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    /**
     * Get all colors
     *
     * Supports:
     * - Search
     * - Product count
     */
    public function index(Request $request)
    {
        $query = Color::withCount('products');

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(
                'name',
                'like',
                '%' . $search . '%'
            );
        }

        return response()->json(
            $query
                ->orderBy('name')
                ->get()
        );
    }

    /**
     * Store Color
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:colors,name',
            ],
        ]);

        $color = Color::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Color created successfully.',
            'color' => $color,
        ], 201);
    }

    /**
     * Delete Color
     */
    public function destroy($id)
    {
        $color = Color::findOrFail($id);

        $color->products()->detach();

        $color->delete();

        return response()->json([
            'success' => true,
            'message' => 'Color deleted successfully.',
        ]);
    }
}