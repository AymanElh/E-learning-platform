<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\TagRequest;
use App\Http\Resources\V1\TagCollection;
use App\Http\Resources\V1\TagResource;
use App\Interfaces\TagRepositoryInterface;

class TagController extends Controller
{
    public TagRepositoryInterface $tagRepository;

    /**
     * @param TagRepositoryInterface $tag
     */
    public function __construct(TagRepositoryInterface $tag)
    {
        $this->tagRepository = $tag;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $tags = new TagCollection($this->tagRepository->index());
        return response()->json([
            'success' => true,
            'message' => "Tags imported successfully",
            'tags' => $tags
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TagRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        try{
            $this->tagRepository->store($data);
            return response()->json([
                'success' => true,
                'message' => "Tag inserted successfully"
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating the tag: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Tag not inserted",
                'data' => $data
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
 public function show(int $id): \Illuminate\Http\JsonResponse
 {
     $tag = $this->tagRepository->getById($id);
     if (!$tag) {
         return response()->json([
             'success' => false,
             'message' => 'Tag not found'
         ], 404);
     }
     return response()->json(new TagResource($tag), 200);
 }

    /**
     * Update the specified resource in storage.
     */
    public function update(TagRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        try {
            $this->tagRepository->update($id, $data);
            return response()->json([
                'success' => true,
                'message' => "Tag updated successfully"
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error updating the tag: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Tag not updated"
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        try{
            $this->tagRepository->delete($id);
            return response()->json([
                'success' => true,
                'message' => "Tag deleted successfully"
            ]);
        }
        catch (\Exception $e) {
            \Log::error('Error deleting the tag: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Tag not deleted"
            ], 500);
        }
    }
}
