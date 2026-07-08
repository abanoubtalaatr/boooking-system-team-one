<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Policies\DoctorPolicy;
use App\Policies\AvailabilitySlotPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\MessagePolicy;
use Illuminate\Support\Facades\Gate;
use App\Models\DoctorProfile;
use App\Models\AvailabilitySlot;
use App\Models\Conversation;
use App\Models\Message;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // polices
        Gate::policy(DoctorProfile::class, DoctorPolicy::class);
        Gate::policy(AvailabilitySlot::class, AvailabilitySlotPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
    }
}
