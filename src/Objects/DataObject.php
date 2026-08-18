<?php

namespace Laracroft\Objects;

use ReflectionClass;

abstract class DataObject
{
    /**
     * Create an instance of the class from an associative array, mapping array keys to property names.
     */
    public static function fromArray(array $data) : static
    {
        $ref = new ReflectionClass(static::class);
        $instance = $ref->newInstance();

        foreach ($ref->getProperties() as $prop) {
            $name = $prop->getName();
            if ( array_key_exists($name, $data) ) {
                $prop->setValue($instance, $data[$name]);
            }
        }

        return $instance;
    }

    /**
     * Serialize to array, excluding null values.
     */
    public function toArray() : array
    {
        return array_filter(get_object_vars($this), fn($v) => $v !== null);
    }
}
