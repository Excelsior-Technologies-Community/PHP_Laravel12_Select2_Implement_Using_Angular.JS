<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Color;

class ColorController extends Controller
{
    // Get all colors
    public function index()
    {
        return Color::all();
    }

    // Store new color
    public function store(Request $request)
    {
        return Color::create($request->only('name'));
    }

    // Delete color
    public function destroy($id)
    {
        Color::find($id)->delete();
        return response()->json(true);
    }
}
