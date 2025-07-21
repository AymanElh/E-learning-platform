<?php

namespace App\Http\Resources\V1\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
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
            'lesson_type' => $this->lesson_type,
            'order_index' => $this->order_index,
            'duration_minutes' => $this->duration_minutes,
            'is_free_preview' => $this->is_free_preview,
            'is_published' => $this->is_published,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'section' => new SectionResource($this->whenLoaded('section')),
            'course' => new CourseResource($this->whenLoaded('section.course')),
        ];
    }
}
