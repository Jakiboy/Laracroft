<?php

namespace Laracroft\Helpers;

final class Permission
{
    /**
     * @inheritdoc
     */
    public const MODULES = [
        'dashboard'   => ['view'],
        'user'        => ['view', 'create', 'update', 'delete'],
        'client'      => ['view', 'create', 'update', 'delete'],
        'appointment' => ['view', 'create', 'update', 'delete'],
        'program'     => ['view', 'create', 'update', 'delete'],
        'treatment'   => ['view', 'create', 'update', 'delete'],
        'role'        => ['view', 'update'],
        'activity'    => ['view', 'delete'],
        'setting'     => ['view', 'update'],
    ];

    /**
     * @inheritdoc
     */
    public const ROLE_DEFAULTS = [
        'admin'   => [
            'dashboard.view',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'client.view',
            'client.create',
            'client.update',
            'client.delete',
            'appointment.view',
            'appointment.create',
            'appointment.update',
            'appointment.delete',
            'program.view',
            'program.create',
            'program.update',
            'program.delete',
            'treatment.view',
            'treatment.create',
            'treatment.update',
            'treatment.delete',
            'role.view',
            'role.update',
            'activity.view',
            'activity.delete',
            'setting.view',
            'setting.update',
        ],
        'manager' => [
            'dashboard.view',
            'client.view',
            'client.create',
            'client.update',
            'client.delete',
            'appointment.view',
            'appointment.create',
            'appointment.update',
            'appointment.delete',
            'program.view',
            'program.create',
            'program.update',
            'program.delete',
            'treatment.view',
            'treatment.create',
            'treatment.update',
            'treatment.delete',
        ],
        'doctor'  => [
            'dashboard.view',
            'client.view',
            'appointment.view',
            'program.view',
            'treatment.view',
        ],
    ];

    /**
     * @inheritdoc
     */
    public static function all() : array
    {
        $list = [];
        foreach (self::MODULES as $module => $actions) {
            foreach ($actions as $action) {
                $list[] = $module . '.' . $action;
            }
        }
        return $list;
    }

    /**
     * @inheritdoc
     */
    public static function schema() : array
    {
        $schema = [];
        foreach (self::MODULES as $module => $actions) {
            foreach ($actions as $action) {
                $schema[$module][$action] = false;
            }
        }
        return $schema;
    }
}
