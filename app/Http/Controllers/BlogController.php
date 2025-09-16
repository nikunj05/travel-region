<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogResource;
use App\Http\Resources\PaginationResource;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $blogs = Blog::with('category')
            ->where('is_featured', $request->boolean('is_featured', false))
            ->latest()
            ->paginate();

        return $this->sendApiResponse(true, __('messages.blog.fetched'), [
            'blogs' => BlogResource::collection($blogs),
            'pagination' => new PaginationResource($blogs)
        ]);
    }

    public function show(Blog $blog)
    {
        return $this->sendApiResponse(true, __('messages.blog.fetched'), [
            'blog' => new BlogResource($blog)
        ]);
    }
}
