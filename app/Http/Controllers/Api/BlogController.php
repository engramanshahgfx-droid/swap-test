<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;

class BlogController extends Controller
{
    public function index()
    {
        $perPage = request()->query('per_page', 10);

        return response()->json(
            Blog::published()->latest()->paginate($perPage)
        );
    }

    public function show(Blog $blog)
    {
        if (!$blog->is_published) {
            return response()->json(['message' => 'Blog not found'], 404);
        }

        return response()->json($blog);
    }
}
