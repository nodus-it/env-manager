<?php

namespace App\Models\Enums;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
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
            fn(self $case) => $case->name,
            static::cases()
        );
    }

    public static function values(): array
    {
        return array_map(
            fn(self $case) => $case->value,
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

    public static function getTabs()
    {
        $tabs = [];
        foreach (self::cases() as $case) {
            $tabs[$case->name] = Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', $case->value))
                ->label($case->getLabel());
        }
        return $tabs;
    }
}
