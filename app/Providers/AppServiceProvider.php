<?php

namespace App\Providers;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Contracts\Sms\SmsSenderInterface;
use App\Models\AvailabilitySlot;
use App\Models\Conversation;
use App\Models\DoctorProfile;
use App\Models\Message;
use App\Policies\AvailabilitySlotPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\DoctorPolicy;
use App\Policies\MessagePolicy;
use App\Services\Payments\PaymobGateway;
use App\Services\Sms\SmsMasrSender;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsSenderInterface::class, SmsMasrSender::class);
        $this->app->bind(PaymentGatewayInterface::class, PaymobGateway::class);
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
