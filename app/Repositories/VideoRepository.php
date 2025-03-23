<?php

namespace App\Repositories;

use App\Interfaces\VideoRepositoryInterface;
use App\Models\Course;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Mockery\Exception;

class VideoRepository implements VideoRepositoryInterface
{

    public function addToCourse(int $courseId, array $data, $file)
    {
        try {
            $course = Course::find($courseId);
            if(!$course) {
                return null;
            }
            $path = $file->store('course-videos', 'public');
//            dd($path);
//            $path = Storage::disk('public')->put('/', $file->file('video'));

            $videoData = [
                'course_id' => $courseId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'video_path' => $path,
                'duration' => null,
            ];
//            dd($videoData);
            return Video::create($videoData);
        } catch (\Exception $e) {
            \Log::error("error adding video to course " . $e->getMessage());
            return null;
        }
    }

    public function update(int $id, array $data, $file)
    {
        try {
            $video = Video::find($id);
            if(!$video) {
                return null;
            }
            if ($file) {
                // Delete old file
                Storage::disk('public')->delete($video->file_path);

                // Store new file
                $path = $file->store('course-videos/' . $video->course_id, 'public');
                $video->video_path = $path;

            }

            // Update other fields
            $video->title = $data['title'] ?? $video->title;
            $video->description = $data['description'] ?? $video->description;

            $video->save();
            return $video;
        } catch(Exception $e) {
            \Log::error("Error updating the video: " . $e->getMessage());
            return null;
        }
    }

    public function delete(int $id)
    {
        try {
            $video = Video::find($id);
            if(!$video) {
                return false;
            }
            Storage::delete($video->video_path);
            return $video->delete();
        } catch (\Exception $e) {
            \Log::error("Error deleting the video: " . $e->getMessage());
            return false;
        }
    }

    public function getVideosByCourse(int $courseId)
    {
        return Video::where('course_id', $courseId)->get();
    }

    public function getVideoById(int $id)
    {
        return Video::find($id);
    }
}
