<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EnrollmentCollection;
use App\Http\Resources\V1\EnrollmentResource;
use App\Interfaces\EnrollmentRepositoryInterface;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Enrollments",
 *     description="API endpoints for course enrollment management"
 * )
 */
class EnrollmentController extends Controller
{
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
    public function enroll(Request $request, int $courseId)
    {
        $userId = auth()->id();
        $enrollment = $this->enrollmentRepository->enroll($userId, $courseId);
        if(!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => "This enrollment is already exist"
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "Course enrolled successfully",
            'data' => new EnrollmentResource($enrollment->load(['user', 'course']))
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/{courseId}/enrollments",
     *     tags={"Enrollments"},
     *     summary="Get enrollments for a course",
     *     description="Retrieve all enrollments for a specific course",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         description="Course ID to get enrollments for",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enrollments retrieved successfully",
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
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="course_id", type="integer", example=1),
     *                         @OA\Property(property="status", type="string", example="in_progress"),
     *                         @OA\Property(property="enrolled_at", type="string", format="datetime"),
     *                         @OA\Property(
     *                             property="user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             @OA\Property(property="email", type="string", example="john@example.com")
     *                         )
     *                     )
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
     *         response=404,
     *         description="Course not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Course not found")
     *         )
     *     )
     * )
     */
    public function getEnrollmentsByCourse(Request $request, int $courseId)
    {
        $enrollments = $this->enrollmentRepository->getEnrollmentByCourse($courseId);
        return response()->json([
            'success' => true,
            'message' => "Courses retrieved successfully",
            'data' => new EnrollmentCollection($enrollments)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/enrollments/{id}/status",
     *     tags={"Enrollments"},
     *     summary="Update enrollment status",
     *     description="Update the status of a specific enrollment (admin/instructor only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Enrollment ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"rejected", "in_progress", "accepted"},
     *                 example="accepted",
     *                 description="New status for the enrollment"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Status updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="status", type="string", example="accepted"),
     *                 @OA\Property(property="enrolled_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime"),
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
     *                     @OA\Property(property="description", type="string", example="Complete Laravel course")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Enrollment doesn't exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enrollment doesn't exist")
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
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="status",
     *                     type="array",
     *                     @OA\Items(type="string", example="The selected status is invalid.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, int $id)
    {
        $data = $request->validate(['status' => ['required', 'in:rejected,in_progress,accepted']]);
        $enrollment = $this->enrollmentRepository->updateStatus($id, $data['status']);

        if(!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => "Enrollment doesn't exist"
            ], 400);
        }

        return response()->json([
           'success' => true,
           'message' => "Status updated successfully",
            'data' => new EnrollmentResource($enrollment->load(['user', 'course']))
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/my-enrollments",
     *     tags={"Enrollments"},
     *     summary="Get current user's enrollments",
     *     description="Retrieve all enrollments for the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User enrollments retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
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
     *                         @OA\Property(
     *                             property="course",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="title", type="string", example="Laravel Development"),
     *                             @OA\Property(property="description", type="string", example="Complete Laravel course"),
     *                             @OA\Property(property="price", type="number", format="float", example=99.99),
     *                             @OA\Property(property="level", type="string", example="intermediate"),
     *                             @OA\Property(property="duration", type="integer", example=120)
     *                         )
     *                     )
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
    public function myEnrollments()
    {
        $enrollments = $this->enrollmentRepository->getEnrollmentByUser(auth()->id());
        return response()->json([
            'success' => true,
            'data' => new EnrollmentCollection($enrollments)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/enrollments/{id}",
     *     tags={"Enrollments"},
     *     summary="Delete an enrollment",
     *     description="Remove an enrollment (unenroll from course)",
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
     *         description="Course deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Course deleted successfully"),
     *             @OA\Property(property="data", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Failed to delete the course",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete the course")
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
     *         description="Enrollment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Enrollment not found")
     *         )
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $deleted = $this->enrollmentRepository->delete($id);
        if(!$deleted) {
            return response()->json([
                'success' => false,
                'message' => "Failed to delete the course"
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Course deleted successfully",
            'data' => $deleted
        ]);
    }
}
