<?php

use App\Livewire\Admin\PatientManager;
use App\Support\AdminPermissionCatalog;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;

it('maps every catalog permission to at least one protected route', function (): void {
    $routePermissions = collect(Route::getRoutes()->getRoutes())
        ->flatMap(fn (LaravelRoute $route): array => $route->gatherMiddleware())
        ->filter(fn (string $middleware): bool => str_starts_with($middleware, 'can:'))
        ->map(fn (string $middleware): string => str($middleware)->after('can:')->before(',')->toString())
        ->unique();

    $protectedPermissions = $routePermissions->merge(PatientManager::PERMISSIONS)->unique();

    expect($protectedPermissions)->toContain(...array_keys(AdminPermissionCatalog::all()));
});
