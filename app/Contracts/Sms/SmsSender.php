<?php

declare(strict_types=1);

namespace App\Contracts\Sms;

interface SmsSender
{
    public function send(string $phone, string $message): void;
}
