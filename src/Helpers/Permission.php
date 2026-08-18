<?php

namespace App\Helpers;

/**
 * Single source of truth for the application's permission schema.
 *
 * Only define permissions that are actively gated in routes — no phantom entries.
 * To add a new permission:
 *   1. Add it here under MODULES
 *   2. Add it to the relevant ROLE_DEFAULTS entries
 *   3. Add the route middleware in api.php
 *   4. Call /admin/role/synchronize (or re-seed) to push it to the database
 */
final class Permission
{
    /**
     * All available modules and their permitted actions.
     * These map directly to 'module.action' permission names in Spatie.
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
     * Default permission assignments per role, applied on first seed.
     * Role assignments made via the admin UI are stored in the database
     * and take precedence — these are only used during initial seeding.
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
     * All permission names as a flat list: ['module.action', ...]
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
     * Full schema with every permission set to false.
     * Used by the UI to render the permissions form (all checkboxes unchecked by default).
     * Format: ['module' => ['action' => false]]
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
