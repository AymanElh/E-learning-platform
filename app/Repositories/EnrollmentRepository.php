<?php

namespace App\Repositories;

use App\Interfaces\EnrollmentRepositoryInterface;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{

    /**
     * Find enrollment by user and course
     */
    public function findEnrollment(int $userId, int $courseId)
    {
        return Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
    }
    public function enroll(int $userId, int $courseId)
    {
        try {
            $isExist = Enrollment::where('user_id', $userId)->where('course_id', $courseId)->first();

            if($isExist) {
                return false;
            }

            $course = Course::find($courseId);
//            dd($course->price);
            if($course->price > 0) {
                return ['require_payment' => true, 'course' => $course];
            }

//            if(!$paymentCheck) {
//                $course = Course::find($courseId);
//                if($course && !$course->is_free && $course->price > 0) {
//                    \Log::info("Infos about the course: " . $course);
////                    dd($course);
//                    return ['require_payment' => true, 'course' => $course];
//                }
//            }

            return Enrollment::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => "pending",
                'require_payment' => false,
                'payment_completed' => true  // free course
            ]);
        } catch (\Exception $e) {
            \Log::error("Error creating a new enrollment: " . $e->getMessage());
            return null;
        }
    }

    public function getEnrollmentByCourse(int $courseId)
    {
        return Enrollment::where('course_id', $courseId)->with(['user', 'course'])->get();
    }

    public function getEnrollmentByUser(int $userId)
    {
        return Enrollment::where('user_id', $userId)->with(['user', 'course'])->get();
    }

    public function getById(int $id)
    {
        return Enrollment::with(['user', 'course'])->find($id);
    }

    public function updateStatus(int $id, string $status)
    {
        $enrollment = Enrollment::find($id);
        if(!$enrollment) {
            return null;
        }

        $enrollment->update(['status' => $status]);
        return $enrollment->refresh();
    }

    public function delete(int $id)
    {
        $enrollment = Enrollment::find($id);
        if(!$enrollment) {
            return false;
        }
        return $enrollment->delete();
    }

    public function createPaymentEnrollment(int $userId, int $courseId)
    {
        try {
            $isExist = Enrollment::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->where('status', '!=', 'rejected')
                ->first();

            if($isExist && $isExist->payment_completed) {
                return false;
            }

            if($isExist) {
                return $isExist;
            }

            Enrollment::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'pending',
                'require_payment' => true,
                'payment_completed' => false
            ]);
        } catch (\Exception $e) {
            \Log::error("Error creating pending payment: " . $e->getMessage());
            return null;
        }
    }

    public function updatePaymentStatus(int $enrollmentId, bool $completed, array $paymentData = [])
    {
        try {
            $enrollment = Enrollment::find($enrollmentId);
            if (!$enrollment) {
                return null;
            }

            // Update enrollment
            $enrollment->update([
                'payment_completed' => $completed,
                'status' => $completed ? 'accepted' : 'pending_payment'
            ]);

            // Update associated payment if data provided
            if (!empty($paymentData) && isset($paymentData['payment_id'])) {
                Payment::where('id', $paymentData['payment_id'])
                    ->update([
                        'status' => $completed ? 'completed' : 'pending',
                        'transaction_id' => $paymentData['transaction_id'] ?? null,
                        'external_id' => $paymentData['external_id'] ?? null,
                        'payment_data' => json_encode($paymentData['details'] ?? []),
                        'completed_at' => $completed ? now() : null
                    ]);
            }

            return $enrollment->refresh();
        } catch (\Exception $e) {
            \Log::error("Error updating payment status", [
                'error' => $e->getMessage(),
                'enrollment_id' => $enrollmentId
            ]);
            return null;
        }
    }
}
