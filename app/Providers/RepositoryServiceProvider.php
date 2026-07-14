<?php

namespace App\Providers;

use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Eloquent\EloquentConversationRepository;
use App\Repositories\Eloquent\EloquentMessageRepository;
use App\Repositories\Contracts\AvailabilitySlotRepositoryInterface;
use App\Repositories\Contracts\DoctorProfileRepositoryInterface;
use App\Repositories\Contracts\HospitalRepositoryInterface;
use App\Repositories\Contracts\SpecialtyRepositoryInterface;
use App\Repositories\Eloquent\EloquentAvailabilitySlotRepository;
use App\Repositories\Eloquent\EloquentDoctorProfileRepository;
use App\Repositories\Eloquent\EloquentHospitalRepository;
use App\Repositories\Eloquent\EloquentSpecialtyRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DoctorProfileRepositoryInterface::class, EloquentDoctorProfileRepository::class);
        $this->app->bind(AvailabilitySlotRepositoryInterface::class, EloquentAvailabilitySlotRepository::class);
        $this->app->bind(SpecialtyRepositoryInterface::class, EloquentSpecialtyRepository::class);
        $this->app->bind(HospitalRepositoryInterface::class, EloquentHospitalRepository::class);

        $this->app->bind(ConversationRepositoryInterface::class, EloquentConversationRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, EloquentMessageRepository::class);
    }
}
