<?php
namespace Hanaddi\Pena;
use Hanaddi\Pena\Exceptions\TypeException;

class Type {

    /**
     * Checks if a value matches any of the given types.
     *
     * @param mixed $value The value to check.
     * @param array<string> $types An array of types to match against.
     * @return bool True if the value matches any of the types, false otherwise.
     */
    public static function isTypes($value, $types) {
        $type = gettype($value);
        return in_array($type, $types);
    }

    /**
     * Asks for types based on the given value and types array.
     *
     * @param mixed $value The value to check against the types.
     * @param array<string> $types An array of types to check against.
     * @return void
     */
    public static function askTypes($value, $types) {
        if (!self::isTypes($value, $types)) {
            throw new TypeException("Invalid type");
        }
    }

    /**
     * Checks if a value is one of the specified objects.
     *
     * @param mixed $value The value to check.
     * @param array<string> $objects An array of objects' name to compare against.
     * @return bool Returns true if the value is one of the objects, false otherwise.
     */
    public static function isObjects($value, $objects) {
        $object = gettype($value);
        return in_array($object, $objects);
    }

    /**
     * Asks the user for input and returns the corresponding object from the given array of objects.
     *
     * @param mixed $value The value to ask the user for.
     * @param array<string> $objects The array of objects' name to choose from.
     * @return mixed|null The selected object from the array, or null if no matching object is found.
     */
    public static function askObjects($value, $objects) {
        self::askTypes($value, ['object']);
        if (!self::isObjects($value, $objects)) {
            throw new TypeException("Invalid object");
        }
    }
}
