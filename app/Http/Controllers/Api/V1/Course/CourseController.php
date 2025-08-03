<?php

namespace App\Http\Controllers\Api\V1\Course;

use App\Http\Controllers\Api\V1\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Content\CourseTagsRequest;
use App\Http\Requests\V1\Course\CourseRequest;
use App\Http\Resources\V1\Course\CourseCollection;
use App\Http\Resources\V1\Course\CourseResource;
use App\Repositories\Course\CourseRepository;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Courses",
 *     description="API endpoints for course management"
 * )
 */
class CourseController extends Controller
{
    use ApiResponse;
    public CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses",
     *     tags={"Courses"},
     *     summary="Get all courses",
     *     description="Retrieve a paginated list of all courses",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Courses retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Courses retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Laravel Development"),
     *                         @OA\Property(property="description", type="string", example="Complete Laravel course"),
     *                         @OA\Property(property="price", type="number", format="float", example=99.99),
     *                         @OA\Property(property="category_id", type="integer", example=1),
     *                         @OA\Property(property="created_at", type="string", format="datetime"),
     *                         @OA\Property(property="updated_at", type="string", format="datetime")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="meta",
     *                     type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=5),
     *                     @OA\Property(property="per_page", type="integer", example=15),
     *                     @OA\Property(property="total", type="integer", example=75)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve courses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve courses")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $courses = new CourseCollection($this->courseRepository->getAll());

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
     * @OA\Post(
     *     path="/api/v1/courses",
     *     tags={"Courses"},
     *     summary="Create a new course",
     *     description="Store a new course in the database",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description","category_id"},
     *             @OA\Property(property="title", type="string", example="Advanced React Development"),
     *             @OA\Property(property="description", type="string", example="Learn advanced React concepts and patterns"),
     *             @OA\Property(property="price", type="number", format="float", example=149.99),
     *             @OA\Property(property="category_id", type="integer", example=2),
     *             @OA\Property(property="duration", type="integer", example=120, description="Duration in minutes"),
     *             @OA\Property(property="level", type="string", enum={"beginner", "intermediate", "advanced"}, example="advanced"),
     *             @OA\Property(property="thumbnail", type="string", example="https://example.com/thumbnail.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Advanced React Development"),
     *                 @OA\Property(property="description", type="string", example="Learn advanced React concepts and patterns"),
     *                 @OA\Property(property="price", type="number", format="float", example=149.99),
     *                 @OA\Property(property="category_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Course creation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course creation failed")
     *         )
     *     )
     * )
     */
    public function store(CourseRequest $request): JsonResponse
    {

        try {
            $data = $request->validated();
            $course = $this->courseRepository->store($data);
            return response()->json([
                'success' => true,
                'message' => "Course created successfully",
                'data' => new CourseResource($course)
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error creating course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Course creation failed"
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/{id}",
     *     tags={"Courses"},
     *     summary="Get a specific course",
     *     description="Retrieve a single course by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Laravel Development"),
     *                 @OA\Property(property="description", type="string", example="Complete Laravel course"),
     *                 @OA\Property(property="price", type="number", format="float", example=99.99),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="duration", type="integer", example=120),
     *                 @OA\Property(property="level", type="string", example="intermediate"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime"),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="PHP")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve course",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve course")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/api/v1/courses/{id}",
     *     tags={"Courses"},
     *     summary="Update a course",
     *     description="Update an existing course by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Laravel Development"),
     *             @OA\Property(property="description", type="string", example="Updated complete Laravel course"),
     *             @OA\Property(property="price", type="number", format="float", example=119.99),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="duration", type="integer", example=150),
     *             @OA\Property(property="level", type="string", enum={"beginner", "intermediate", "advanced"}, example="intermediate"),
     *             @OA\Property(property="thumbnail", type="string", example="https://example.com/new-thumbnail.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Updated Laravel Development"),
     *                 @OA\Property(property="description", type="string", example="Updated complete Laravel course"),
     *                 @OA\Property(property="price", type="number", format="float", example=119.99),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Course update failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course update failed")
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/v1/courses/{id}",
     *     tags={"Courses"},
     *     summary="Delete a course",
     *     description="Delete an existing course by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Course deletion failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course deletion failed")
     *         )
     *     )
     * )
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

    /**
     * @OA\Post(
     *     path="/api/v1/courses/{id}/attach-tags",
     *     tags={"Courses"},
     *     summary="Attach tags to a course",
     *     description="Add additional tags to an existing course without removing current tags",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tags"},
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 2, 3},
     *                 description="Array of tag IDs to attach to the course"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tags attached successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tags attached successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Laravel Development"),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="PHP")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/v1/courses/{id}/sync-tags",
     *     tags={"Courses"},
     *     summary="Sync tags with a course",
     *     description="Replace all current course tags with the provided tags",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tags"},
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={2, 4, 5},
     *                 description="Array of tag IDs to sync with the course (replaces all existing tags)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tags synced successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tags synced successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Laravel Development"),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="JavaScript")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/v1/courses/{id}/detach-tags",
     *     tags={"Courses"},
     *     summary="Detach tags from a course",
     *     description="Remove specific tags from a course without affecting other tags",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tags"},
     *             @OA\Property(
     *                 property="tags",
     *                 type="array",
     *                 @OA\Items(type="integer"),
     *                 example={1, 3},
     *                 description="Array of tag IDs to detach from the course"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tags detached successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tags detached successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Laravel Development"),
     *                 @OA\Property(
     *                     property="tags",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="JavaScript")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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

    public function getOpenCourses(): JsonResponse
    {
        try {
            $courses = $this->courseRepository->getOpenCourses();
            return $this->successResponse("Open courses got successfully", $courses);
        } catch (\Exception $e) {
            \Log::error("Error got courses: " . $e->getMessage());
            return $this->errorResponse("Error got open courses", null, 500);
        }
    }
}
