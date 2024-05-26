<?php

namespace App\Models\Enums;

use Illuminate\Support\Str;

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
        $options = [];
        foreach (static::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }

    public function getLabel(): ?string
    {
        return trans(self::TRANSLATION_FILE . '.enum.status.' . Str::lower($this->name));
    }
}
