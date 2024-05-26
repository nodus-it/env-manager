<?php

namespace App\Models\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasLabel, HasColor
{
    private const TRANSLATION_FILE = 'user';

    use EnumHelper;

    case ACTIVE = 'active';

    case INACTIVE = 'inactive';

    case BLOCKED = 'blocked';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'warning',
            self::BLOCKED => 'danger',
        };
    }
}
