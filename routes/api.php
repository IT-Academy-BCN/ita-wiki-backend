<?php

declare (strict_types= 1);

use App\Http\Controllers\RoleController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ResourceEditController;
use Illuminate\Support\Facades\Route;


Route::post('/resource', [ResourceController::class, 'store'])->name('resource.store');
Route::get('/resources/lists', [ResourceController::class, 'index'])->name('resources.list');
//Route::delete('/resources/{resource}', [ResourceController::class, 'destroy']);
Route::put('/resources/{resource}', [ResourceEditController::class, 'update'])->name('resource.update');

Route::get('/users/user-signedin-as', [RoleController::class, 'getRoleByGithubId']);

/* BURN AFTER READING
Given that the user signed-up and sign-in requires same validation process through GitHub OAuth...
Given that students signed-up requires no previous authentication other that GitHub OAuth...
Given that mentor needs students to be signed-up as 'anonymous' to update their role...
THEN : it's convenient to store any verified GitHub account in Roles Migration as anonymous when endpoint FAILS.
(the more, the merrier)
*/