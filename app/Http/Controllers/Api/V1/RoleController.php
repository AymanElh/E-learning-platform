<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\RoleRequest;
use App\Interfaces\RoleRepositoryInterface;
use App\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    protected RoleRepository $roleRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Interfaces\RoleRepositoryInterface  $roleRepository
     * @return void
     */
    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
//        $this->middleware('auth:api');
    }


    /**
     * Display All roles
     *
     * @return JsonResponse
     */
    public function index()
    {
        $roles = $this->roleRepository->getAllRoles();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * Create a new role
     *
     * @param RoleRequest $request
     * @return JsonResponse
     */
    public function store(RoleRequest $request)
    {
        $role = $this->roleRepository->createRole($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    /**
     * Show role by id
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $role = $this->roleRepository->getRoleById($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }


    /**
     * update a role by its id
     *
     * @param RoleRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(RoleRequest $request, $id)
    {
        $role = $this->roleRepository->updateRole($id, $request->validated());

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }


    /**
     * Delete a specific role
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $deleted = $this->roleRepository->deleteRole($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }

    /**
     * Display all permission
     *
     * @return JsonResponse
     */
    public function permissions()
    {
        $permissions = $this->roleRepository->getAllPermissions();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function assignPermissions(Request $request, $id)
    {
        $role = $this->roleRepository->assignPermissions($id, $request->permissions);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned successfully',
            'data' => $role
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function removePermissions(Request $request, $id)
    {
        $role = $this->roleRepository->removePermissions($id, $request->permissions);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permissions removed successfully',
            'data' => $role
        ]);
    }
}
