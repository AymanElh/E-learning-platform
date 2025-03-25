<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\EnrollmentCollection;
use App\Http\Resources\V1\EnrollmentResource;
use App\Interfaces\EnrollmentRepositoryInterface;
use App\Services\PaypalService;
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
//        $this->middleware('auth:api');
    }

    public function enroll(Request $request, int $courseId)
    {
        try {
            // Start database transaction
            \DB::beginTransaction();

            $userId = auth()->id();

            // Check if user is already enrolled
            $existingEnrollment = $this->enrollmentRepository->findEnrollment($userId, $courseId);
            if ($existingEnrollment) {
                return response()->json([
                    'success' => false,
                    'message' => "You have already enrolled in this course."
                ], 400);
            }

            // Get course details
            $course = \App\Models\Course::findOrFail($courseId);
            $requiresPayment = $course->price > 0;

            // Create enrollment record with appropriate status
            $enrollment = \App\Models\Enrollment::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => $requiresPayment ? 'pending' : 'accepted',
                'require_payment' => $requiresPayment
            ]);

            // For courses requiring payment
            if ($requiresPayment) {
                $price = $course->price;
                $currency = $course->currency ?: 'USD';

                // Create payment record
                $payment = \App\Models\Payment::create([
                    'enrollment_id' => $enrollment->id,
                    'user_id' => $userId,
                    'amount' => $price,
                    'currency' => $currency,
                    'status' => 'pending',
                    'payment_method' => 'paypal',
                    'created_at' => now()
                ]);

                try {
                    $paypalService = new PaypalService();

                    $paypalResponse = $paypalService->create($price, $currency);
                    \Log::info("Paypal Response: ", $paypalResponse);
                    if (isset($paypalResponse['id'])) {
                        $payment->update([
                            'transaction_id' => $paypalResponse['id'],
                            'payment_data' => json_encode($paypalResponse)
                        ]);
                    }

                    $approvalUrl = null;
                    foreach ($paypalResponse['links'] as $link) {
                        if ($link['rel'] === 'approve') {
                            $approvalUrl = $link['href'];
                            break;
                        }
                    }

                    if ($approvalUrl) {
                        $separator = (parse_url($approvalUrl, PHP_URL_QUERY)) ? '&' : '?';
                        $approvalUrl .= "{$separator}enrollment_id={$enrollment->id}&payment_id={$payment->id}";

                        // Commit transaction before returning response
                        \DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => "This course requires payment to enroll",
                            'enrollment_id' => $enrollment->id,
                            'payment_id' => $payment->id,
                            'course' => [
                                'id' => $course->id,
                                'title' => $course->title,
                                'price' => $price,
                                'currency' => $currency,
                            ],
                            'payment_url' => $approvalUrl,
                        ]);
                    } else {
                        \Log::error('PayPal approval URL not found', ['response' => $paypalResponse]);
                        \DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Payment setup failed - missing approval URL"
                        ], 500);
                    }
                } catch (\Exception $e) {
                    \Log::error('PayPal payment setup failed: ' . $e->getMessage(), [
                        'enrollment_id' => $enrollment->id,
                        'payment_id' => $payment->id ?? null,
                        'trace' => $e->getTraceAsString()
                    ]);
                    \DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Payment setup failed: " . $e->getMessage()
                    ], 500);
                }
            }

            // For free courses, we're good to go
            \DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Course enrolled successfully",
                'data' => new EnrollmentResource($enrollment->load(['user', 'course']))
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Enrollment error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'course_id' => $courseId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => "Enrollment failed: " . $e->getMessage()
            ], 500);
        }
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
