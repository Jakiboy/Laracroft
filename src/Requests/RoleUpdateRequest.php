<?php

namespace Laracroft\Requests;

use Laracroft\Helpers\Permission;

class RoleUpdateRequest extends Authentication
{
    /**
     * @inheritdoc
     */
    public function rules() : array
    {
        return [
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'sometimes|array',
        ];
    }

    /**
     * @inheritdoc
     */
    public function withValidator($validator) : void
    {
        $validator->after(function ($v) {
            $permissions = $this->input('permissions');
            if ( !is_array($permissions) ) {
                return;
            }

            if ( array_is_list($permissions) ) {
                // Flat list of strings: ['dashboard.view', 'user.create']
                $valid = Permission::all();
                foreach ($permissions as $name) {
                    if ( !in_array($name, $valid, strict: true) ) {
                        $v->errors()->add('permissions', "Unknown permission: {$name}");
                    }
                }
            } else {
                // Structured: {'dashboard' => {'view' => true}}
                foreach ($permissions as $module => $actions) {
                    if ( !isset(Permission::MODULES[$module]) ) {
                        $v->errors()->add("permissions.{$module}", "Unknown module: {$module}");
                        continue;
                    }
                    if ( !is_array($actions) ) {
                        continue;
                    }
                    foreach (array_keys($actions) as $action) {
                        if ( !in_array($action, Permission::MODULES[$module], strict: true) ) {
                            $v->errors()->add("permissions.{$module}.{$action}", "Unknown action '{$action}' for module '{$module}'");
                        }
                    }
                }
            }
        });
    }
}
