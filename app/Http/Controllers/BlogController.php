<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlogResource;
use App\Http\Resources\PaginationResource;
use App\Interfaces\BlogInterface;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected $blogRepository;

    public function __construct(BlogInterface $blogRepository)
    {
        $this->blogRepository = $blogRepository;
    }

    public function index(Request $request)
    {
        $blogs = $this->blogRepository->blogsWithFilters($request);

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
