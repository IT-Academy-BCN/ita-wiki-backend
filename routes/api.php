<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\GitHubAuthController;
use App\Http\Controllers\Api\TagNodeController;
use App\Http\Controllers\Api\RoleNodeController;
use App\Http\Controllers\ResourceEditController;
use App\Http\Controllers\Api\BookmarkNodeController;

Route::get('/auth/github/redirect', [GitHubAuthController::class, 'redirect']);
Route::get('/auth/github/callback', [GitHubAuthController::class, 'callback']);

Route::post('/resources', [ResourceController::class, 'store'])->name('resources.store');

Route::post('/v2/resources', [ResourceController::class, 'storeResource'])->name('resources.store.v2');

Route::get('/resources', [ResourceController::class, 'index'])->name('resources');

Route::get('v2/resources', [ResourceController::class, 'showResource'])->name('showResource');

Route::post('/login', [RoleController::class, 'getRoleByGithubId'])->name('login');

Route::put('/resources/{resource}', [ResourceEditController::class, 'update'])->name('resources.update');

Route::get('/bookmarks/{github_id}', [BookmarkController::class,'getStudentBookmarks'])->name('bookmarks');

Route::post('/bookmarks', [BookmarkController::class,'createStudentBookmark'])->name('bookmark.create');

Route::delete('/bookmarks', [BookmarkController::class,'deleteStudentBookmark'])->name('bookmark.delete');

Route::post('/roles', [RoleController::class, 'createRole'])->name('roles.create');

Route::put('/roles', [RoleController::class, 'updateRole'])->name('roles.update');

Route::get('/likes/{github_id}', [LikeController::class,'getStudentLikes'])->name('likes');

Route::post('/likes', [LikeController::class,'createStudentLike'])->name('like.create');

Route::delete('/likes', [LikeController::class,'deleteStudentLike'])->name('like.delete');

Route::get('/tags', [TagController::class, 'index'])->name('tags');

Route::get('/tags/frequency', [TagController::class, 'getTagsFrequency'])->name('tags.frequency');

Route::get('/tags/category-frequency', [TagController::class, 'getCategoryTagsFrequency'])->name('category.tags.frequency');

Route::get('/tags/by-category', [TagController::class, 'getCategoryTagsId'])->name('category.tags.id');


// FEATURE FLAGS ENDPOINTS

Route::put('/feature-flags/role-self-assignment', [RoleController::class, 'roleSelfAssignment'])->name('feature-flags.role-self-assignment');



//new github_id to node_id transition ENDPOINTS
Route::post('/roles-node', [RoleNodeController::class, 'createRoleNode'])->name('roles-node.create');
Route::put('/roles-node',  [RoleNodeController::class, 'updateRoleNode'])->name('roles-node.update');
Route::post('/login-node', [RoleNodeController::class, 'getRoleByNodeId'])->name('login-node');

//FEATURE FLAGS ENDPOINTS for node
Route::put('/feature-flags/role-self-assignment-node', [RoleNodeController::class, 'roleSelfAssignmentNode'])->name('feature-flags.role-self-assignment-node');

//BOOKMARKNODE ENDPOINTS
Route::post('/bookmarks-node',   [BookmarkNodeController::class, 'createStudentBookmarkNode'])->name('bookmark-node.create');
Route::delete('/bookmarks-node', [BookmarkNodeController::class, 'deleteStudentBookmarkNode'])->name('bookmark-node.delete');
Route::get('/bookmarks-node/{node_id}', [BookmarkNodeController::class, 'getStudentBookmarksNode'])->name('bookmarks-node');


//TAGSNODE ENDPOINTS
Route::get('/tags-node', [TagNodeController::class, 'index'])->name('tags-node');
Route::get('/tags-node/frequency', [TagNodeController::class, 'getTagsFrequency'])->name('tags-node.frequency');
Route::get('/tags-node/category-frequency', [TagNodeController::class, 'getCategoryTagsFrequency'])->name('category.tags-node.frequency');
Route::get('/tags-node/by-category', [TagNodeController::class, 'getCategoryTagsId'])->name('category.tags-node.id');

//LIKESNODE ENDPOINTS



//RESOURCESNODE ENDPOINTS