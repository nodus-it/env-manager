<?php

namespace App\Models;

use App\Observers\BlameableObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([BlameableObserver::class])]
class EnvironmentVariableValue extends BaseModel
{
    protected $fillable = [
        'environment_id',
        'variable_key_id',
        'value',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'encrypted:string',
        ];
    }

    public function environment(): BelongsTo
    {
        return $this->belongsTo(Environment::class);
    }

    public function variableKey(): BelongsTo
    {
        return $this->belongsTo(VariableKey::class);
    }
}
