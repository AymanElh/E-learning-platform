<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\TagRequest;
use App\Http\Resources\V1\TagCollection;
use App\Http\Resources\V1\TagResource;
use App\Interfaces\TagRepositoryInterface;

/**
 * @OA\Tag(
 *     name="Tags",
 *     description="API endpoints for tag management"
 * )
 */
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
     * @OA\Get(
     *     path="/api/v1/tags",
     *     tags={"Tags"},
     *     summary="Get all tags",
     *     description="Retrieve a list of all tags",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Tags retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tags imported successfully"),
     *             @OA\Property(
     *                 property="tags",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="PHP"),
     *                         @OA\Property(property="description", type="string", example="PHP programming language"),
     *                         @OA\Property(property="color", type="string", example="#3178C6"),
     *                         @OA\Property(property="created_at", type="string", format="datetime"),
     *                         @OA\Property(property="updated_at", type="string", format="datetime")
     *                     )
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
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/v1/tags",
     *     tags={"Tags"},
     *     summary="Create a new tag",
     *     description="Store a new tag in the database",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="JavaScript"),
     *             @OA\Property(property="description", type="string", example="JavaScript programming language"),
     *             @OA\Property(property="color", type="string", example="#F7DF1E", description="Hex color code for the tag")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tag created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag inserted successfully")
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
     *         description="Tag creation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tag not inserted")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/v1/tags/{id}",
     *     tags={"Tags"},
     *     summary="Get a specific tag",
     *     description="Retrieve a single tag by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tag ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="PHP"),
     *             @OA\Property(property="description", type="string", example="PHP programming language"),
     *             @OA\Property(property="color", type="string", example="#3178C6"),
     *             @OA\Property(property="created_at", type="string", format="datetime"),
     *             @OA\Property(property="updated_at", type="string", format="datetime")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tag not found")
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
     * @OA\Put(
     *     path="/api/v1/tags/{id}",
     *     tags={"Tags"},
     *     summary="Update a tag",
     *     description="Update an existing tag by its ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tag ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Tag Name"),
     *             @OA\Property(property="description", type="string", example="Updated tag description"),
     *             @OA\Property(property="color", type="string", example="#FF5733")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag updated successfully")
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
     *         description="Tag update failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tag not updated")
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/v1/tags/{id}",
     *     tags={"Tags"},
     *     summary="Delete a tag",
     *     description="Remove a tag from the database",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Tag ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tag deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Tag deleted successfully")
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
     *         description="Tag deletion failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Tag not deleted")
     *         )
     *     )
     * )
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
