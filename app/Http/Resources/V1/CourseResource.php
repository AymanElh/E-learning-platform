<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'duration' => $this->duration,
            'difficulty' => $this->difficulty,
            'status' => $this->status,
            'price' => $this->price,
            'isFree' => $this->is_free,
            'isPublished' => $this->is_published,
            'isFeatured' => $this->is_featured,
            'thumbnail_url' => $this->thumbnail_url,
            'totalStudents' => $this->total_students,
            'published_At' => $this->published_at,

            // Relationships
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'subcategory' => new CategoryResource($this->whenLoaded('subcategory')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
