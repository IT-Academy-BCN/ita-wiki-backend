<?php

declare (strict_types= 1);

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ResourceEditController;
use App\Http\Controllers\LikeController;
use Illuminate\Support\Facades\Route;


Route::post('/resources', [ResourceController::class, 'store'])->name('resources.store');

Route::get('/resources', [ResourceController::class, 'index'])->name('resources');

Route::post('/login', [RoleController::class, 'getRoleByGithubId'])->name('login');

Route::put('/resources/{resource}', [ResourceEditController::class, 'update'])->name('resources.update');

Route::get('/bookmarks/{github_id}', [BookmarkController::class,'getStudentBookmarks'])->name('bookmarks'); // retrieves bookmarks of a given student

Route::post('/bookmarks', [BookmarkController::class,'createStudentBookmark'])->name('bookmark.create');

Route::delete('/bookmarks', [BookmarkController::class,'deleteStudentBookmark'])->name('bookmark.delete');

Route::post('/roles', [RoleController::class, 'createRole'])->name('roles.create');

Route::get('/likes/{github_id}', [LikeController::class,'getStudentLikes'])->name('likes'); // retrieves likes of a given student

Route::post('/likes', [LikeController::class,'createStudentLike'])->name('like.create');

Route::delete('/likes', [LikeController::class,'deleteStudentLike'])->name('like.delete');
