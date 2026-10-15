<?php

namespace Laracroft\Policies;

use Laracroft\Models\AdminUser;
use Laracroft\Models\Role;

class UserPolicy
{
    /**
     * @inheritdoc
     */
    public function view(AdminUser $auth, AdminUser $user) : bool
    {
        if ( $user->hasRole(Role::SUPER) ) {
            return false;
        }

        if ( $auth->hasRole(Role::ADMIN) && $user->hasRole(Role::ADMIN) ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function update(AdminUser $auth, AdminUser $user) : bool
    {
        return !$user->hasRole(Role::SUPER);
    }

    /**
     * @inheritdoc
     */
    public function delete(AdminUser $auth, AdminUser $user) : bool
    {
        if ( $user->hasRole(Role::SUPER) ) {
            return false;
        }

        if ( $auth->hasRole(Role::ADMIN) && $user->hasRole(Role::ADMIN) ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function archive(AdminUser $auth, AdminUser $user) : bool
    {
        return $this->delete($auth, $user);
    }

    /**
     * @inheritdoc
     */
    public function enable(AdminUser $auth, AdminUser $user) : bool
    {
        return $this->delete($auth, $user);
    }
}
