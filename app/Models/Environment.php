<?php

namespace App\Models;

use App\Observers\BlameableObserver;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy([BlameableObserver::class])]
class Environment extends BaseModel implements Authenticatable
{
    use HasApiTokens;
    use \Illuminate\Auth\Authenticatable;

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
        'type',
        'is_default',
        'created_by',
        'updated_by',
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

    public function variables(): HasMany
    {
        return $this->hasMany(EnvironmentVariableValue::class);
    }
}
