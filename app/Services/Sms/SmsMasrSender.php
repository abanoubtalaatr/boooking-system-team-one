<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Contracts\Sms\SmsSenderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsMasrSender implements SmsSenderInterface
{
    public function send(string $phone, string $message): void
    {
        $config = config('services.sms_masr');

        if (! $this->hasCredentials($config)) {
            if (app()->environment('production')) {
                throw new RuntimeException('SMS Masr credentials are not configured.');
            }

            Log::info('SMS Masr is not configured. SMS was not sent.', [
                'phone' => $phone,
            ]);

            return;
        }

        $response = Http::asForm()
            ->timeout(10)
            ->retry(2, 250)
            ->post($config['endpoint'], [
                'username' => $config['username'],
                'password' => $config['password'],
                'sender' => $config['sender'],
                'mobile' => $phone,
                'message' => $message,
                'language' => $config['language'],
                'environment' => $config['environment'],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('SMS Masr request failed.');
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function hasCredentials(array $config): bool
    {
        return filled($config['endpoint'] ?? null)
            && filled($config['username'] ?? null)
            && filled($config['password'] ?? null)
            && filled($config['sender'] ?? null);
    }
}
