<?php

namespace Laracroft\Helpers;

use Illuminate\Support\Facades\Storage;

final class Permission
{
    public const array MODULES = [];

    public static function modules() : array
    {
        return self::load('modules.json');
    }

    public static function roleDefaults() : array
    {
        return self::load('permissions.json');
    }

    /**
     * @inheritdoc
     */
    public static function all() : array
    {
        $list = [];
        foreach (self::modules() as $module => $actions) {
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
        foreach (self::modules() as $module => $actions) {
            foreach ($actions as $action) {
                $schema[$module][$action] = false;
            }
        }
        return $schema;
    }

    private static function load(string $filename) : array
    {
        $data = json_decode(Storage::get($filename), true);

        if ( !is_array($data) ) {
            throw new \RuntimeException("Invalid permission configuration: {$filename}");
        }

        return $data;
    }
}
