<?php

namespace App\Data;

use App\Facades\EnvironmentService as EnvironmentServiceFacade;
use App\Models\Environment;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class EnvironmentData extends Data
{
    public string $name;

    public string $slug;

    public string $type;

    public string $projectSlug;

    public string $projectName;

    /** @var Collection<int, EnvironmentKeyData> */
    public Collection $keys;

    public static function fromEnvironment(Environment $environment, bool $showSecrets = false): self
    {
        return self::from([
            'name' => $environment->name,
            'slug' => $environment->slug,
            'type' => $environment->type,
            'projectName' => $environment->project->name,
            'projectSlug' => $environment->project->slug,
            'keys' => EnvironmentServiceFacade::getKeys($environment, $showSecrets),
        ]);
    }
}
