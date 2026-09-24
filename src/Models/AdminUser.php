<?php

namespace Laracroft\Models;

use Laracroft\Traits\HasCamelCase;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;
use Spatie\Permission\Traits\HasRoles;

class AdminUser extends Authenticatable
{
    use HasApiTokens, HasFactory, HasCamelCase, Notifiable, HasOneTimePasswords, HasRoles;

    /**
     * @inheritdoc
     */
    protected string $guard_name = 'admin';

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_prefix',
        'phone',
        'password',
        'is_active',
    ];

    /**
     * @inheritdoc
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @inheritdoc
     */
    protected static function booted() : void
    {
        static::creating(function (self $user) {
            if ( empty($user->password) ) {
                $user->password = Str::password(12);
            }
        });
    }

    /**
     * @inheritdoc
     */
    protected function casts() : array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNameAttribute() : string
    {
        return trim("{$this->firstName} {$this->lastName}");
    }

    /**
     * @inheritdoc
     */
    public function getPhoneAttribute() : ?string
    {
        $phone = $this->getPhoneRawAttribute();

        if ( !$phone ) {
            return null;
        }

        return $this->getPhonePrefixAttribute()
            ? "{$this->getPhonePrefixAttribute()}{$phone}"
            : $phone;
    }

    /**
     * @inheritdoc
     */
    public function getPhoneRawAttribute() : ?string
    {
        $phone = $this->attributes['phone'] ?? null;

        return $phone !== null ? preg_replace('/^0/', '', $phone) : null;
    }

    /**
     * @inheritdoc
     */
    public function getPhonePrefixAttribute() : ?string
    {
        return $this->attributes['phone_prefix'] ?? null;
    }
}
