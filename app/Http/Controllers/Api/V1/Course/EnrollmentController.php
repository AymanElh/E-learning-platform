<?php

namespace App\Http\Controllers\Api\V1\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Course\UpdateEnrollmentStatusRequest;
use App\Http\Resources\V1\Course\EnrollmentCollection;
use App\Http\Resources\V1\Course\EnrollmentResource;
use App\Interfaces\Course\EnrollmentRepositoryInterface;
use App\Models\Course;
use App\Models\Enrollment;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    public function index(): JsonResponse
    {
        return $this->successResponse("Enrollments retrieved successfully", $this->enrollmentRepository->getAllEnrollments());
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
                return $this->errorResponse("An error occurred while enrolling.", null, 500);
            }

            return $this->successResponse("Course Enrolled successfully", new EnrollmentResource($enrollment), 201);
        } catch (\Exception $e) {
            \Log::error("Error enrolling this course: ", $e->getMessage());
            return $this->errorResponse("Error enrolling this course", null, 500);
        }
    }

    public function updateStatus(Course $course, int $enrollmentId, UpdateEnrollmentStatusRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->enrollmentRepository->updateStatus($course, $enrollmentId, $data['status']);

            return $this->successResponse("Enrollment status updated successfully");
        } catch (ModelNotFoundException $e) {
            \Log::error("Enrollment not found: " . $e->getMessage());
            return $this->notFoundResponse("Enrollment not found on this course");
        } catch (\Exception $e) {
            \Log::error("Error updating the status of course: " . $e->getMessage());
            return $this->errorResponse("Error updating the status of the course");
        }
    }

    public function getEnrollmentsByCourse(Course $course): JsonResponse
    {
        try {
            $enrollments = $this->enrollmentRepository->getEnrollmentByCourse($course);

            return $this->successResponse("Enrollments got successfully", new EnrollmentCollection($enrollments));
        } catch(\Exception $e) {
            \Log::error("Error getting enrollments: " . $e->getMessage());
            return $this->errorResponse("Error getting enrollments");
        }
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
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->enrollmentRepository->cancelEnroll($id);

        if (!$deleted) {
            return $this->notFoundResponse("Enrollment not found");
        }

        return $this->successResponse("Enroll cancelled successfully");
    }

    /**
     * Get user enrollments
     */
    public function myEnrollments(): JsonResponse
    {
        try {
            $user = auth()->user();
            $enrollments = $this->enrollmentRepository->getEnrollmentByUser($user->id);

            return $this->successResponse("Enrollments getted successfully", new EnrollmentCollection($enrollments));
        } catch(\Exception $e) {
            \Log::error("Error getting enrollments: " . $e->getMessage());
            return $this->errorResponse("Error getting enrollments", null, 500);
        }
    }

    public function isExistEnrollment(int $courseId): JsonResponse
    {
        try {
            $userId = Auth::id();

            $isEnrolled = $this->enrollmentRepository->getEnrollmentStatus($courseId, $userId);

            return $this->successResponse("Enrollment exist for this user", ['isEnrolled' => $isEnrolled]);
        } catch (\Exception $e) {
            \Log::error("Error: " . $e->getMessage());
            return $this->errorResponse("Error");
        }
    }
}
