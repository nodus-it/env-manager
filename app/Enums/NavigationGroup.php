<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

enum NavigationGroup implements HasLabel
{
    case MAIN;

    case SETTINGS;

    public function getLabel(): string|Htmlable|null
    {
        return __('navigation.group.'.Str::lower($this->name));
    }
}
