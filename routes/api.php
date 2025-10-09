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
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController; 

//GitHub Auth System Endpoints
Route::get('/auth/github/redirect', [GitHubAuthController::class, 'redirect'])->name('github.redirect');
Route::get('/auth/github/callback', [GitHubAuthController::class, 'callback'])->name('github.callback');
Route::get('/auth/github/user', [GitHubAuthController::class, 'user'])->name('github.user');


// ========== TAG ENDPOINTS (JSON-based - PUBLIC for now) ==========
// ⚠️ These are PUBLIC until we decide on authentication strategy
Route::prefix('tags')->group(function () {
    Route::get('/', [TagController::class, 'index'])->name('tags');
    Route::get('/frequency', [TagController::class, 'getTagsFrequency'])->name('tags.frequency');
    Route::get('/category-frequency', [TagController::class, 'getCategoryTagsFrequency'])->name('category.tags.frequency');
    Route::get('/by-category', [TagController::class, 'getCategoryTagsId'])->name('tags.by-category');
});

// Protected routes with authentication and authorization
Route::middleware(['auth:api'])->group(function () {
    
    // RESOURCES ENDPOINTS
    Route::apiResource('resources', ResourceController::class);
    
    // TECHNICAL TESTS ENDPOINTS
    Route::apiResource('technical-tests', TechnicalTestController::class); 
    
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
});

// ========== OLD ROLE ENDPOINTS (deprecated - backward compatibility) ==========
// ⚠️ TODO: Remove after frontend migration
Route::prefix('old-role')->group(function () {
    Route::get('/{github_id}', [OldRoleController::class, 'show'])->name('old-role.show');
    Route::post('/', [OldRoleController::class, 'store'])->name('old-role.store');
    Route::put('/{old_role}', [OldRoleController::class, 'update'])->name('old-role.update');
    Route::delete('/{old_role}', [OldRoleController::class, 'destroy'])->name('old-role.destroy');
});