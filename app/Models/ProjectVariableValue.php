<?php

namespace App\Models;

use App\Observers\BlameableObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([BlameableObserver::class])]
class ProjectVariableValue extends BaseModel
{
    protected $fillable = [
        'project_id',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function variableKey(): BelongsTo
    {
        return $this->belongsTo(VariableKey::class);
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
