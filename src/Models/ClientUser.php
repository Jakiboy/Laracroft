<?php

namespace Laracroft\Models;

use Laracroft\Traits\HasCamelCase;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\OneTimePasswords\Models\Concerns\HasOneTimePasswords;

class ClientUser extends Authenticatable
{
    use HasApiTokens, HasFactory, HasCamelCase, Notifiable, HasOneTimePasswords;
    
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
    protected $attributes = [
        'is_active' => true,
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
}
