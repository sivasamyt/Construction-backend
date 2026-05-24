<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TenantAuthController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\TenantUserController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('companies')->group(function () {
    Route::get('preview-domain', [CompanyController::class, 'previewDomain']);
    Route::post('register', [CompanyController::class, 'register']);

    Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('{company}', [CompanyController::class, 'show']);
    });
});

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::prefix('tenant/{domain}')->middleware('tenant')->group(function () {
    Route::get('/', [TenantController::class, 'show']);

    Route::prefix('auth')->group(function () {
        Route::post('login', [TenantAuthController::class, 'login']);
        Route::post('register-owner', [TenantAuthController::class, 'registerOwner']);

        Route::middleware(['auth:sanctum', 'tenant.user'])->group(function () {
            Route::post('logout', [TenantAuthController::class, 'logout']);
            Route::get('me', [TenantAuthController::class, 'me']);
        });
    });

    Route::middleware(['auth:sanctum', 'tenant.user'])->group(function () {
        Route::get('users', [TenantUserController::class, 'index'])
            ->middleware('permission:company.users.view');
        Route::post('users', [TenantUserController::class, 'store'])
            ->middleware(['owner', 'permission:company.users.create']);
        Route::get('users/{user}', [TenantUserController::class, 'show'])
            ->middleware('permission:company.users.view');
        Route::put('users/{user}', [TenantUserController::class, 'update'])
            ->middleware(['owner', 'permission:company.users.update']);
        Route::delete('users/{user}', [TenantUserController::class, 'destroy'])
            ->middleware(['owner', 'permission:company.users.delete']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('users', [UserController::class, 'index'])->middleware('permission:users.view');
    Route::post('users', [UserController::class, 'store'])->middleware('permission:users.create');
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('permission:users.view');
    Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete');
    Route::post('users/{user}/roles', [UserController::class, 'assignRoles'])
        ->middleware(['role:super_admin|admin', 'permission:users.assign-roles']);

    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
    Route::post('roles', [RoleController::class, 'store'])
        ->middleware(['role:super_admin|admin', 'permission:roles.create']);
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view');
    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware(['role:super_admin|admin', 'permission:roles.update']);
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])
        ->middleware(['role:super_admin|admin', 'permission:roles.delete']);
    Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])
        ->middleware(['role:super_admin|admin', 'permission:roles.assign-permissions']);

    Route::get('permissions', [PermissionController::class, 'index'])->middleware('permission:permissions.view');
    Route::post('permissions', [PermissionController::class, 'store'])
        ->middleware(['role:super_admin|admin', 'permission:permissions.create']);
    Route::get('permissions/{permission}', [PermissionController::class, 'show'])
        ->middleware('permission:permissions.view');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])
        ->middleware(['role:super_admin|admin', 'permission:permissions.update']);
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])
        ->middleware(['role:super_admin|admin', 'permission:permissions.delete']);
});
