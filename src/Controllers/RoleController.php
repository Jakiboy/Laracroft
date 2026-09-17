<?php

namespace Laracroft\Controllers;

use Laracroft\Helpers\Response;
use Laracroft\Helpers\Permission;
use Laracroft\Requests\RoleUpdateRequest;
use Laracroft\Models\Role;
use Laracroft\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * roleIndex [GET] /role
     */
    public function index(Request $request) : Response
    {
        return $this->success(
            data: RoleService::getAll($request)
        );
    }

    /**
     * roleItem [GET] /role/{id}
     */
    public function show(Role $role)
    {
        return $this->success(
            data: RoleService::get($role->id)
        );
    }

    /**
     * roleUpdate [PUT] /role/{id}
     */
    public function update(RoleUpdateRequest $request, Role $role)
    {
        $this->authorize('update', $role);

        if ( ($id = RoleService::update($request->validated(), $role)) ) {
            return $this->success(
                'Rôle mis à jour',
                ['id' => $id]
            );
        }

        return $this->error(
            message: 'Rôle non mis à jour',
            code: 422
        );
    }

    /**
     * roleMeta [GET] /role/meta
     */
    public function meta()
    {
        return $this->setResponse(
            data: RoleService::meta()
        );
    }

    /**
     * Get all available permissions
     * [GET] /role/permissions
     */
    public function permissions()
    {
        return $this->success(
            data: Permission::schema()
        );
    }

    /**
     * Clear all permission caches
     */
    public function clearCache() : Response
    {
        RoleService::purgeCache();
        return $this->success('Permission caches cleared successfully');
    }

    /**
     * @inheritdoc
     */
    public function synchronize() : Response
    {
        if ( RoleService::synchronize() ) {
            return $this->success('Permissions synced successfully');
        }
        return $this->error('Failed to sync permissions', code: 500);
    }
}
