<?php

namespace App\Http\Resources\V1\Course;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    /**
     * Transform the resource into an array
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'order_index' => $this->order_index,
            'is_published' => $this->is_published,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships
            'course' => new CourseResource($this->whenLoaded('course')),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
            'lessons_count' => $this->lessons_count ?? $this->lessons->count(),
        ];
    }

}
