<?php

namespace App\Models\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProjectStatus: string implements HasLabel, HasColor
{
    private const TRANSLATION_FILE = 'project';

    use EnumHelper;

    case ACTIVE = 'active';

    case INACTIVE = 'inactive';

    case ARCHIVED = 'archived';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'danger',
            self::ARCHIVED => 'warning',
        };
    }
}
