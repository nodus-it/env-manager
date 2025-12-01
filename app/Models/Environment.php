<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

class Environment extends BaseModel
{
    use HasApiTokens;
    public const TYPES = [
        'production',
        'staging',
        'testing',
        'local',
        'custom',
    ];

    protected $fillable = [
        'project_id',
        'name',
        'slug',
        'order',
        'type',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
