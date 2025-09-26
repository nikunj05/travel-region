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
            ->when($request->has('tags'), function ($query) use ($request) {
                $tags = explode(',', $request->input('tags'));
                $query->where(function ($q) use ($tags) {
                    foreach ($tags as $tag) {
                        $q->orWhere('tags', 'like', '%' . $tag . '%');
                    }
                });
            });

        if ($request->has('sort_by')) {
            $sortBy = $request->input('sort_by');
            $sortOrder = $request->input('sort_order', 'asc');
            if (in_array($sortBy, ['created_at', 'read_time']) && in_array($sortOrder, ['asc', 'desc'])) {
                $blogs = $blogs->orderBy($sortBy, $sortOrder);
            }
        } else {
            $blogs = $blogs->latest();
        }

        $blogs = $blogs->paginate();

        return $blogs;
    }

    /**
     * Store a new comment for a blog post.
     *
     * @param Request $request
     * @param Blog $blog
     * @return Blog
     */
    public function storeComment(Request $request, $blog): Blog
    {
        $blog->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $request->input('comment'),
        ]);

        return $blog;
    }
}
