<?php

namespace Laracroft\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EncryptedFloat implements CastsAttributes
{
    /**
     * @inheritdoc
     */
    public function get(Model $model, string $key, mixed $value, array $attributes) : ?float
    {
        return $value === null ? null : (float)Crypt::decryptString($value);
    }

    /**
     * @inheritdoc
     */
    public function set(Model $model, string $key, mixed $value, array $attributes) : ?string
    {
        return $value === null ? null : Crypt::encryptString((string)$value);
    }
}
