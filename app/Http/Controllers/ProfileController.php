<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    // app/Http/Controllers/ProfileController.php
public function show()
    {
        $user = auth()->user();
        $categories = Category::withCount(['words'])
                        ->orderBy('name')
                        ->get();
        
        return view('profile.show', [
            'user' => $user,
            'categories' => $categories,
            'totalPoints' => $user->points,
            'completedCount' => $user->completedCategories()->count(),
            'totalCategories' => Category::count()
        ]);
    }     
}
