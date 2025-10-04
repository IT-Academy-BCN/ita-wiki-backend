<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\OldRoleController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\GitHubAuthController;
use App\Http\Controllers\TechnicalTestController;

//GitHub Auth System Endpoints
Route::get('/auth/github', [GitHubAuthController::class, 'redirect'])->name('auth.github');
Route::get('/auth/github/callback', [GitHubAuthController::class, 'callback'])->name('auth.github.callback');
Route::get('/auth/logout', [GitHubAuthController::class, 'logout'])->name('auth.logout');


// (Old)Roles Endpoints: in the current permission logic Roles table refers to Users
Route::post('/login', [OldRoleController::class, 'getRoleByGithubId'])->name('login');
Route::post('/roles', [OldRoleController::class, 'createRole'])->name('roles.create');
Route::put('/roles', [OldRoleController::class, 'updateRole'])->name('roles.update');
Route::put('/feature-flags/role-self-assignment', [OldRoleController::class, 'roleSelfAssignment'])->name('feature-flags.role-self-assignment');


//TECHNICAL TESTS ENDPOINTS
Route::apiResource('technical-tests', TechnicalTestController::class)->only(['index', 'store']);


//Resources Endpoints
Route::apiResource('resources', ResourceController::class);


//Likes Endpoints
Route::get('/likes/{github_id}', [LikeController::class,'getStudentLikes'])->name('likes');
Route::post('/likes', [LikeController::class,'createStudentLike'])->name('like.create');
Route::delete('/likes', [LikeController::class,'deleteStudentLike'])->name('like.delete');


//Bookmarks Endpoints
Route::get('/bookmarks/{github_id}', [BookmarkController::class,'getStudentBookmarks'])->name('bookmarks');
Route::post('/bookmarks', [BookmarkController::class,'createStudentBookmark'])->name('bookmark.create');
Route::delete('/bookmarks', [BookmarkController::class,'deleteStudentBookmark'])->name('bookmark.delete');


//Tags Endpoints
Route::get('/tags/frequency', [TagController::class, 'getTagsFrequency'])->name('tags.frequency');
Route::get('/tags/category-frequency', [TagController::class, 'getCategoryTagsFrequency'])->name('category.tags.frequency');
Route::get('/tags/by-category', [TagController::class, 'getCategoryTagsId'])->name('category.tags.id');
Route::get('/tags', [TagController::class, 'index'])->name('tags');
