<?php

namespace Laracroft\Traits;

use Laracroft\Helpers\CamelCaseBuilder;
use Illuminate\Support\Str;

/**
 * camelCase property names on Eloquent.
 */
trait HasCamelCase
{
    /**
     * @inheritDoc
     */
    public function getAttribute($key) : mixed
    {
        $snakeKey = Str::snake($key);

        if ( $snakeKey !== $key ) {
            if (
                array_key_exists($snakeKey, $this->attributes ?? []) ||
                array_key_exists($snakeKey, $this->getCasts()) ||
                $this->hasGetMutator($snakeKey) ||
                $this->hasAttributeMutator($snakeKey) ||
                $this->isRelation($snakeKey)
            ) {
                return parent::getAttribute($snakeKey);
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * @inheritDoc
     */
    public function isDirty($attributes = null) : bool
    {
        if ( $attributes === null ) {
            return parent::isDirty();
        }
        return parent::isDirty($this->normaliseArgument($attributes));
    }

    /**
     * @inheritDoc
     */
    public function isClean($attributes = null) : bool
    {
        return !$this->isDirty($attributes);
    }

    /**
     * @inheritDoc
     */
    public function wasChanged($attributes = null) : bool
    {
        if ( $attributes === null ) {
            return parent::wasChanged();
        }

        return parent::wasChanged($this->normaliseArgument($attributes));
    }

    /**
     * @inheritDoc
     */
    public function getOriginal($key = null, $default = null) : mixed
    {
        if ( $key !== null ) {
            $key = Str::snake($key);
        }
        return parent::getOriginal($key, $default);
    }

    /**
     * @inheritDoc
     */
    public function setAttribute($key, $value) : mixed
    {
        return parent::setAttribute(Str::snake($key), $value);
    }

    /**
     * @inheritDoc
     */
    public function fill(array $attributes) : static
    {
        return parent::fill($this->normaliseKeys($attributes));
    }

    /**
     * @inheritDoc
     */
    public function forceFill(array $attributes) : static
    {
        return parent::forceFill($this->normaliseKeys($attributes));
    }

    /**
     * @inheritDoc
     */
    public function toArray() : array
    {
        return $this->keysToCamelCase(parent::toArray());
    }

    /**
     * @inheritDoc
     */
    public function newEloquentBuilder($query) : CamelCaseBuilder
    {
        return new CamelCaseBuilder($query);
    }

    /**
     * Convert every key in the given array from camelCase to snake_case.
     */
    protected function normaliseKeys(array $attributes) : array
    {
        $result = [];

        foreach ($attributes as $key => $value) {
            $result[Str::snake($key)] = $value;
        }

        return $result;
    }

    /**
     * Normalise a single key or array of keys to snake_case.
     */
    protected function normaliseArgument(array|string|null $keys) : array|string|null
    {
        if ( is_array($keys) ) {
            return array_map(fn($k) => Str::snake($k), $keys);
        }

        return $keys !== null ? Str::snake($keys) : null;
    }

    /**
     * Convert every key in the given array from snake_case to camelCase.
     */
    protected function keysToCamelCase(array $attributes) : array
    {
        $result = [];

        foreach ($attributes as $key => $value) {
            $result[Str::camel($key)] = $value;
        }

        return $result;
    }
}
