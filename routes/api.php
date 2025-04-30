<?php

declare (strict_types= 1);

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ResourceEditController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;


Route::post('/resources', [ResourceController::class, 'store'])->name('resources.store');

Route::get('/resources', [ResourceController::class, 'index'])->name('resources');

Route::post('/login', [RoleController::class, 'getRoleByGithubId'])->name('login');

Route::put('/resources/{resource}', [ResourceEditController::class, 'update'])->name('resources.update');

Route::get('/bookmarks/{github_id}', [BookmarkController::class,'getStudentBookmarks'])->name('bookmarks');

Route::post('/bookmarks', [BookmarkController::class,'createStudentBookmark'])->name('bookmark.create');

Route::delete('/bookmarks', [BookmarkController::class,'deleteStudentBookmark'])->name('bookmark.delete');

Route::post('/roles', [RoleController::class, 'createRole'])->name('roles.create');

Route::get('/likes/{github_id}', [LikeController::class,'getStudentLikes'])->name('likes');

Route::post('/likes', [LikeController::class,'createStudentLike'])->name('like.create');

Route::delete('/likes', [LikeController::class,'deleteStudentLike'])->name('like.delete');

Route::get('/tags', [TagController::class, 'index'])->name('tags');

Route::get('/tags/frequency', [TagController::class, 'getTagsFrequency'])->name('tags.frequency');
// for stats and filtering...

Route::get('/tags/category-frequency', [TagController::class, 'getCategoryTagsFrequency'])->name('category.tags.frequency');
// for stats and filtering...