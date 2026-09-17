<?php

namespace Laracroft\Services;

use Laracroft\Models\Role;
use Laracroft\Helpers\Permission as PermissionHelper;
use Laracroft\Resources\RoleCollection;
use Laracroft\Resources\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleService
{
	/**
	 * @inheritdoc
	 */
	public static function getAll(Request $request) : RoleCollection
	{
		$items = (int)$request->query('items', 5);
		$search = $request->query('search');
		$protected = array_keys(array_filter(Role::definitions(), fn($role) => $role['protected'] ?? false));

		$query = Role::with('permissions')
			->whereNotIn('name', $protected)
			->where('guard_name', Role::GUARD);

		if ( $search ) {
			$query->where(function ($q) use ($search) {
				$q->where('name', 'like', '%' . $search . '%')
					->orWhere('description', 'like', '%' . $search . '%');
			});
		}

		return new RoleCollection($query->orderBy('name', 'ASC')->paginate($items));
	}

	/**
	 * @inheritdoc
	 */
	public static function get($id) : RoleResource
	{
		if ( !($role = Role::with('permissions')->where('guard_name', Role::GUARD)->find($id)) ) {
			throw new ModelNotFoundException('Role not found');
		}
		return new RoleResource($role);
	}

	/**
	 * @inheritdoc
	 */
	public static function meta() : array
	{
		return [
			'roles'   => Role::titles(),
			'modules' => PermissionHelper::MODULES,
		];
	}

	/**
	 * Create role (not permitted via API, kept for internal use).
	 */
	public static function create(array $data) : int|false
	{
		$role = Role::create([
			'name'        => $data['name'],
			'guard_name'  => Role::GUARD,
			'title'       => $data['title'] ?? null,
			'description' => $data['description'] ?? null,
		]);

		if ( !empty($data['permissions']) ) {
			self::syncPermissions($role, $data['permissions']);
		}

		return $role->id ?? false;
	}

	/**
	 * @inheritdoc
	 */
	public static function update(array $data, Role $role) : int|false
	{
		if ( $role->isProtected() ) {
			return false;
		}

		$role->update([
			'title'       => $data['title'] ?? $role->title,
			'description' => $data['description'] ?? $role->description,
		]);

		if ( isset($data['permissions']) ) {
			self::syncPermissions($role, $data['permissions']);
		}

		return $role->id;
	}

	/**
	 * @inheritdoc
	 */
	public static function delete($id) : bool
	{
		if ( !($role = Role::where('guard_name', Role::GUARD)->find($id)) ) {
			return false;
		}
		if ( $role->isProtected() ) {
			return false;
		}
		$role->delete();
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public static function syncPermissions(Role $role, array $permissions) : void
	{
		if ( array_is_list($permissions) ) {
			$permissionNames = $permissions;
		} else {

			$permissionNames = [];
			foreach ($permissions as $module => $actions) {
				foreach ($actions as $action => $enabled) {
					if ( $enabled ) {
						$permissionNames[] = $module . '.' . $action;
					}
				}
			}
		}

		$valid = PermissionHelper::all();
		$permissionNames = array_values(array_intersect($permissionNames, $valid));

		$role->syncPermissions($permissionNames);
		self::purgeCache();
	}

    /**
     * @inheritdoc
     */
	public static function synchronize() : bool
	{
		try {
			$allNames = Permission::all();

			foreach ($allNames as $name) {
				Permission::firstOrCreate(['name' => $name, 'guard_name' => Role::GUARD]);
			}

			self::purgeCache();
			return true;
		} catch (\Exception $e) {
			Log::error('Error syncing permissions: ' . $e->getMessage());
			return false;
		}
	}

    /**
     * @inheritdoc
     */
	public static function purgeCache() : void
	{
		app(PermissionRegistrar::class)->forgetCachedPermissions();
	}
}
