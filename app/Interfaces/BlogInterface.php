<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface BlogInterface
{
    public function blogsWithFilters(Request $request);

    public function storeComment(Request $request, $blog);
}
