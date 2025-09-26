<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'content' => $this->content,
            'image' => $this->image,
            'full_image_url' => $this->image ? url(Storage::url($this->image)) : null,
            'read_time' => $this->read_time,
            'is_featured' => $this->is_featured,
            'tags' => $this->tags,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'author' => $this->author,
            'author_info' => $this->author_info,
            'author_image' => $this->author_image,
            'full_author_image_url' => $this->author_image ? url(Storage::url($this->author_image)) : null,
            'category' => new CategoryResource($this->category),
            // 'comments' => BlogCommentResource::collection($this->comments),
        ];
    }
}
