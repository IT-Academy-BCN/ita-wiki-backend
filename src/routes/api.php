<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\GitHubAuthController;
use App\Http\Controllers\TechnicalTestController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ListProjectsController;
use Illuminate\Http\Request;

// GitHub Auth System Endpoints (PUBLIC)
Route::get('/auth/github/redirect', [GitHubAuthController::class, 'redirect'])->name('github.redirect');
Route::get('/auth/github/callback', [GitHubAuthController::class, 'callback'])->name('github.callback');

// Protected Auth Endpoints (Require Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user()->only(['id', 'github_id', 'github_user_name', 'name', 'email'])
        ]);
    });

    Route::get('/auth/github/user', [GitHubAuthController::class, 'user'])->name('github.user');

    Route::post('/auth/logout', function(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Sesión closed succesfully'
        ]);
    });
});


// ========== TAG ENDPOINTS (JSON-based - PUBLIC for now) ==========
// ⚠️ These are PUBLIC until we decide on authentication strategy
Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index'])->name('tags');
    Route::get('/frequency', [TagController::class, 'getTagsFrequency'])->name('tags.frequency');
    Route::get('/category-frequency', [TagController::class, 'getCategoryTagsFrequency'])->name('category.tags.frequency');
    Route::get('/by-category', [TagController::class, 'getCategoryTagsId'])->name('tags.by-category');
});

// ========== LIST PROJECTS ENDPOINTS ==========

// PUBLIC
Route::apiResource('codeconnect', ListProjectsController::class)->only(['index', 'show']);

// PROTECTED
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('codeconnect', ListProjectsController::class)->except(['index', 'show']);
});

// ========== CONTRIBUTORS ENDPOINTS ==========

// PUBLIC
Route::get('/codeconnect/{listProject}/contributors', [ListProjectsController::class, 'getContributors'])->name('contributors.index');

// PROTECTED
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/codeconnect/{listProject}/contributors', [ListProjectsController::class, 'addContributor'])->name('contributors.store');
    Route::delete('/codeconnect/{listProject}/contributors/{contributor}', [ListProjectsController::class, 'removeContributor'])->name('contributors.destroy');
    Route::patch('/codeconnect/{listProject}/contributors/{contributor}/status', [ListProjectsController::class, 'updateContributorStatus'])->name('contributors.update-status');
});

// ========== RESOURCES ENDPOINTS ==========

// PUBLIC
Route::apiResource('resources', ResourceController::class)->only(['index', 'show']);

// PROTECTED
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('resources', ResourceController::class)->except(['index', 'show']);
});

// TECHNICAL TESTS ENDPOINTS
Route::middleware(['throttle:60,1'])->group(function () {
    Route::apiResource('technical-tests', TechnicalTestController::class);
});

// EXERCISES ENDPOINTS
Route::middleware(['throttle:60,1'])->group(function () {
    Route::apiResource('exercises', ExerciseController::class);
});

// LIKES ENDPOINTS
Route::post('/likes', [LikeController::class, 'createStudentLike'])->name('like.create');
Route::delete('/likes', [LikeController::class, 'deleteStudentLike'])->name('like.delete');
Route::get('/likes/{github_id}', [LikeController::class, 'getStudentLikes'])->name('likes');

// BOOKMARKS ENDPOINTS
Route::post('/bookmarks', [BookmarkController::class, 'createStudentBookmark'])->name('bookmark.create');
Route::delete('/bookmarks', [BookmarkController::class, 'deleteStudentBookmark'])->name('bookmark.delete');
Route::get('/bookmarks/{github_id}', [BookmarkController::class, 'getStudentBookmarks'])->name('bookmarks');

// USER ENDPOINTS (Empty for now - pending mentor decision)
Route::put('/users/{user}/update-role', [UserController::class, 'updateRole']);
Route::get('/profile', [UserController::class, 'profile']);
Route::get('/users', [UserController::class, 'index']);
Route::delete('/users/{user}', [UserController::class, 'destroy']);

// ROLES ENDPOINTS
Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->name('roles.index');
    Route::post('/assign', [RoleController::class, 'assignRole'])->name('roles.assign');
    Route::get('/users/{user}', [RoleController::class, 'getUserRoles'])->name('roles.user');
});
