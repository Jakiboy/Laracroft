<?php

namespace Laracroft\Policies;

use Laracroft\Models\AdminUser;
use Laracroft\Models\Role;

class RolePolicy
{
    /**
     * @inheritdoc
     */
    public function update(AdminUser $auth, Role $role) : bool
    {
        return !$role->isProtected();
    }
}
