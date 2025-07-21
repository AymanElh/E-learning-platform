<?php

namespace App\Http\Controllers\Api\V1\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Lesson\LessonRequest;
use App\Http\Resources\V1\Course\LessonResource;
use App\Interfaces\Course\LessonRepositoryInterface;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class LessonController extends Controller
{
    public function __construct(
        private readonly LessonRepositoryInterface $lessonRepository
    )
    {
    }

    /**
     * Get all lessons for a section
     */
    public function index(Course $course, int $sectionId): JsonResponse
    {
        try {
            $section = $course->sections()->findOrFail($sectionId);

            $lessons = $this->lessonRepository->getBySection($section->id);

            return response()->json([
                'success' => true,
                'data' => LessonResource::collection($lessons)
            ]);

        } catch (ModelNotFoundException $e) {
            \Log::error("Section not found for this course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Section doesn\'t exist for this course'
            ], 404);
        } catch (\Exception $e) {
            \Log::error("Error getting lessons for this course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting lessons for this course'
            ], 500);
        }
    }

    /**
     * Store a new lesson for a section
     */
    public function store(LessonRequest $request, Course $course, int $sectionId): JsonResponse
    {
        try {
            $section = $course->sections()->findOrFail($sectionId);

            $data = $request->validated();
            $data['section_id'] = $section->id;

            $lesson = $this->lessonRepository->store($data);

            return response()->json([
                'success' => true,
                'message' => 'Lesson created successfully',
                'data' => new LessonResource($lesson)
            ], 201);
        } catch (ModelNotFoundException $e) {
            \Log::error("Cannot find the section in this course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Cannot find the section on this course"
            ], 404);
        } catch (\Exception $e) {
            \Log::error("Error creating lesson: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error creating lesson"
            ], 500);
        }
    }

    /**
     * Show a specific lesson
     */
    public function show(Course $course, int $sectionId, int $lessonId): JsonResponse
    {
        try {
            $section = $course->sections()->findOrFail($sectionId);
            $lesson = $section->lessons()->findOrFail($lessonId);

            $lesson = $this->lessonRepository->getById($lessonId);


            return response()->json([
                'success' => true,
                'data' => new LessonResource($lesson)
            ]);

        } catch (ModelNotFoundException $e) {
            \Log::error("Lesson or section not found: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lesson or section not found for this course'
            ], 404);
        } catch (\Exception $e) {
            \Log::error("Error getting lesson: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error getting the lesson'
            ], 500);
        }
    }

    /**
     * Update a lesson
     */
    public function update(LessonRequest $request, Course $course, int $sectionId, $lessonId): JsonResponse
    {
        try {
            $lesson = $this->lessonRepository->getLessonBySectionByCourse($course, $sectionId, $lessonId);

            $data = $request->validated();

            $updatedLesson = $this->lessonRepository->update($lesson->id, $data);

            return response()->json([
                'success' => true,
                'message' => 'Lesson updated successfully',
                'data' => new LessonResource($updatedLesson)
            ], 200);

        } catch (ModelNotFoundException $e) {
            \Log::error("Lesson or section not found: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lesson or section not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error("Error updating lesson: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating the lesson'
            ], 500);
        }
    }

    /**
     * Delete a lesson
     */
    public function destroy(Course $course, int $sectionId, int $lessonId): JsonResponse
    {
        try {
            $this->lessonRepository->delete($course, $sectionId, $lessonId);

            return response()->json([
                'success' => true,
                'message' => 'Lesson deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            \Log::error("Lesson or section not found: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lesson or section not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error("Error updating lesson: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting the lesson'
            ], 500);
        }
    }
}
