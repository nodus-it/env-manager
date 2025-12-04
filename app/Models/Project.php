<?php

namespace App\Models;

use App\Observers\BlameableObserver;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([BlameableObserver::class])]
class Project extends BaseModel
{
    protected $fillable = [
        'name',
        'slug',
        'repo_url',
        'description',
        'created_by',
        'updated_by',
    ];

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function environments(): HasMany
    {
        return $this->hasMany(Environment::class);
    }

    public function projectVariableValues(): HasMany
    {
        return $this->hasMany(ProjectVariableValue::class);
    }
}
