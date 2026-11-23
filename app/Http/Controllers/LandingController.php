<?php
// app/Http/Controllers/LandingController.php

namespace App\Http\Controllers;

use App\Models\Category;

class LandingController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->take(8)
            ->get();

        return view('landing', compact('categories'));
    }
}