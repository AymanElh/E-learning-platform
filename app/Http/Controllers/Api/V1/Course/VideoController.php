<?php

namespace App\Http\Controllers\Api\V1\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Section;
use App\Models\Lesson;
use App\Models\Video;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Tag(
 *     name="Videos",
 *     description="API endpoints for lesson video management"
 * )
 */
class VideoController extends Controller
{
    use ApiResponse;

    /**
     * @OA\Get(
     *     path="/api/v1/courses/{course}/sections/{section}/lessons/{lesson}/video",
     *     tags={"Videos"},
     *     summary="Get lesson video details",
     *     description="Retrieve video information for a specific lesson",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="course",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="section",
     *         in="path",
     *         description="Section ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="lesson",
     *         in="path",
     *         description="Lesson ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Video details retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="lesson_id", type="integer", example=1),
     *                 @OA\Property(property="video_url", type="string", example="https://example.com/video.mp4"),
     *                 @OA\Property(property="video_thumbnail", type="string", example="https://example.com/thumb.jpg"),
     *                 @OA\Property(property="video_duration", type="integer", example=3600),
     *                 @OA\Property(property="video_quality", type="string", example="720p"),
     *                 @OA\Property(property="video_size", type="number", format="float", example=156.75),
     *                 @OA\Property(property="formatted_duration", type="string", example="60:00"),
     *                 @OA\Property(property="formatted_size", type="string", example="156.75 MB")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found or lesson is not a video type",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Video not found for this lesson")
     *         )
     *     )
     * )
     */
    public function show(Course $course, Section $section, Lesson $lesson): JsonResponse
    {
        try {
            // Validate that the section belongs to the course
            if ($section->course_id !== $course->id) {
                return $this->errorResponse('Section not found for this course', null, 404);
            }

            // Validate that the lesson belongs to the section
            if ($lesson->section_id !== $section->id) {
                return $this->errorResponse('Lesson not found for this section', null, 404);
            }

            // Check if lesson is of video type
            if ($lesson->lesson_type !== 'video') {
                return $this->errorResponse('This lesson is not a video lesson', null, 400);
            }

            $video = $lesson->video;

            if (!$video) {
                return $this->errorResponse('Video not found for this lesson', null, 404);
            }

            return $this->successResponse('Video details retrieved successfully', [
                'id' => $video->id,
                'lesson_id' => $video->lesson_id,
                'video_url' => $video->video_url,
                'video_thumbnail' => $video->video_thumbnail,
                'video_duration' => $video->video_duration,
                'video_quality' => $video->video_quality,
                'video_size' => $video->video_size,
                'formatted_duration' => $video->formatted_duration,
                'formatted_size' => $video->formatted_size,
                'lesson' => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'description' => $lesson->description,
                    'order_index' => $lesson->order_index,
                    'is_free_preview' => $lesson->is_free_preview
                ]
            ]);

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Resource not found', null, 404);
        } catch (\Exception $e) {
            \Log::error('Error retrieving video details: ' . $e->getMessage());
            return $this->errorResponse('Error retrieving video details', null, 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/courses/{course}/sections/{section}/lessons/{lesson}/video",
     *     tags={"Videos"},
     *     summary="Upload or add video to lesson",
     *     description="Add video information to a video-type lesson",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"video_url"},
     *             @OA\Property(property="video_url", type="string", example="https://example.com/video.mp4"),
     *             @OA\Property(property="video_thumbnail", type="string", example="https://example.com/thumb.jpg"),
     *             @OA\Property(property="video_duration", type="integer", example=3600),
     *             @OA\Property(property="video_quality", type="string", enum={"480p", "720p", "1080p", "4k"}, example="720p"),
     *             @OA\Property(property="video_size", type="number", format="float", example=156.75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Video added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Video added to lesson successfully")
     *         )
     *     )
     * )
     */
    public function store(Request $request, Course $course, Section $section, Lesson $lesson): JsonResponse
    {
        try {
            // Validate that the section belongs to the course
            if ($section->course_id !== $course->id) {
                return $this->errorResponse('Section not found for this course', null, 404);
            }

            // Validate that the lesson belongs to the section
            if ($lesson->section_id !== $section->id) {
                return $this->errorResponse('Lesson not found for this section', null, 404);
            }

            // Check if lesson is of video type
            if ($lesson->lesson_type !== 'video') {
                return $this->errorResponse('Cannot add video to non-video lesson', null, 400);
            }

            // Check if video already exists for this lesson
            if ($lesson->video) {
                return $this->errorResponse('Video already exists for this lesson. Use update instead.', null, 409);
            }

            $validator = Validator::make($request->all(), [
                'video_url' => 'required|url|max:255',
                'video_thumbnail' => 'nullable|url|max:255',
                'video_duration' => 'nullable|integer|min:1',
                'video_quality' => 'nullable|in:480p,720p,1080p,4k',
                'video_size' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $video = Video::create([
                'lesson_id' => $lesson->id,
                'video_url' => $request->video_url,
                'video_thumbnail' => $request->video_thumbnail,
                'video_duration' => $request->video_duration,
                'video_quality' => $request->video_quality ?? '720p',
                'video_size' => $request->video_size
            ]);

            // Update lesson duration based on video duration if provided
            if ($request->video_duration) {
                $lesson->update([
                    'duration_minutes' => ceil($request->video_duration / 60)
                ]);
            }

            return $this->successResponse('Video added to lesson successfully', [
                'id' => $video->id,
                'lesson_id' => $video->lesson_id,
                'video_url' => $video->video_url,
                'video_thumbnail' => $video->video_thumbnail,
                'video_duration' => $video->video_duration,
                'video_quality' => $video->video_quality,
                'video_size' => $video->video_size,
                'formatted_duration' => $video->formatted_duration,
                'formatted_size' => $video->formatted_size
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error adding video to lesson: ' . $e->getMessage());
            return $this->errorResponse('Error adding video to lesson', null, 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/courses/{course}/sections/{section}/lessons/{lesson}/video",
     *     tags={"Videos"},
     *     summary="Update lesson video",
     *     description="Update video information for a lesson",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="video_url", type="string", example="https://example.com/video.mp4"),
     *             @OA\Property(property="video_thumbnail", type="string", example="https://example.com/thumb.jpg"),
     *             @OA\Property(property="video_duration", type="integer", example=3600),
     *             @OA\Property(property="video_quality", type="string", enum={"480p", "720p", "1080p", "4k"}, example="1080p"),
     *             @OA\Property(property="video_size", type="number", format="float", example=256.50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video updated successfully"
     *     )
     * )
     */
    public function update(Request $request, Course $course, Section $section, Lesson $lesson): JsonResponse
    {
        try {
            // Validate relationships
            if ($section->course_id !== $course->id) {
                return $this->errorResponse('Section not found for this course', null, 404);
            }

            if ($lesson->section_id !== $section->id) {
                return $this->errorResponse('Lesson not found for this section', null, 404);
            }

            if ($lesson->lesson_type !== 'video') {
                return $this->errorResponse('This lesson is not a video lesson', null, 400);
            }

            $video = $lesson->video;
            if (!$video) {
                return $this->errorResponse('Video not found for this lesson', null, 404);
            }

            $validator = Validator::make($request->all(), [
                'video_url' => 'nullable|url|max:255',
                'video_thumbnail' => 'nullable|url|max:255',
                'video_duration' => 'nullable|integer|min:1',
                'video_quality' => 'nullable|in:480p,720p,1080p,4k',
                'video_size' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', $validator->errors(), 422);
            }

            $updateData = array_filter($request->only([
                'video_url', 'video_thumbnail', 'video_duration', 'video_quality', 'video_size'
            ]), function($value) {
                return $value !== null;
            });

            $video->update($updateData);

            // Update lesson duration if video duration was updated
            if (isset($updateData['video_duration'])) {
                $lesson->update([
                    'duration_minutes' => ceil($updateData['video_duration'] / 60)
                ]);
            }

            return $this->successResponse('Video updated successfully', [
                'id' => $video->id,
                'lesson_id' => $video->lesson_id,
                'video_url' => $video->video_url,
                'video_thumbnail' => $video->video_thumbnail,
                'video_duration' => $video->video_duration,
                'video_quality' => $video->video_quality,
                'video_size' => $video->video_size,
                'formatted_duration' => $video->formatted_duration,
                'formatted_size' => $video->formatted_size
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating video: ' . $e->getMessage());
            return $this->errorResponse('Error updating video', null, 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/courses/{course}/sections/{section}/lessons/{lesson}/video",
     *     tags={"Videos"},
     *     summary="Delete lesson video",
     *     description="Remove video from a lesson",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Video deleted successfully"
     *     )
     * )
     */
    public function destroy(Course $course, Section $section, Lesson $lesson): JsonResponse
    {
        try {
            // Validate relationships
            if ($section->course_id !== $course->id) {
                return $this->errorResponse('Section not found for this course', null, 404);
            }

            if ($lesson->section_id !== $section->id) {
                return $this->errorResponse('Lesson not found for this section', null, 404);
            }

            if ($lesson->lesson_type !== 'video') {
                return $this->errorResponse('This lesson is not a video lesson', null, 400);
            }

            $video = $lesson->video;
            if (!$video) {
                return $this->errorResponse('Video not found for this lesson', null, 404);
            }

            $video->delete();

            return $this->successResponse('Video deleted successfully');

        } catch (\Exception $e) {
            \Log::error('Error deleting video: ' . $e->getMessage());
            return $this->errorResponse('Error deleting video', null, 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/{course}/videos",
     *     tags={"Videos"},
     *     summary="Get all videos in a course",
     *     description="Retrieve all video lessons in a course",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Course videos retrieved successfully"
     *     )
     * )
     */
    public function courseVideos(Course $course): JsonResponse
    {
        try {
            $videos = Video::whereHas('lesson.section', function($query) use ($course) {
                $query->where('course_id', $course->id);
            })->with(['lesson.section'])->get();

            $videoData = $videos->map(function($video) {
                return [
                    'id' => $video->id,
                    'video_url' => $video->video_url,
                    'video_thumbnail' => $video->video_thumbnail,
                    'video_duration' => $video->video_duration,
                    'video_quality' => $video->video_quality,
                    'video_size' => $video->video_size,
                    'formatted_duration' => $video->formatted_duration,
                    'formatted_size' => $video->formatted_size,
                    'lesson' => [
                        'id' => $video->lesson->id,
                        'title' => $video->lesson->title,
                        'order_index' => $video->lesson->order_index,
                        'is_free_preview' => $video->lesson->is_free_preview
                    ],
                    'section' => [
                        'id' => $video->lesson->section->id,
                        'title' => $video->lesson->section->title,
                        'order_index' => $video->lesson->section->order_index
                    ]
                ];
            });

            return $this->successResponse('Course videos retrieved successfully', $videoData);

        } catch (\Exception $e) {
            \Log::error('Error retrieving course videos: ' . $e->getMessage());
            return $this->errorResponse('Error retrieving course videos', null, 500);
        }
    }
}
