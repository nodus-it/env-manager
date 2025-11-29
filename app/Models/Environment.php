<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Environment extends Model
{
    use HasFactory;

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

    protected static function booted(): void
    {
        static::saving(function (self $environment): void {
            if ($environment->type !== null && ! in_array($environment->type, self::TYPES, true)) {
                $environment->type = 'custom';
            }
        });

        static::saved(function (self $environment): void {
            if (! $environment->is_default) {
                return;
            }

            DB::transaction(function () use ($environment): void {
                $environment->newQuery()
                    ->where('project_id', $environment->project_id)
                    ->whereKeyNot($environment->getKey())
                    ->update(['is_default' => false]);
            });
        });
    }
}
