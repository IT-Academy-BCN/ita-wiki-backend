<?php

declare (strict_types= 1);

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ResourceEditController;
use Illuminate\Support\Facades\Route;


Route::post('/resources', [ResourceController::class, 'store'])->name('resources.store');

Route::get('/resources', [ResourceController::class, 'index'])->name('resources');

Route::get('/users/user-signedin-as', [RoleController::class, 'getRoleByGithubId']);

Route::put('/resources/{resource}', [ResourceEditController::class, 'update'])->name('resource.update');

Route::get('/bookmarks', [BookmarkController::class,'bookmarkSwitcherAndRetriever'])->name('bookmarks'); // retrieves bookmarks of a given student

Route::post('/bookmarks', [BookmarkController::class,'bookmarkSwitcherAndRetriever'])->name('bookmark.switch'); // creates or deletes a single bookmark of a given student and then retrieves all student's bookmarks
