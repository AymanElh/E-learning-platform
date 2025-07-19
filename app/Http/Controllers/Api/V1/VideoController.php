<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\VideoRequest;
use App\Interfaces\VideoRepositoryInterface;
use App\Repositories\VideoRepository;
use Illuminate\Http\Request;
use Mockery\Exception;

/**
 * @OA\Tag(
 *     name="Videos",
 *     description="API endpoints for video management and course content"
 * )
 */
class VideoController extends Controller
{
    public VideoRepository $videoRepository;

    /**
     * @param VideoRepository $videoRepository
     */
    public function __construct(VideoRepository $videoRepository)
    {
        $this->videoRepository = $videoRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/courses/{courseId}/videos",
     *     tags={"Videos"},
     *     summary="Get videos for a course",
     *     description="Retrieve all videos associated with a specific course",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Videos retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Introduction to Laravel"),
     *                     @OA\Property(property="description", type="string", example="Learn the basics of Laravel framework"),
     *                     @OA\Property(property="video_url", type="string", example="storage/videos/course_1_video_1.mp4"),
     *                     @OA\Property(property="duration", type="integer", example=1800, description="Duration in seconds"),
     *                     @OA\Property(property="order", type="integer", example=1),
     *                     @OA\Property(property="course_id", type="integer", example=1),
     *                     @OA\Property(property="created_at", type="string", format="datetime"),
     *                     @OA\Property(property="updated_at", type="string", format="datetime")
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
     *         description="Error fetching videos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="error fetching videos")
     *         )
     *     )
     * )
     */
    public function index(int $courseId)
    {
        try {
            $videos = $this->videoRepository->getVideosByCourse($courseId);
            return response()->json([
                'success' => true,
                'data' => $videos
            ]);
        } catch(\Exception $e) {
            \Log::error("Error fetching videos: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "error fetching videos",
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/courses/{courseId}/videos",
     *     tags={"Videos"},
     *     summary="Upload a video to a course",
     *     description="Add a new video to a specific course",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="courseId",
     *         in="path",
     *         description="Course ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "video"},
     *                 @OA\Property(property="title", type="string", example="Laravel Controllers Tutorial"),
     *                 @OA\Property(property="description", type="string", example="Learn about Laravel controllers and routing"),
     *                 @OA\Property(property="order", type="integer", example=2, description="Order/position of the video in the course"),
     *                 @OA\Property(property="duration", type="integer", example=2400, description="Duration in seconds"),
     *                 @OA\Property(
     *                     property="video",
     *                     type="string",
     *                     format="binary",
     *                     description="Video file to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Video added to course successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Video added to course successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Laravel Controllers Tutorial"),
     *                 @OA\Property(property="description", type="string", example="Learn about Laravel controllers and routing"),
     *                 @OA\Property(property="video_url", type="string", example="storage/videos/course_1_video_2.mp4"),
     *                 @OA\Property(property="duration", type="integer", example=2400),
     *                 @OA\Property(property="order", type="integer", example=2),
     *                 @OA\Property(property="course_id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Failed to add video to course",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to add video to course")
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
     *         description="Error uploading video",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Error uploading the video")
     *         )
     *     )
     * )
     */
    public function store(VideoRequest $request, int $courseId)
    {
        try {
//            dd($request->file('video'));
            $video = $this->videoRepository->addToCourse($courseId, $request->except('video'), $request->file('video'));
            if(!$video) {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to add video to course"
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => "Video added to course successfully",
                'data' => $video
            ], 201);
        } catch (Exception $e) {
            \Log::error("Error adding the video to course: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error uploading the video"
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/videos/{id}",
     *     tags={"Videos"},
     *     summary="Get a specific video",
     *     description="Retrieve a single video by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Video ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Introduction to Laravel"),
     *                 @OA\Property(property="description", type="string", example="Learn the basics of Laravel framework"),
     *                 @OA\Property(property="video_url", type="string", example="storage/videos/course_1_video_1.mp4"),
     *                 @OA\Property(property="duration", type="integer", example=1800),
     *                 @OA\Property(property="order", type="integer", example=1),
     *                 @OA\Property(property="course_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Video not found")
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
    public function show(int $id)
    {
        try {
            $video = $this->videoRepository->getVideoById($id);
            if(!$video) {
                return response()->json([
                    'success' => false,
                    'message' => "Video not found"
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $video
            ], 200);
        } catch (Exception $e) {
            \Log::error("Error fetching the video: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error fetching the video"
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/videos/{id}",
     *     tags={"Videos"},
     *     summary="Update a video",
     *     description="Update video information (not the video file itself)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Video ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Video Title"),
     *             @OA\Property(property="description", type="string", example="Updated video description"),
     *             @OA\Property(property="order", type="integer", example=3),
     *             @OA\Property(property="duration", type="integer", example=3000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Video updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="Updated Video Title"),
     *                 @OA\Property(property="description", type="string", example="Updated video description"),
     *                 @OA\Property(property="order", type="integer", example=3),
     *                 @OA\Property(property="duration", type="integer", example=3000),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Video not found")
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
    public function update(VideoRequest $request, int $id)
    {
        try {
            $video = $this->videoRepository->update($id, $request->except('video'), $request->file('video'));
            if(!$video) {
                return response()->json([
                    'success' => false,
                    'message' => "Video not found"
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => "Video updated successfully",
                'data' => $video
            ]);
        } catch (Exception $e) {
            \Log::error("Error updating the video: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error updating the video"
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/videos/{id}",
     *     tags={"Videos"},
     *     summary="Delete a video",
     *     description="Remove a video from the course and delete the file",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Video ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Video deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Video deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Video not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Video not found")
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
    public function destroy(int $id)
    {
        try {
            $deleted = $this->videoRepository->delete($id);
            if(!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => "Video not found"
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => "Video deleted successfully"
            ]);
        } catch (Exception $e) {
            \Log::error("Error deleting the video: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error deleting the video"
            ]);
        }
    }
}
