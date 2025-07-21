<?php

namespace App\Http\Controllers\Api\V1\Course;

use App\Http\Requests\V1\Course\SectionRequest;
use App\Http\Resources\V1\Course\CourseResource;
use App\Http\Resources\V1\Course\SectionResource;
use App\Interfaces\Course\SectionRepositoryInterface;
use App\Models\Course;
use App\Repositories\Course\SectionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class SectionController extends Controller
{
    protected SectionRepository $sectionRepository;

    public function __construct(SectionRepositoryInterface $sectionRepository)
    {
        $this->sectionRepository = $sectionRepository;
    }

    public function index(Course $course): JsonResponse
    {
        try {
            $sections = $this->sectionRepository->getByCourse($course->id);

            return response()->json([
                'success' => true,
                'data' => SectionResource::collection($sections)
            ]);
        } catch (\Exception $e) {
            \Log::error("Error getting section for this course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error getting section for this " . $course->title
            ]);
        }
    }

    public function store(SectionRequest $request, Course $course): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['course_id'] = $course->id;
            $section = $this->sectionRepository->store($data);

            return response()->json([
                'success' => true,
                'message' => "Section added to the course successfully",
                'data' => new SectionResource($section)
            ], 201);
        } catch (\Exception $e) {
            \Log::error("Error creating the section for this course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error creating the section..."
            ], 500);
        }
    }

    public function update(SectionRequest $request, Course $course, int $sectionId): JsonResponse
    {
        try {
            $section = $this->sectionRepository->getById($sectionId);

            if (!$section || $section->course_id !== $course->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section not found'
                ], 404);
            }

            $updatedSection = $this->sectionRepository->update($sectionId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully',
                'data' => new SectionResource($updatedSection)
            ]);
        } catch (\Exception $e) {
            \Log::error("Error updating the section: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error updating the section for this course"
            ]);
        }
    }

    public function show(Course $course, int $sectionId): JsonResponse
    {
        $section = $this->sectionRepository->getById($sectionId);

        if (!$section || $section->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SectionResource($section)
        ]);
    }

    public function destroy(Course $course, int $sectionId): JsonResponse
    {
        $section = $this->sectionRepository->getById($sectionId);

        if (!$section || $section->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found'
            ], 404);
        }

        // Check if section has lessons
        if ($section->lessons()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete section with lessons. Please delete lessons first.'
            ], 422);
        }

        $this->sectionRepository->delete($sectionId);

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully'
        ]);
    }
}
