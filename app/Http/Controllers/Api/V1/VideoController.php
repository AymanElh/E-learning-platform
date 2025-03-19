<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\VideoRequest;
use App\Interfaces\VideoRepositoryInterface;
use App\Repositories\VideoRepository;
use Illuminate\Http\Request;
use Mockery\Exception;

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

    public function show(int $id)
    {
        try {
            $video = $this->videoRepository->getVideoById($id);
            if(!$video) {
                return response()->json([
                    'success' => false,
                    'message' => "Video not found"
                ]);
            }
            return response()->json([
                'success' => true,
                'data' => $video
            ]);
        } catch (Exception $e) {
            \Log::error("Error fetching the video: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Error fetching the video"
            ]);
        }
    }

    public function update(VideoRequest $request, $id)
    {
        // TODO: Implement update() method.
    }

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
