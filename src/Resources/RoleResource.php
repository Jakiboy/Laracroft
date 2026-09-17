<?php

namespace Laracroft\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * @inheritdoc
     */
    public function toArray($request) : array
    {
        if ( $this->isProtected() ) {
            return [];
        }

        $permissions = [];
        foreach ($this->permissions as $perm) {
            if ( str_contains($perm->name, '.') ) {
                [$module, $action] = explode('.', $perm->name, 2);
                $permissions[$module][$action] = true;
            }
        }

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'title'       => $this->title,
            'description' => $this->description,
            'permissions' => $permissions,
        ];
    }
}
