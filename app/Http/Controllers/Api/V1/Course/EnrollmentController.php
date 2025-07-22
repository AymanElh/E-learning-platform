<?php

namespace App\Http\Controllers\Api\V1\Course;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Course\EnrollmentCollection;
use App\Http\Resources\V1\Course\EnrollmentResource;
use App\Interfaces\Course\EnrollmentRepositoryInterface;
use App\Models\Course;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Enrollments",
 *     description="API endpoints for course enrollment management"
 * )
 */
class EnrollmentController extends Controller
{
    use ApiResponse;

    public EnrollmentRepositoryInterface $enrollmentRepository;

    /**
     * @param EnrollmentRepositoryInterface $enrollmentRepository
     */
    public function __construct(EnrollmentRepositoryInterface $enrollmentRepository)
    {
        $this->enrollmentRepository = $enrollmentRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/courses/{courseId}/enroll",
     *     tags={"Enrollments"},
     *     summary="Enroll in a course",
     *     description="Enroll the authenticated user in a specific course",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         description="Course ID to enroll in",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Course enrolled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course enrolled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="in_progress"),
     *                 @OA\Property(property="enrolled_at", type="string", format="datetime"),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com")
     *                 ),
     *                 @OA\Property(
     *                     property="course",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Laravel Development"),
     *                     @OA\Property(property="description", type="string", example="Complete Laravel course"),
     *                     @OA\Property(property="price", type="number", format="float", example=99.99)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Enrollment already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="This enrollment is already exist")
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
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found")
     *         )
     *     )
     * )
     */
    public function enroll(Course $course): JsonResponse
    {
        try {
            $userId = auth()->id();
            $enrollment = $this->enrollmentRepository->enroll($course, $userId);

            if ($enrollment === false) {
                return $this->errorResponse("User is already enrolled this course", null, 409);
            }

            if ($enrollment === null) {
                return $this->errorResponse("An error occurred while enrolling.", null, 500);c
            }

            return $this->successResponse("Course Enrolled successfully", new EnrollmentResource($enrollment), 201);
        } catch (\Exception $e) {
            \Log::error("Error enrolling this course: ", $e->getMessage());
            return $this->errorResponse("Error enrolling this course", null, 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/enrollments",
     *     tags={"Enrollments"},
     *     summary="Get user enrollments",
     *     description="Retrieve all enrollments for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter enrollments by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"in_progress", "completed", "cancelled"}, example="in_progress")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enrollments retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="course_id", type="integer", example=1),
     *                         @OA\Property(property="status", type="string", example="in_progress"),
     *                         @OA\Property(property="enrolled_at", type="string", format="datetime"),
     *                         @OA\Property(property="completed_at", type="string", format="datetime", nullable=true),
     *                         @OA\Property(
     *                             property="course",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="title", type="string", example="Laravel Development"),
     *                             @OA\Property(property="description", type="string", example="Complete Laravel course")
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="meta",
     *                     type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=3),
     *                     @OA\Property(property="per_page", type="integer", example=15),
     *                     @OA\Property(property="total", type="integer", example=42)
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
     *     )
     * )
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $enrollments = $this->enrollmentRepository->getUserEnrollments($userId, $request->query('status'));

        return response()->json([
            'success' => true,
            'message' => "Enrollments retrieved successfully",
            'data' => new EnrollmentCollection($enrollments)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/enrollments/{id}",
     *     tags={"Enrollments"},
     *     summary="Get enrollment details",
     *     description="Retrieve detailed information about a specific enrollment",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Enrollment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enrollment details retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="in_progress"),
     *                 @OA\Property(property="enrolled_at", type="string", format="datetime"),
     *                 @OA\Property(property="completed_at", type="string", format="datetime", nullable=true),
     *                 @OA\Property(property="progress_percentage", type="integer", example=75),
     *                 @OA\Property(
     *                     property="course",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Laravel Development"),
     *                     @OA\Property(property="description", type="string", example="Complete Laravel course"),
     *                     @OA\Property(property="duration", type="integer", example=120)
     *                 ),
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enrollment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enrollment not found")
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
     *         response=403,
     *         description="Unauthorized to view this enrollment",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized to view this enrollment")
     *         )
     *     )
     * )
     */
    public function show(int $id)
    {
        $enrollment = $this->enrollmentRepository->getById($id);

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => "Enrollment not found"
            ], 404);
        }

        // Check if user owns this enrollment
        if ($enrollment->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => "Unauthorized to view this enrollment"
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => "Enrollment details retrieved successfully",
            'data' => new EnrollmentResource($enrollment)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/enrollments/{id}/complete",
     *     tags={"Enrollments"},
     *     summary="Mark enrollment as completed",
     *     description="Update enrollment status to completed",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Enrollment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment marked as completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enrollment marked as completed"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="completed"),
     *                 @OA\Property(property="completed_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enrollment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enrollment not found")
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
     *         response=403,
     *         description="Unauthorized to update this enrollment",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized to update this enrollment")
     *         )
     *     )
     * )
     */
    public function complete(int $id)
    {
        $enrollment = $this->enrollmentRepository->complete($id, auth()->id());

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => "Enrollment not found or unauthorized"
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "Enrollment marked as completed",
            'data' => new EnrollmentResource($enrollment)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/enrollments/{id}",
     *     tags={"Enrollments"},
     *     summary="Cancel/Unenroll from course",
     *     description="Cancel enrollment or unenroll from a course",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Enrollment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollment cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Enrollment cancelled successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Enrollment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enrollment not found")
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
     *         response=403,
     *         description="Unauthorized to cancel this enrollment",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized to cancel this enrollment")
     *         )
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $deleted = $this->enrollmentRepository->cancel($id, auth()->id());

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => "Enrollment not found or unauthorized"
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "Enrollment cancelled successfully"
        ]);
    }
}
