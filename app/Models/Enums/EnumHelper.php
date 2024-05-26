<?php

namespace App\Models\Enums;

use Exception;
use ReflectionEnum;
use ReflectionException;

// ToDo: Tools-Sammlung

/**
 * Enum Helper Trait
 */
trait EnumHelper
{
    public static function keys(): array
    {
        return array_map(
            fn (self $case) => $case->name,
            static::cases()
        );
    }

    public static function values(): array
    {
        return array_map(
            fn (self $case) => $case->value,
            static::cases()
        );
    }

    public static function options(): array
    {
        return array_column(static::cases(), 'value', 'name');
    }


}
