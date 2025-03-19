<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionRequest;
use App\Interfaces\PermissionRepositoryInterface;
use App\Repositories\PermissionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery\Exception;

class PermissionController extends Controller
{
    protected PermissionRepository $permissionRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Interfaces\PermissionRepositoryInterface  $permissionRepository
     * @return void
     */
    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }


    public function index()
    {
        $permissions = $this->permissionRepository->getAllPermissions();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function store(PermissionRequest $request)
    {
        try{
            $permission = $this->permissionRepository->createPermission($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Permission created successfully',
                'data' => $permission
            ], 201);
        } catch (Exception $e) {
            return response()-json([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
        }
    }


    public function show($id)
    {
        $permission = $this->permissionRepository->getPermissionById($id);

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $permission
        ]);
    }

    public function update(PermissionRequest $request, $id)
    {
        $permission = $this->permissionRepository->updatePermission($id, $request->validated());

        if (!$permission) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully',
            'data' => $permission
        ]);
    }

    public function destroy($id)
    {
        $deleted = $this->permissionRepository->deletePermission($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Permission not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully'
        ]);
    }
}
