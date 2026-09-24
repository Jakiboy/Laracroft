<?php

namespace Laracroft\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    public const SUPER = 'super';
    public const ADMIN = 'admin';
    public const STAFF = 'staff';

    public const GUARD = 'admin';

    protected $fillable = ['name', 'guard_name', 'title', 'description'];

    /**
     * @inheritdoc
     */
    public static function definitions() : array
    {
        return json_decode(Storage::get('role.json'), true) ?? [];
    }

    /**
     * @inheritdoc
     */
    public static function titles() : array
    {
        return array_map(fn($role) => $role['title'] ?? '', static::definitions());
    }

    /**
     * @inheritdoc
     */
    public function isProtected() : bool
    {
        return (bool)(static::definitions()[$this->name]['protected'] ?? false);
    }
}
