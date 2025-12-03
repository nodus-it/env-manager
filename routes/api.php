<?php

use App\Http\Controllers\GetEnvironmentKeyController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'ability:env.read',
])->get('/environment', GetEnvironmentKeyController::class);
//
// Route::middleware([
//    'auth:sanctum',
//    'ability:env.write'
// ])->post('/environment', GetEnvironmentKeyController::class);
