<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\DoctorRepositoryInterface;
use App\Repositories\Contracts\AvailabilitySlotRepositoryInterface;
use App\Repositories\DoctorRepository;
use App\Repositories\AvailabilitySlotRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AvailabilitySlotRepositoryInterface::class, AvailabilitySlotRepository::class);
        $this->app->bind(DoctorRepositoryInterface::class, DoctorRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
