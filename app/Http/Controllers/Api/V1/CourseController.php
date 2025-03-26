<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\CourseCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CourseRequest;
use App\Http\Requests\V1\CourseTagsRequest;
use App\Http\Resources\V1\CourseCollection;
use App\Http\Resources\V1\CourseResource;
use App\Repositories\CourseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    public CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Get all courses (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $courses = new CourseCollection($this->courseRepository->getAll($request->query()));

            return response()->json([
                'success' => true,
                'message' => "Courses retrieved successfully",
                'data' => $courses
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching courses: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Failed to retrieve courses"
            ], 500);
        }
    }

    /**
     * Store a new course.
     */
    public function store(CourseRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $course = $this->courseRepository->store($data);
            event(new CourseCreated($course, Auth::user()));
            return response()->json([
                'success' => true,
                'message' => "Course created successfully",
                'data' => new CourseResource($course)
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error creating course: " . $e->getMessage() . " On this line: " . $e->getLine() . " in this file" . $e->getFile());
            return response()->json([
                'success' => false,
                'message' => "Course creation failed"
            ], 500);
        }
    }

    /**
     * Get a single course by ID.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $course = $this->courseRepository->getById($id);
            if (!$course) {
                return response()->json([
                    'success' => false,
                    'message' => "Course not found"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Course retrieved successfully",
                'data' => new CourseResource($course)
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error fetching course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Failed to retrieve course"
            ], 500);
        }
    }

    /**
     * Update a course.
     */
    public function update(CourseRequest $request, int $id): JsonResponse
    {
        try {
            $updated = $this->courseRepository->update($id, $request->validated());
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => "Course not found or not updated"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Course updated successfully"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error updating course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Course update failed"
            ], 500);
        }
    }

    /**
     * Delete a course.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->courseRepository->delete($id);
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => "Course not found or already deleted"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => "Course deleted successfully"
            ], 200);
        } catch (\Exception $e) {
            Log::error("Error deleting course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Course deletion failed"
            ], 500);
        }
    }

    public function attachTags(CourseTagsRequest $request, $id): JsonResponse
    {
        $course = $this->courseRepository->attachTags($id, $request->validated()['tags']);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tags attached successfully',
            'data' => new CourseResource($course)
        ]);
    }

    public function syncTags(CourseTagsRequest $request, $id) : JsonResponse
    {
        $course = $this->courseRepository->syncTags($id, $request->validated()['tags']);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tags synced successfully',
            'data' => new CourseResource($course)
        ], 200);
    }

    public function detachTags(CourseTagsRequest $request, $id): JsonResponse
    {
        $course = $this->courseRepository->detachTags($id, $request->validated()['tags']);
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tags detached successfully',
            'data' => new CourseResource($course)
        ]);
    }
}
