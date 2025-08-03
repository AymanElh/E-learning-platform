<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\StripePaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    use ApiResponse;

    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'payment_provider' => 'sometimes|string|in:stripe'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Validation fails", $validator->errors(), 422);
        }

        try {
            $course = Course::findOrFail($request->course_id);
            $user = auth()->user();

            $existingEnrollment = Enrollment::where('course_id', $course->id)->where('user_id', $user->id)->first();

            if ($existingEnrollment && $existingEnrollment->status === 'active') {
                return $this->errorResponse("You are already enrolled this course", null, 400);
            }

            if ($course->is_free) {
                return $this->handleFreeEnrollment($course, $user);
            }

            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'currency' => 'USD',
                'status' => 'pending',
                'payment_provider' => $request->payment_provider ?? 'stripe'
            ]);

            $paymentData = [
                'amount' => $course->price,
                'currency' => 'USD',
                'course_id' => $course->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'description' => "Purchase of course: {$course->title}"
            ];

            $paymentResult = $this->paymentService->createPayment($paymentData);

            if (!$paymentResult['success']) {
                DB::rollback();
                return $this->errorResponse("Failed to create payment intent", $paymentResult['error'] ?? "Unknown error", 500);
            }

            $order->update([
                'payment_intent_id' => $paymentResult['payment_intent_id'],
                'payment_status' => $paymentResult['status']
            ]);

            Payment::create([
                'order_id' => $order->id,
                'payment_id' => $paymentResult['payment_intent_id'],
                'provider' => $request->payment_provider ?? 'stripe',
                'amount' => $course->price,
                'currency' => 'USD',
                'status' => $paymentResult['status'],
            ]);


            DB::commit();

            $data = [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'client_secret' => $paymentResult['client_secret'],
                'payment_intent_id' => $paymentResult['payment_intent_id'],
                'amount' => $course->price,
                'currency' => 'USD',
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'price' => $course->price,
                ]
            ];

            return $this->successResponse("Payment intent created successfully", $data);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment intent creation failed: ' . $e->getMessage());

            return $this->errorResponse("Failed to create payment intent", null, 500);
        }
    }

    public function confirmPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse("Validation failed", $validator->errors(), 422);
        }

        try {
            $order = Order::where('payment_intent_id', $request->payment_intent_id)->first();

            if (!$order) {
                return $this->notFoundResponse("Order not found");
            }

            $paymentResult = $this->paymentService->confirmPayment($request->payment_intent_id);

            if (!$paymentResult['success']) {
                return $this->errorResponse("Payment confirmation failed", ['error' => $paymentResult['error'] ?? "Unknown error"]);
            }

            DB::beginTransaction();

            if ($paymentResult['status'] === 'succeeded') {
                $order->update([
                    'status' => 'completed',
                    'payment_status' => 'succeeded',
                    'completed_at' => now()
                ]);


                $payment = Payment::where('order_id', $order->id)->first();
                if ($payment) {
                    $payment->update([
                        'status' => 'succeeded',
                        'processed_at' => now()
                    ]);
                }

                Enrollment::create([
                    'user_id' => $order->user_id,
                    'course_id' => $order->course_id,
                    'order_id' => $order->id,
                    'status' => 'accepted',
                    'enrolled_at' => now()
                ]);

                DB::commit();

                $data = [
                    'order_number' => $order->order_number,
                    'status' => 'completed',
                    'course_id' => $order->course_id
                ];

                return $this->successResponse("Payment confirmed and enrollment completed", $data);
            }

            DB::rollBack();
            return $this->errorResponse("Payment not succeeded", ['status' => $paymentResult['status']]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment confirmation failed: ' . $e->getMessage());
            return $this->errorResponse("Failed to confirm payment", null, 500);
        }
    }

    public function testConfirmationPayment(Request $request): JsonResponse
    {
        if(!app()->environment(['local', 'testing'])) {
            return $this->errorResponse("This endpoint is only available in testing environment", null, 403);
        }

        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
        ]);

        if($validator->fails()) {
            return $this->errorResponse("validation failed", $validator->errors(), 422);
        }

        try {
            $paymentResult = $this->paymentService->confirmPayment($request->payment_intent_id);

            if (!$paymentResult['success']) {
                return $this->errorResponse("Payment test failed", ['error' => $paymentResult['error']]);
            }

            return $this->successResponse("Test payment completed", [
                'status' => $paymentResult['status'],
                'payment_id' => $paymentResult['payment_id']
            ]);
        } catch (\Exception $e) {
            \Log::error('Test payment failed: ' . $e->getMessage());
            return $this->errorResponse("Test payment failed", null, 500);
        }

    }

    public function getPaymentStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::where('payment_intent_id', $request->payment_intent_id)
                ->with(['course', 'payments'])
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $paymentResult = $this->paymentService->getPaymentStatus($request->payment_intent_id);

            $data = [
                'order_number' => $order->order_number,
                'order_status' => $order->status,
                'payment_status' => $paymentResult['status'] ?? $order->payment_status,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'course' => [
                    'title' => $order->course->title,
                    'id' => $order->course->id,
                ]
            ];

            return $this->successResponse("", $data);

        } catch (\Exception $e) {
            \Log::error('Get payment status failed: ' . $e->getMessage());

            return $this->errorResponse("Failed to get payment status", null, 500);
        }
    }

    private function handleFreeEnrollment(Course $course, $user): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Create order for free course
            $order = Order::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'amount' => 0,
                'currency' => 'USD',
                'status' => 'completed',
                'payment_provider' => 'free',
                'completed_at' => now(),
            ]);

            // Create enrollment
            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'order_id' => $order->id,
                'status' => 'active',
            ]);

            DB::commit();

            $data = [
                'order_number' => $order->order_number,
                'status' => 'completed',
                'course_id' => $course->id,
            ];

            return $this->successResponse("Successfully enrolled free course", $data);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Free enrollment failed: ' . $e->getMessage());

            return $this->errorResponse("Failed to enroll course", null, 500);
        }
    }


    public function getUserOrders(): JsonResponse
    {
        try {
            $orders = Order::where('user_id', auth()->id())
                ->with(['course', 'payments'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return $this->successResponse("User orders fetched successfully", $orders);

        } catch (\Exception $e) {
            \Log::error('Get user orders failed: ' . $e->getMessage());
            return $this->errorResponse("Failed to retrieve courses", null, 500);
        }
    }
}
