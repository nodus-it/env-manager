<?php

namespace App\Models\Enums;

enum UserStatus: string
{
    use EnumHelper;

    case ACTIVE = 'active';

    case INACTIVE = 'inactive';

    case BLOCKED = 'blocked';
}
