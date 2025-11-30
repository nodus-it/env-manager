<?php

namespace App\Models;

use App\Observers\BlameableObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([BlameableObserver::class])]
class VariableKey extends BaseModel
{
    protected $fillable = [
        'key',
        'description',
        'type',
        'is_secret',
        'validation_rules',
        'default_value',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
            'default_value' => 'encrypted:string',
        ];
    }

    public function projectVariableValues(): HasMany
    {
        return $this->hasMany(ProjectVariableValue::class, 'variable_key_id');
    }

    public function environmentVariableValues(): HasMany
    {
        return $this->hasMany(EnvironmentVariableValue::class, 'variable_key_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
