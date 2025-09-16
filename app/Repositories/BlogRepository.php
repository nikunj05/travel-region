<?php

namespace App\Repositories;

use App\Interfaces\BlogInterface;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BlogRepository implements BlogInterface
{
    /**
     * Fetches blogs with optional filters for featured status and category.
     *
     * @param  Request  $request  The incoming request object containing filter parameters.
     * @return LengthAwarePaginator Paginated list of blogs.
     */
    public function blogsWithFilters(Request $request): LengthAwarePaginator
    {
        $blogs = Blog::with('category')
            ->where('is_featured', $request->boolean('is_featured', false))
            ->when($request->has('category_id'), function ($query) use ($request) {
                $query->whereIn('category_id', explode(',', $request->input('category_id')));
            })
            ->latest()
            ->paginate();

        return $blogs;
    }
}
