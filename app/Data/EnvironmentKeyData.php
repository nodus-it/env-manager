<?php

namespace App\Data;

use App\Enums\VariableKeySource;
use Spatie\LaravelData\Data;

class EnvironmentKeyData extends Data
{
    public int $variable_key_id;

    public string $key;

    public string $type;

    public string $value;

    public VariableKeySource $source;

    public int $sourceId;
}
