<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EnrollmentCollection;
use App\Http\Resources\V1\EnrollmentResource;
use App\Interfaces\EnrollmentRepositoryInterface;
use Illuminate\Http\Request;

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

    public function getEnrollmentsByCourse(Request $request, int $courseId)
    {
        $enrollments = $this->enrollmentRepository->getEnrollmentByCourse($courseId);
        return response()->json([
            'success' => true,
            'message' => "Courses retrieved successfully",
            'data' => new EnrollmentCollection($enrollments)
        ]);
    }

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

    public function myEnrollments()
    {
        $enrollments = $this->enrollmentRepository->getEnrollmentByUser(auth()->id());
        return response()->json([
            'success' => true,
            'data' => new EnrollmentCollection($enrollments)
        ]);
    }

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
