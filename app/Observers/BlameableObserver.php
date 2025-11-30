<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class BlameableObserver
{
    public function created(Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $userId = auth()->id();

        $model->created_by ??= $userId;
        $model->updated_by ??= $userId;
    }

    public function updated(Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        $model->updated_by = auth()->id();
    }
}
