<?php

namespace App\Facades;

use App\Data\EnvironmentKeyData;
use App\Models\Environment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection<int, EnvironmentKeyData> getKeys(Environment $environment, bool $showSecrets = false)
 */
class EnvironmentService extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'environment.service';
    }
}
