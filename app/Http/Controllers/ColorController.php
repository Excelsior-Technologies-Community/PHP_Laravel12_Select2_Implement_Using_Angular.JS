<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    /**
     * Get all colors
     */
    public function index(Request $request)
    {
        $query = Color::withCount('products');

        /*
        |--------------------------------------------------------------------------
        | Color search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = $request->search;

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
     * Store color
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:colors,name',
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
     * Delete color
     */
    public function destroy($id)
    {
        $color = Color::findOrFail($id);

        $color->products()->detach();

        $color->delete();

        return response()->json([
            'success' => true,
            'message' => 'Color deleted successfully.'
        ]);
    }
}